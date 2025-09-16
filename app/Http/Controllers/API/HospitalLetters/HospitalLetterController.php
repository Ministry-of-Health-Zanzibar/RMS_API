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
     *                 required={"referral_id","outcome"},
     *                 @OA\Property(property="referral_id", type="integer"),
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

        // Validate Hospital Letter data
        $validated = $request->validate([
            'referral_id'          => ['required', 'exists:referrals,referral_id'],
            'content_summary'      => ['nullable', 'string'],
            'next_appointment_date'=> ['nullable', 'string'],
            'letter_file'          => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
            'outcome'              => ['required', 'in:Follow-up,Finished,Transferred,Death'],
            'followup_date'        => ['nullable', 'string'],
        ]);

        // Ensure referral exists
        $referral = Referral::find($validated['referral_id']);
        if (!$referral) {
            return response()->json([
                'message'    => 'Referral not found',
                'statusCode' => 404
            ], 404);
        }

        if ($request->hasFile('letter_file')) {

            // Get the uploaded file
            $file = $request->file('letter_file');

            // Extract the file extension (pdf, jpg, etc.)
            $extension = $file->getClientOriginalExtension();

            // Generate a custom file name: hospital_letter_1694791234.pdf
            $newFileName = 'hospital_letter_' . time() . '.' . $extension;

            // Move the file to public/uploads/hospitalLetters/
            $file->move(public_path('uploads/hospitalLetters/'), $newFileName);

            // Save the relative file path for DB
            $validated['letter_file'] = 'uploads/hospitalLetters/'.$newFileName;
        }

        $validated['created_by'] = Auth::id();

        // Create Hospital Letter
        $letter = HospitalLetter::create($validated);

        // Prepare FollowUp data (common fields)
        $baseFollowUpData = [
            'letter_id'      => $letter->letter_id,
            'patient_id'     => $referral->patient_id,
            'created_by'     => Auth::id(),
        ];

        // Handle outcome-specific logic
        switch ($validated['outcome']) {
            case 'Follow-up':
                $followupData = $request->validate([
                    'followup_date'   => ['required', 'string'],
                    'content_summary' => ['nullable', 'string'],
                ]);
                $followupData['followup_status'] = 'Ongoing';
                break;

            case 'Finished':
                $followupData = $request->validate([
                    'content_summary' => ['nullable', 'string'],
                ]);
                $followupData['followup_status'] = 'Closed';
                $referral->update(['status' => 'Closed']);
                break;

            // case 'Transferred':
            //     $followupData = $request->validate([
            //         'followup_date'   => ['required', 'string'],
            //         'content_summary' => ['nullable', 'string'],
            //         'hospital_id'     => ['required', 'exists:hospitals,hospital_id']
            //     ]);
            //     $followupData['followup_status'] = 'Transferred';
            //     break;
            case 'Transferred':
                // 1️ Validate follow-up data
                $followupData = $request->validate([
                    'followup_date'   => ['required', 'date'], // use 'date' instead of string
                    'content_summary' => ['nullable', 'string'],
                    'hospital_id'     => ['required', 'exists:hospitals,hospital_id'],
                ]);

                // 2️ Set follow-up status
                $followupData['followup_status'] = 'Transferred';
                $followupData['patient_id'] = $referral->patient_id; // make sure you have patient reference

                // 3️ Save the follow-up
                $followup = Followup::create($followupData);

                // 4 Create a new referral using the same referral_number
                $originalReferral = Referral::where('referral_number', $referral->referral_number)->latest()->first();

                if ($originalReferral) {
                    Referral::create([
                        'referral_number' => $originalReferral->referral_number, // same number
                        'patient_id'      => $patient->id,
                        'hospital_id'     => $followupData['hospital_id'], // new hospital
                        'status'          => 'New', // or whatever default status
                        'content'         => $originalReferral->content, // copy original content if needed
                        'created_by'      => auth()->id(),
                    ]);
                }

                break;

            case 'Death':
                $followupData = $request->validate([
                    'content_summary' => ['nullable', 'string'],
                ]);
                $followupData['followup_status'] = 'Closed';
                $referral->update(['status' => 'Closed']);
                break;
        }

        // Map content_summary → notes if present
        $followupData['notes'] = $followupData['content_summary'] ?? null;
        unset($followupData['content_summary']);

        // Merge common + outcome-specific data
        FollowUp::create(array_merge($baseFollowUpData, $followupData));

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
