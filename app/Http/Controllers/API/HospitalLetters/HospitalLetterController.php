<?php

namespace App\Http\Controllers\API\HospitalLetters;

use App\Http\Controllers\Controller;
use App\Models\HospitalLetter;
use App\Models\Referral;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HospitalLetterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Hospital Letter|Create Hospital Letter|Update Hospital Letter|Delete Hospital Letter', ['only' => ['index','store','show','update','destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $letters = HospitalLetter::with(['referral','followups'])->get();

        return response()->json([
            'data' => $letters,
            'statusCode' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/hospital-letters",
     *     summary="Create a new hospital letter",
     *     tags={"Hospital Letters"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"referral_id","outcome"},
     *                 @OA\Property(
     *                     property="referral_id",
     *                     type="integer",
     *                     description="ID of the referral"
     *                 ),
     *                 @OA\Property(
     *                     property="hospital_id",
     *                     type="integer",
     *                     nullable=true,
     *                     description="Required if outcome = Transferred"
     *                 ),
     *                 @OA\Property(
     *                     property="content_summary",
     *                     type="string",
     *                     nullable=true,
     *                     description="Summary of the letter"
     *                 ),
     *                 @OA\Property(
     *                     property="next_appointment_date",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     description="Next appointment date"
     *                 ),
     *                 @OA\Property(
     *                     property="letter_file",
     *                     type="string",
     *                     format="binary",
     *                     nullable=true,
     *                     description="Letter file (PDF, DOC, DOCX, max 2MB)"
     *                 ),
     *                 @OA\Property(
     *                     property="outcome",
     *                     type="string",
     *                     enum={"Follow-up","Finished","Transferred","Death"},
     *                     description="Outcome of the hospital letter"
     *                 ),
     *                 @OA\Property(
     *                     property="followup_date",
     *                     type="string",
     *                     format="date",
     *                     nullable=true,
     *                     description="Follow-up date (required for Follow-up, Finished, Transferred, Death)"
     *                 ),
     *                 @OA\Property(
     *                     property="notes",
     *                     type="string",
     *                     nullable=true,
     *                     description="Additional notes"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Hospital Letter created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hospital Letter created successfully"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Referral not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Permission check
        if (
            !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF']) ||
            !$user->can('Create Hospital Letter')
        ) {
            return response()->json([
                'message'    => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Base validation rules
        $rules = [
            'referral_id'           => 'required|exists:referrals,referral_id',
            'hospital_id'           => 'nullable|exists:hospitals,hospital_id',
            'content_summary'       => 'nullable|string',
            'next_appointment_date' => 'nullable|date',
            'letter_file'           => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'outcome'               => 'required|in:Follow-up,Finished,Transferred,Death',
        ];

        $validator = Validator::make($request->all(), $rules);

        // Conditional validation
        $validator->sometimes('followup_date', 'required|date', function ($input) {
            return in_array($input->outcome, ['Follow-up', 'Finished', 'Death', 'Transferred']);
        });

        $validator->sometimes('hospital_id', 'required|exists:hospitals,hospital_id', function ($input) {
            return $input->outcome === 'Transferred';
        });

        if ($validator->fails()) {
            return response()->json([
                'message'    => 'Validation Error',
                'errors'     => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $validated = $validator->validated();

        // Add default followup_date if missing
        if (empty($validated['followup_date'])) {
            $validated['followup_date'] = now()->toDateString();
        }

        // Ensure referral exists
        $referral = Referral::find($validated['referral_id']);
        if (!$referral) {
            return response()->json([
                'message'    => 'Referral not found',
                'statusCode' => 404
            ], 404);
        }

        // Handle file upload
        if (!empty($validated['letter_file'])) {
            $file = $request->file('letter_file');
            $extension = $file->getClientOriginalExtension();
            $newFileName = 'hospital_letter_' . date('h-i-s_a_d-m-Y') . '.' . $extension;
            $file->move(public_path('uploads/hospitalLetters/'), $newFileName);
            $validated['letter_file'] = 'uploads/hospitalLetters/' . $newFileName;
        }

        $validated['created_by'] = Auth::id();

        // Create Hospital Letter
        $letter = HospitalLetter::create($validated);

        // Outcome-specific Follow-up data
        switch ($validated['outcome']) {
            case 'Follow-up':
                $followupData = [
                    'followup_date'   => $validated['followup_date'],
                    'notes'           => $validated['content_summary'] ?? null,
                    'followup_status' => 'Ongoing',
                ];
                break;

            case 'Finished':
                $followupData = [
                    'followup_date'   => $validated['followup_date'],
                    'notes'           => $validated['content_summary'] ?? null,
                    'followup_status' => 'Closed',
                ];
                break;

            case 'Transferred':
                $followupData = [
                    'followup_date'   => $validated['followup_date'],
                    'notes'           => $validated['content_summary'] ?? null,
                    'followup_status' => 'Transferred',
                    'hospital_id'     => $validated['hospital_id'],
                    'patient_id'      => $referral->patient_id,
                ];
                break;

            case 'Death':
                $followupData = [
                    'followup_date'   => $validated['followup_date'],
                    'notes'           => $validated['content_summary'] ?? null,
                    'followup_status' => 'Closed',
                ];
                break;

            default:
                // Safety fallback in case outcome is missing or invalid
                $followupData = [
                    'followup_date'   => $validated['followup_date'] ?? now()->toDateString(),
                    'notes'           => $validated['content_summary'] ?? null,
                    'followup_status' => 'Ongoing',
                ];
                break;
        }

        // Update referral status only for certain outcomes
        if ($validated['outcome'] === 'Finished' || $validated['outcome'] === 'Death') {
            $referral->update(['status' => 'Closed']);
        }

        // If Transferred, create new referral
        if ($validated['outcome'] === 'Transferred') {
            Referral::create([
                'referral_number'     => $referral->referral_number,
                'patient_id'          => $referral->patient_id,
                'hospital_id'         => $validated['hospital_id'],
                'status'              => 'Transferred',
                'reason_id'           => $referral->reason_id,
                'parent_referral_id'  => $referral->referral_id, // link to parent
                'confirmed_by'        => Auth::id(),
                'created_by'          => Auth::id(),
            ]);
        }

        // Save follow-up (explicit approach)
        $followUp = new FollowUp();
        $followUp->letter_id       = $letter->letter_id;
        $followUp->patient_id      = $referral->patient_id;
        $followUp->followup_date   = $followupData['followup_date'];
        $followUp->notes           = $followupData['notes'] ?? null;
        $followUp->followup_status = $followupData['followup_status'];
    
        $followUp->save();

        return response()->json([
            'message'    => 'Hospital Letter created successfully',
            'data'       => $letter,
            'statusCode' => 201
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $letter = HospitalLetter::with(['referral','followups'])->find($id);

        if (!$letter) {
            return response()->json([
                'message' => 'Hospital Letter not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $letter,
            'statusCode' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateHospitalLetter(Request $request, $id)
    {
        $letter = HospitalLetter::findOrFail($id);

        // ✅ Get referral linked to this letter (supporting parent_referral_id)
        $referral = Referral::where('referral_id', $letter->referral_id)
            ->orWhere('referral_id', $letter->parent_referral_id)
            ->first();

        // ✅ Safe patient_id: from referral OR letter
        $patientId = $referral->patient_id ?? $letter->patient_id ?? null;

        if (!$patientId) {
            return response()->json([
                'message'    => 'No patient found for this hospital letter',
                'statusCode' => 404,
            ], 404);
        }

        // ✅ Validate input
        $validated = $request->validate([
            'outcome'         => 'nullable|string',
            'followup_date'   => 'nullable|date',
            'content_summary' => 'nullable|string',
            'hospital_id'     => 'nullable|numeric|exists:hospitals,hospital_id',
        ]);

        // ✅ Outcome fallback (never null)
        $outcome = $validated['outcome'] ?? $letter->outcome ?? 'Follow-up';

        // ✅ Follow-up date fallback
        $followupDate = $validated['followup_date'] ?? now()->toDateString();

        // ✅ Build followup data by outcome (all cases covered)
        switch ($outcome) {
            case 'Follow-up':
                $followupData = [
                    'followup_date'   => $followupDate,
                    'notes'           => $validated['content_summary'] ?? $letter->content_summary ?? null,
                    'followup_status' => 'Ongoing',
                    'hospital_id'     => $validated['hospital_id'] ?? $letter->hospital_id ?? null,
                    'patient_id'      => $patientId,
                ];
                break;

            case 'Finished':
                $followupData = [
                    'followup_date'   => $followupDate,
                    'notes'           => $validated['content_summary'] ?? $letter->content_summary ?? null,
                    'followup_status' => 'Closed',
                    'hospital_id'     => $validated['hospital_id'] ?? $letter->hospital_id ?? null,
                    'patient_id'      => $patientId,
                ];
                break;

            case 'Transferred':
                $followupData = [
                    'followup_date'   => $followupDate,
                    'notes'           => $validated['content_summary'] ?? $letter->content_summary ?? null,
                    'followup_status' => 'Transferred',
                    'hospital_id'     => $validated['hospital_id'] ?? $letter->hospital_id ?? null,
                    'patient_id'      => $patientId,
                ];
                break;

            case 'Death':
                $followupData = [
                    'followup_date'   => $followupDate,
                    'notes'           => $validated['content_summary'] ?? $letter->content_summary ?? null,
                    'followup_status' => 'Death',
                    'hospital_id'     => $validated['hospital_id'] ?? $letter->hospital_id ?? null,
                    'patient_id'      => $patientId,
                ];
                break;

            default:
                // ✅ Safety fallback for any other unforeseen outcome
                $followupData = [
                    'followup_date'   => $followupDate,
                    'notes'           => $validated['content_summary'] ?? $letter->content_summary ?? null,
                    'followup_status' => 'Ongoing',
                    'hospital_id'     => $validated['hospital_id'] ?? $letter->hospital_id ?? null,
                    'patient_id'      => $patientId,
                ];
                break;
        }

        // ✅ Update the hospital letter
        $letter->update([
            'outcome'         => $outcome,
            'content_summary' => $validated['content_summary'] ?? $letter->content_summary,
        ]);

        // ✅ Save or update follow-up (always has followup_status now)
        FollowUp::updateOrCreate(
            ['letter_id' => $letter->letter_id],
            [
                'patient_id'      => $followupData['patient_id'],
                'followup_date'   => $followupData['followup_date'],
                'notes'           => $followupData['notes'],
                'followup_status' => $followupData['followup_status'],
                'hospital_id'     => $followupData['hospital_id'],
            ]
        );

        return response()->json([
            'message' => 'Hospital Letter updated successfully',
            'letter'  => $letter,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Delete Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $letter = HospitalLetter::find($id);

        if (!$letter) {
            return response()->json([
                'message' => 'Hospital Letter not found',
                'statusCode' => 404
            ], 404);
        }

        if ($letter->letter_file) {
            Storage::disk('public')->delete($letter->letter_file);
        }

        $letter->delete();

        return response()->json([
            'message' => 'Hospital Letter deleted successfully',
            'statusCode' => 200
        ]);
    }
}
