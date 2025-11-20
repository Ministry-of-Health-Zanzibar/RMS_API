<?php

namespace App\Http\Controllers\API\Patients;

use App\Http\Controllers\Controller;
use App\Models\PatientHistory;
use App\Models\Diagnosis;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Patient;


/**
 * @OA\Tag(
 *     name="Patient History",
 *     description="API Endpoints for managing patient histories"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Patient History",
 *     type="object",
 *     required={"patient_id"},
 *     @OA\Property(property="patient_histories_id", type="integer", example=1),
 *     @OA\Property(property="patient_id", type="integer", example=1),
 *     @OA\Property(property="referring_doctor", type="string", example="Dr. John Doe"),
 *     @OA\Property(property="file_number", type="string", example="F12345"),
 *     @OA\Property(property="referring_date", type="string", format="date"),
 *     @OA\Property(property="history_of_presenting_illness", type="string"),
 *     @OA\Property(property="physical_findings", type="string"),
 *     @OA\Property(property="investigations", type="string"),
 *     @OA\Property(property="management_done", type="string"),
 *     @OA\Property(property="board_comments", type="string"),
 *     @OA\Property(property="history_file", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PatientHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/patient-histories",
     *     tags={"Patient History"},
     *     summary="Get all patient histories",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of patient histories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Patient History")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('View Patient History')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $histories = PatientHistory::with('patient', 'diagnoses', 'reason')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $histories,
            'message' => 'Patient histories retrieved successfully',
            'statusCode' => 200
        ]);
    }

    // public function getPatientToBeAssignedToMedicalBoard()
    // {
    //     $user = auth()->user();

    //     if (!$user->can('View Patient History')) {
    //         return response()->json([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     // STEP 1: FILTER PATIENTS
    //     $patients = Patient::query()
    //         // Only patients whose latest history is reviewed
    //         ->whereHas('latestHistory', function ($q) {
    //             $q->where('status', 'reviewed');
    //         })
    //         // Patients NOT in any PatientList
    //         ->whereDoesntHave('patientList')
    //         // Patients with NO referral OR referral.status = 'closed'
    //         ->where(function ($q) {
    //             $q->whereDoesntHave('referrals')
    //             ->orWhereHas('referrals', function ($ref) {
    //                 $ref->where('status', 'closed');
    //             });
    //         })
    //         ->with([
    //             'latestHistory' => function ($q) {
    //                 $q->with(['patient', 'diagnoses', 'reason']);
    //             }
    //         ])
    //         ->latest()
    //         ->get();

    //     // STEP 2: Extract only the latestHistory from each patient
    //     $histories = $patients->pluck('latestHistory')->filter();

    //     return response()->json([
    //         'status' => true,
    //         'data' => $histories->values(), // reset array keys
    //         'message' => 'Patient histories retrieved successfully',
    //         'statusCode' => 200
    //     ]);
    // }

//     public function getPatientToBeAssignedToMedicalBoard()
// {
//     $user = auth()->user();

//     // Permission check
//     if (!$user->canAny(['View Patient', 'View History'])) {
//         return response()->json([
//             'message' => 'Forbidden',
//             'statusCode' => 403
//         ], 403);
//     }

//     // Base query: patients with at least one history
//     $query = Patient::whereHas('patientHistories')
//         ->with(['latestHistory' => function ($q) {
//             // Only load latest history that is reviewed
//             $q->where('status', 'reviewed')
//               ->with(['patient', 'diagnoses', 'reason']);
//         }])
//         ->latest();

//     // ROLE: Medical Board Member -> only patients whose latest history is reviewed
//     if ($user->hasRole('ROLE MEDICAL BOARD MEMBER')) {
//         $query->whereHas('patientHistories', function ($q) {
//             $q->where('status', 'reviewed');
//         });
//     }

//     // Get patients
//     $patients = $query->get();

//     // Extract latest reviewed histories
//     $histories = $patients->map(function ($patient) {
//         return $patient->latestHistory;
//     })->filter(); // remove nulls

//     // Transform to clean JSON (optional)
//     $result = $histories->map(function ($history) {
//         return [
//             'patient_histories_id' => $history->patient_histories_id,
//             'patient_id' => $history->patient_id,
//             'referring_doctor' => $history->referring_doctor,
//             'file_number' => $history->file_number,
//             'referring_date' => $history->referring_date,
//             'reason_id' => $history->reason_id,
//             'history_of_presenting_illness' => $history->history_of_presenting_illness,
//             'physical_findings' => $history->physical_findings,
//             'investigations' => $history->investigations,
//             'management_done' => $history->management_done,
//             'board_comments' => $history->board_comments,
//             'history_file' => $history->history_file,
//             'created_at' => $history->created_at,
//             'updated_at' => $history->updated_at,
//             'deleted_at' => $history->deleted_at,
//             'status' => $history->status,
//             'mkurugenzi_tiba_comments' => $history->mkurugenzi_tiba_comments,
//             'dg_comments' => $history->dg_comments,
//             'mkurugenzi_tiba_id' => $history->mkurugenzi_tiba_id,
//             'dg_id' => $history->dg_id,
//             'board_reason_id' => $history->board_reason_id,
//             'patient' => [
//                 'patient_id' => $history->patient->patient_id,
//                 'name' => $history->patient->name,
//                 'matibabu_card' => $history->patient->matibabu_card,
//                 'date_of_birth' => $history->patient->date_of_birth,
//                 'gender' => $history->patient->gender,
//                 'phone' => $history->patient->phone,
//                 'location_id' => $history->patient->location_id,
//                 'job' => $history->patient->job,
//                 'position' => $history->patient->position,
//                 'created_by' => $history->patient->created_by,
//                 'created_at' => $history->patient->created_at,
//                 'updated_at' => $history->patient->updated_at,
//                 'deleted_at' => $history->patient->deleted_at,
//                 'zan_id' => $history->patient->zan_id,
//             ],
//             'diagnoses' => $history->diagnoses->map(function ($d) {
//                 return [
//                     'diagnosis_id' => $d->diagnosis_id,
//                     'diagnosis_name' => $d->diagnosis_name,
//                     'pivot' => [
//                         'patient_histories_id' => $d->pivot->patient_histories_id,
//                         'diagnosis_id' => $d->pivot->diagnosis_id,
//                     ],
//                 ];
//             }),
//             'reason' => $history->reason ? [
//                 'reason_id' => $history->reason->reason_id,
//                 'referral_reason_name' => $history->reason->referral_reason_name,
//             ] : null,
//         ];
//     });

//     return response()->json([
//         'status' => true,
//         'data' => $result->values(),
//         'message' => 'Patient histories retrieved successfully',
//         'statusCode' => 200
//     ]);
// }


public function getPatientToBeAssignedToMedicalBoard(Request $request)
{
    $user = auth()->user();

    // Permission check
    if (!$user->canAny(['View Patient', 'View History'])) {
        return response()->json([
            'message' => 'Forbidden',
            'statusCode' => 403
        ], 403);
    }

    // Optional patient list filter
    $patientListId = $request->input('patient_list_id');

    // Query patients whose latest history is reviewed
    $patients = Patient::whereHas('latestHistory', function ($q) {
            $q->where('status', 'reviewed');
        })
        ->when($patientListId, function ($q) use ($patientListId) {
            // Exclude patients already in the given patient list
            $q->whereDoesntHave('patientList', function ($query) use ($patientListId) {
                $query->where('patient_list_id', $patientListId);
            });
        })
        ->when(!$patientListId, function ($q) {
            // Exclude patients in any patient list
            $q->whereDoesntHave('patientList');
        })
        ->with(['latestHistory' => function ($q) {
            $q->where('status', 'reviewed')
              ->with(['patient', 'diagnoses', 'reason']);
        }])
        ->latest()
        ->get();

    // Map to required fields
    $result = $patients->map(function ($patient) {
        return [
            'patient_id' => $patient->patient_id,
            'name' => $patient->name,
            'phone' => $patient->phone,
            'latest_history_id' => $patient->latestHistory->patient_histories_id ?? null,
            'latest_history_status' => $patient->latestHistory->status ?? null,
        ];
    });

    return response()->json([
        'status' => true,
        'data' => $result->values(),
        'message' => 'Patients retrieved successfully',
        'statusCode' => 200
    ]);
}







    /**
     * @OA\Post(
     *     path="/api/patient-histories",
     *     tags={"Patient History"},
     *     summary="Create a new patient history",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"patient_id"},
     *             @OA\Property(property="patient_id", type="integer"),
     *             @OA\Property(property="reason_id", type="integer"),
     *             @OA\Property(property="referring_doctor", type="string"),
     *             @OA\Property(property="file_number", type="string"),
     *             @OA\Property(property="referring_date", type="string", format="date"),
     *             @OA\Property(property="history_of_presenting_illness", type="string"),
     *             @OA\Property(property="physical_findings", type="string"),
     *             @OA\Property(property="investigations", type="string"),
     *             @OA\Property(property="management_done", type="string"),
     *             @OA\Property(property="board_comments", type="string"),
     *             @OA\Property(property="diagnosis_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="history_file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Patient history created successfully"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Patient History')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,patient_id',
            'reason_id' => 'required|exists:reasons,reason_id',
            'referring_doctor' => 'nullable|string',
            'file_number' => 'nullable|string',
            'referring_date' => 'nullable|string',
            'history_of_presenting_illness' => 'nullable|string',
            'physical_findings' => 'nullable|string',
            'investigations' => 'nullable|string',
            'management_done' => 'nullable|string',
            'board_comments' => 'nullable|string',
            'diagnosis_ids' => 'nullable|array',
            'diagnosis_ids.*' => 'exists:diagnoses,diagnosis_id',
            'history_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        try {
            // Extract only allowed data
            $data = $request->only([
                'patient_id',
                'reason_id',
                'referring_doctor',
                'file_number',
                'referring_date',
                'history_of_presenting_illness',
                'physical_findings',
                'investigations',
                'management_done',
                'board_comments'
            ]);

            $data['created_by'] = auth()->id();

            // Normalize referring_date
            if (empty($data['referring_date']) || strtolower($data['referring_date']) === 'default') {
                $data['referring_date'] = null;
            } else {
                try {
                    $data['referring_date'] = \Carbon\Carbon::parse($data['referring_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $data['referring_date'] = null;
                }
            }

            // Handle optional file upload
            if ($request->hasFile('history_file')) {
                $file = $request->file('history_file');
                $fileName = 'history_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/historyFiles/'), $fileName);
                $data['history_file'] = 'uploads/historyFiles/' . $fileName;
            }

            // Create patient history
            $history = PatientHistory::create($data);

            // Attach diagnoses if provided
            if ($request->filled('diagnosis_ids')) {

                // Sync diagnoses to the history
                $history->diagnoses()->sync($request->diagnosis_ids);

                // Automatically create a referral for this patient history

                // Generate referral number
                $today = now()->format('Y-m-d');
                $count = Referral::whereDate('created_at', $today)->count() + 1;
                $referralNumber = 'REF-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

                $referral = Referral::create([
                    'patient_id' => $request->patient_id,
                    'reason_id' => $request->reason_id,
                    'status' => 'Pending',
                    'referral_number' => $referralNumber,
                    'created_by' => Auth::id(),
                ]);

                // Attach the same diagnoses to the referral via pivot table
                $referral->diagnoses()->sync($request->diagnosis_ids);
            }

            return response()->json([
                'status' => true,
                'data' => $history->load('patient', 'diagnoses', 'reason'),
                'message' => 'Patient history created and Referral requested successfully,',
                'statusCode' => 201
            ], 201);

        } catch (\Exception $e) {
            Log::error('Patient history creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Creation failed',
                'error' => $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/patient-histories/{id}",
     *     tags={"Patient History"},
     *     summary="Get a patient history by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Patient history found"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show($id)
    {
        $user = auth()->user();

        if (!$user->canAny(['View Patient History', 'View History'])) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $history = PatientHistory::with('patient', 'diagnoses')->find($id);

        if (!$history) {
            return response()->json([
                'status' => false,
                'message' => 'Patient history not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $history,
            'message' => 'Patient history retrieved successfully',
            'statusCode' => 200
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/patient-histories/update/{id}",
     *     tags={"Patient History"},
     *     summary="Update patient history",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="reason_id", type="integer"),
     *             @OA\Property(property="referring_doctor", type="string"),
     *             @OA\Property(property="file_number", type="string"),
     *             @OA\Property(property="referring_date", type="string", format="date"),
     *             @OA\Property(property="history_of_presenting_illness", type="string"),
     *             @OA\Property(property="physical_findings", type="string"),
     *             @OA\Property(property="investigations", type="string"),
     *             @OA\Property(property="management_done", type="string"),
     *             @OA\Property(property="board_comments", type="string"),
     *             @OA\Property(property="diagnosis_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="history_file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated successfully"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->can('Update Patient History')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $history = PatientHistory::findOrFail($id);

        if (!$history) {
            return response()->json([
                'status' => false,
                'message' => 'Patient history not found',
                'statusCode' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'reason_id' => 'nullable|exists:reasons,reason_id',
            'referring_doctor' => 'nullable|string',
            'file_number' => 'nullable|string',
            'referring_date' => 'nullable|string',
            'history_of_presenting_illness' => 'nullable|string',
            'physical_findings' => 'nullable|string',
            'investigations' => 'nullable|string',
            'management_done' => 'nullable|string',
            'board_comments' => 'nullable|string',
            'diagnosis_ids' => 'nullable|array',
            'diagnosis_ids.*' => 'exists:diagnoses,diagnosis_id',
            'history_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        try {
            $data = $request->only([
                'reason_id',
                'referring_doctor',
                'file_number',
                'referring_date',
                'history_of_presenting_illness',
                'physical_findings',
                'investigations',
                'management_done',
                'board_comments'
            ]);

            // Normalize referring_date
            if (empty($data['referring_date']) || strtolower($data['referring_date']) === 'default') {
                $data['referring_date'] = null;
            } else {
                try {
                    $data['referring_date'] = \Carbon\Carbon::parse($data['referring_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $data['referring_date'] = null;
                }
            }

            // Handle optional file upload
            if ($request->hasFile('history_file')) {
                // Remove old file if exists
                if ($history->history_file && file_exists(public_path($history->history_file))) {
                    unlink(public_path($history->history_file));
                }

                $file = $request->file('history_file');
                $fileName = 'history_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/historyFiles/'), $fileName);
                $data['history_file'] = 'uploads/historyFiles/' . $fileName;
            }

            // Update patient history
            $history->update($data);

            // Sync diagnoses if provided
            if ($request->filled('diagnosis_ids')) {
                $history->diagnoses()->sync($request->diagnosis_ids);
            }

            return response()->json([
                'status' => true,
                'data' => $history->load('patient', 'diagnoses', 'reason'),
                'message' => 'Patient history updated successfully',
                'statusCode' => 200
            ]);

        } catch (\Exception $e) {
            Log::error('Patient history update failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/patient-histories/{id}",
     *     tags={"Patient History"},
     *     summary="Delete patient history",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted successfully"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->can('Delete Patient History')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $history = PatientHistory::findOrFail($id);
        $history->delete();

        return response()->json([
            'status' => true,
            'message' => 'Patient history deleted successfully',
            'statusCode' => 200
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/patient-histories/unblock/{id}",
     *     tags={"Patient History"},
     *     summary="Unblock a patient history",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Unblocked successfully"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function unblock($id)
    {
        $user = auth()->user();
        if (!$user->can('Update Patient History')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $history = PatientHistory::withTrashed()->findOrFail($id);
        $history->restore();

        return response()->json([
            'status' => true,
            'message' => 'Patient history unblocked successfully',
            'statusCode' => 200
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/patient-histories/update-status/{id}",
     *     tags={"Patient History"},
     *     summary="Update patient history status",
     *     description="Allows authorized roles to update the status of a patient history record, add comments, and track reviewers.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient history ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending","reviewed","requested","approved","confirmed","rejected"},
     *                 description="New status to set"
     *             ),
     *             @OA\Property(
     *                 property="comment",
     *                 type="string",
     *                 description="Optional comment for the reviewer"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status transition",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action for your role",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient history not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $history = PatientHistory::findOrFail($id);

        // --- Use Validator instead of $request->validate() ---
        $validator = \Validator::make($request->all(), [
            'status' => 'required|string|in:pending,reviewed,requested,approved,confirmed,rejected',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $newStatus = $validated['status'];
        $comment = $validated['comment'] ?? null;

        // Ensure only valid status transitions
        if (!$this->isValidTransition($history->status, $newStatus)) {
            return response()->json([
                'success' => false,
                'message' => "Invalid status transition from {$history->status} to {$newStatus}."
            ], 400);
        }

        // Role-based action control
        switch ($newStatus) {
            case 'reviewed':
                if (!$user->hasRole('ROLE MKURUGENZI TIBA')) {
                    return $this->unauthorized();
                }
                $history->mkurugenzi_tiba_id = $user->id;
                $history->mkurugenzi_tiba_comments = $comment;
                break;

            case 'requested':
                if (!$user->hasRole('ROLE MEDICAL BOARD MEMBER')) {
                    return $this->unauthorized();
                }
                // $history->medical_board_id = $user->id;
                $history->board_comments = $comment;
                break;

            case 'approved':
                if (!$user->hasRole('ROLE MKURUGENZI TIBA')) {
                    return $this->unauthorized();
                }
                $history->mkurugenzi_tiba_comments = $comment;
                break;

            case 'confirmed':
                if (!$user->hasRole('ROLE DIRECTOR GENERAL')) {
                    return $this->unauthorized();
                }
                $history->dg_id = $user->id;
                $history->dg_comments = $comment;
                break;

            case 'rejected':
                if (!$user->hasAnyRole(['ROLE MKURUGENZI TIBA', 'ROLE MEDICAL BOARD MEMBER', 'ROLE DIRECTOR GENERAL'])) {
                    return $this->unauthorized();
                }
                $history->board_comments = $comment;
                break;
        }

        $history->status = $newStatus;
        $history->save();

        return response()->json([
            'success' => true,
            'message' => "Status updated successfully to {$newStatus}.",
            'data' => $history,
        ]);
    }

    /**
     * Define allowed workflow transitions
     */
    private function isValidTransition($current, $next)
    {
        $allowed = [
            'pending' => ['reviewed'],          // hospital → director
            'reviewed' => ['requested', 'approved'], // director → medical board / approve to DG
            'requested' => ['approved'],          // medical board → director
            'approved' => ['confirmed', 'rejected'], // director → DG
            'confirmed' => [],                    // DG final
            'rejected' => [],                    // terminal
        ];

        return in_array($next, $allowed[$current] ?? []);
    }

    private function unauthorized()
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized action for your role.',
            'statusCode' => 403
        ], 403);
    }
}
