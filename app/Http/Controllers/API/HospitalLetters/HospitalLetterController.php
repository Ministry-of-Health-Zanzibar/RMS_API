<?php

namespace App\Http\Controllers\API\HospitalLetters;

use App\Http\Controllers\Controller;
use App\Models\HospitalLetter;
use App\Models\Referral;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
     *                 required={"referral_id","received_date","outcome"},
     *                 @OA\Property(property="referral_id", type="integer"),
     *                 @OA\Property(property="received_date", type="string", format="date"),
     *                 @OA\Property(property="content_summary", type="string"),
     *                 @OA\Property(property="next_appointment_date", type="string", format="date"),
     *                 @OA\Property(property="letter_file", type="string", format="binary"),
     *                 @OA\Property(property="outcome", type="string", enum={"Follow-up","Finished","Transferred","Death"}),
     *                 @OA\Property(property="followup_date", type="string", format="date"),
     *                 @OA\Property(property="notes", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Hospital Letter created successfully"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Referral not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Validate Hospital Letter data
        $validated = $request->validate([
            'referral_id' => ['required','exists:referrals,referral_id'],
            'received_date' => ['required','string'],
            'content_summary' => ['nullable','string'],
            'next_appointment_date' => ['nullable','string'],
            'letter_file' => ['nullable','file','mimes:pdf,doc,docx','max:2048'],
            'outcome' => ['required','in:Follow-up,Finished,Transferred,Death'],
            'followup_date' => ['nullable','string'], // will be required later if outcome is Follow-up
            'notes' => ['nullable','string'],
        ]);

        // Handle file upload
        if ($request->hasFile('letter_file')) {
            $path = $request->file('letter_file')->store('hospital_letters','public');
            $validated['letter_file'] = $path;
        }

        $validated['created_by'] = Auth::id();

        // Create Hospital Letter
        $letter = HospitalLetter::create($validated);

        $referral = Referral::find($validated['referral_id']);

        if (!$referral) {
            return response()->json([
                'message' => 'Referral not found',
                'statusCode' => 404
            ], 404);
        }

        $patientId = $referral->patient_id;

        // If outcome is Follow-up, validate and create a follow-up record
        if ($validated['outcome'] === 'Follow-up') {
            $followupValidated = $request->validate([
                'followup_date' => ['required','date'],
                'notes' => ['nullable','string'],
            ]);

            $followupValidated['letter_id'] = $letter->letter_id;
            $followupValidated['patient_id'] = $patientId;
            $followupValidated['created_by'] = Auth::id();

            $followup = FollowUp::create($followupValidated);
        }

        return response()->json([
            'message' => 'Hospital Letter created successfully',
            'data' => $letter,
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

    public function getHospitalLettersByReferralId($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $letter = HospitalLetter::with([
            'referral',
            'followups'
        ])->where('referral_id',$id)
        ->get();

        return response()->json([
            'data' => $letter,
            'statusCode' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Update Hospital Letter')) {
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

        $validated = $request->validate([
            'referral_id' => ['sometimes','exists:referrals,referral_id'],
            'received_date' => ['sometimes','date'],
            'content_summary' => ['nullable','string'],
            'next_appointment_date' => ['nullable','date'],
            'letter_file' => ['nullable','file','mimes:pdf,doc,docx','max:2048'],
            'outcome' => ['sometimes','in:Follow-up,Finished,Transferred,Death'],
        ]);

        if ($request->hasFile('letter_file')) {
            if ($letter->letter_file) {
                Storage::disk('public')->delete($letter->letter_file);
            }
            $path = $request->file('letter_file')->store('hospital_letters','public');
            $validated['letter_file'] = $path;
        }

        $letter->update($validated);

        return response()->json([
            'message' => 'Hospital Letter updated successfully',
            'data' => $letter,
            'statusCode' => 200
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
