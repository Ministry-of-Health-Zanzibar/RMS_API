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
use DB;


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
     *     tags={"Patient Histories"},
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
    // public function index()
    // {
    //     $user = auth()->user();

    //     if (!$user->can('View Patient History')) {
    //         return response()->json([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     $histories = PatientHistory::with([
    //         'patient.geographicalLocation',
    //         'diagnoses',
    //         'reason',

    //         // 🔹 Creator of the patient
    //         'patient.creator.hospitals' => function ($q) {
    //             $q->select(
    //                 'hospitals.hospital_id',
    //                 'hospitals.hospital_name'
    //             );
    //         },
    //     ])
    //     ->latest()
    //     ->get();

    //     return response()->json([
    //         'status' => true,
    //         'data' => $histories,
    //         'message' => 'Patient histories retrieved successfully',
    //         'statusCode' => 200
    //     ]);
    // }
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('View Patient History')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $histories = PatientHistory::with('patient.geographicalLocation', 'diagnoses', 'reason')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $histories,
            'message' => 'Patient histories retrieved successfully',
            'statusCode' => 200
        ]);
    }



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
     *     tags={"Patient Histories"},
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
                $diagnosisIds = collect($request->diagnosis_ids)->mapWithKeys(function ($id) {
                    return [$id => ['added_by' => 'doctor']];
                })->toArray();

                // Attach new diagnoses without detaching existing ones
                $history->diagnoses()->syncWithoutDetaching($diagnosisIds);
            }


            return response()->json([
                'status' => true,
                'data' => $history->load('patient', 'diagnoses', 'reason'),
                'message' => 'Patient history created successfully,',
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
     *     tags={"Patient Histories"},
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

        $history = PatientHistory::with([
            'patient.geographicalLocation',
            'diagnoses',
            'boardDiagnoses',
            'reason',
            'boardReason'
        ])->find($id);

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
     *     tags={"Patient Histories"},
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

        $validator = Validator::make($request->all(), [
            'patient_id' => 'nullable|exists:patients,patient_id', // optional if changing patient
            'reason_id' => 'nullable|exists:reasons,reason_id',
            'referring_doctor' => 'nullable|string',
            'file_number' => 'nullable|string',
            'referring_date' => 'nullable|string',
            'history_of_presenting_illness' => 'nullable|string',
            'physical_findings' => 'nullable|string',
            'investigations' => 'nullable|string',
            'management_done' => 'nullable|string',
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
                'patient_id',
                'reason_id',
                'referring_doctor',
                'file_number',
                'referring_date',
                'history_of_presenting_illness',
                'physical_findings',
                'investigations',
                'management_done',
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

            // Handle file upload
            if ($request->hasFile('history_file')) {
                // Delete old file if exists
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
     * @OA\Put(
     *     path="/api/patient-histories/{id}/medical-board",
     *     operationId="updateByMedicalBoard",
     *     tags={"Patient Histories"},
     *     summary="Update patient history by Medical Board",
     *     description="Allows a Medical Board member to update patient history status, comments, reason, and diagnoses. Automatically creates a referral if board diagnoses are provided.",
     *     security={{"bearerAuth": {}}},
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
     *             @OA\Property(property="status", type="string", enum={"pending","reviewed","Requested","approved","confirmed","rejected"}, example="reviewed", description="Workflow status"),
     *             @OA\Property(property="board_comments", type="string", example="Patient requires additional investigation", description="Comments by medical board"),
     *             @OA\Property(property="board_reason_id", type="integer", example=2, description="Reason ID associated with the board update"),
     *             @OA\Property(
     *                 property="board_diagnosis_ids",
     *                 type="array",
     *                 description="List of diagnosis IDs assigned by the medical board",
     *                 @OA\Items(type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient history updated and referral created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="patient_histories_id", type="integer", example=1),
     *                 @OA\Property(property="patient_id", type="integer", example=10),
     *                 @OA\Property(property="status", type="string", example="reviewed"),
     *                 @OA\Property(property="board_comments", type="string", example="Patient requires additional investigation"),
     *                 @OA\Property(property="board_reason_id", type="integer", example=2),
     *                 @OA\Property(property="diagnoses", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="diagnosis_id", type="integer", example=5),
     *                         @OA\Property(property="diagnosis_name", type="string", example="Hypertension")
     *                     )
     *                 ),
     *                 @OA\Property(property="patient", type="object",
     *                     @OA\Property(property="patient_id", type="integer", example=10),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="phone", type="string", example="255712345678")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Patient history updated and referral created successfully by Medical Board"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - user not a Medical Board member",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="statusCode", type="integer", example=422)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Update failed"),
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function updateByMedicalBoard(Request $request, $id)
    {
        $user = auth()->user();
        $history = PatientHistory::findOrFail($id);

        // Only medical board member allowed
        if (!$user->hasRole('ROLE MEDICAL BOARD MEMBER')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'board_comments'         => 'required|string',
            'board_reason_id'        => 'required|exists:reasons,reason_id',
            'board_diagnosis_ids'    => 'required|array',
            'board_diagnosis_ids.*'  => 'exists:diagnoses,diagnosis_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update board fields
            $history->update([
                'board_comments'  => $request->board_comments,
                'board_reason_id' => $request->board_reason_id,
            ]);

            // Attach or update board diagnoses safely
            if ($request->filled('board_diagnosis_ids')) {
                $boardDiagnoses = collect($request->board_diagnosis_ids)->mapWithKeys(function ($id) {
                    return [$id => ['added_by' => 'medical_board']];
                })->toArray();

                $history->boardDiagnoses()->syncWithoutDetaching($boardDiagnoses);
            }

            // Create referral
            $today = now()->format('Y-m-d');
            $count = Referral::whereDate('created_at', $today)->count() + 1;
            $referralNumber = 'REF-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $referral = Referral::create([
                'patient_id'      => $history->patient_id,
                'reason_id'       => $request->board_reason_id,
                'status'          => 'Requested',
                'referral_number' => $referralNumber,
                'created_by'      => $user->id,
            ]);

            // Attach diagnoses to referral
            $referral->diagnoses()->sync($request->board_diagnosis_ids);

            // Update workflow status
            $this->applyStatusUpdate($history, 'requested', $request->board_comments, $user);

            DB::commit();

            return response()->json([
                'status'  => true,
                'data'    => $history->load([
                    'patient',
                    'diagnoses',
                    'reason'
                ]),
                'message' => 'Patient history updated and referral created successfully',
                'statusCode' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Medical Board update failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage(),
                'statusCode' => 500
            ], 500);
        }
    }


    public function updateByMkurugenzi(Request $request, $id)
    {
        $user = auth()->user();

        // Load history + patient + referrals that are "Requested"
        $history = PatientHistory::with([
            'patient',
            'referrals' => function ($q) {
                $q->where('status', 'Requested');
            }
        ])->find($id);

        // history not found
        if (!$history) {
            return response()->json([
                'status' => false,
                'message' => 'Patient history not found.',
                'statusCode' => 404
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'mkurugenzi_tiba_comments' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        try {

            // -----------------------------------------
            // 1️ Save Mkurugenzi Tiba comments
            // -----------------------------------------
            $history->mkurugenzi_tiba_comments = $request->mkurugenzi_tiba_comments;
            $history->mkurugenzi_tiba_id = $user->id;
            $history->save();


            // -----------------------------------------
            // 2️ Find the Requested referral
            // -----------------------------------------
            $referral = $history->referrals->first();

            if (!$referral) {
                return response()->json([
                    'status' => false,
                    'message' => 'No referral with status "Requested" found for this history.',
                    'statusCode' => 404
                ], 404);
            }

            // -----------------------------------------
            // 3️ Update referral status
            // -----------------------------------------
            $referral->status = 'Pending';
            $referral->save();


            // -----------------------------------------
            // 4 Apply overall history status update
            // -----------------------------------------
            $this->applyStatusUpdate(
                $history,
                'approved',
                $request->mkurugenzi_tiba_comments,
                $user
            );

            // -----------------------------------------
            // 5 Return full response
            // -----------------------------------------
            return response()->json([
                'status' => true,
                'data' => [
                    'history'  => $history->load('patient', 'diagnoses', 'reason'),
                    'referral' => $referral->load('diagnoses', 'reason'),
                ],
                'message' => 'Referral approved updated successfully',
                'statusCode' => 200
            ]);

        } catch (\Exception $e) {

            Log::error('Medical Board update failed: ' . $e->getMessage());

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
     *     tags={"Patient Histories"},
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
     *     tags={"Patient Histories"},
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
     *     tags={"Patient Histories"},
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

    private function applyStatusUpdate(PatientHistory $history, $newStatus, $comment = null, $user = null)
    {
        $user = $user ?? auth()->user();

        // Ensure only valid status transitions
        if (!$this->isValidTransition($history->status, $newStatus)) {
            throw new \Exception("Invalid status transition from {$history->status} to {$newStatus}.");
        }

        switch ($newStatus) {
            case 'reviewed':
                $history->mkurugenzi_tiba_id = $user->id;
                $history->mkurugenzi_tiba_comments = $comment;
                break;

            case 'requested':
                $history->board_comments = $comment;
                break;

            case 'approved':
                $history->mkurugenzi_tiba_comments = $comment;
                break;

            case 'confirmed':
                $history->dg_id = $user->id;
                $history->dg_comments = $comment;
                break;

            case 'rejected':
                $history->board_comments = $comment;
                break;
        }

        $history->status = $newStatus;
        $history->save();

        return $history;
    }

}
