<?php

namespace App\Http\Controllers\API\Patients;

use App\Models\Patient;
use App\Models\PatientList;
use App\Models\PatientHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MedicalBoadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/patient-lists",
     *     summary="Get all patient lists",
     *     tags={"Patient Lists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="statusCode",
     *                 type="integer",
     *                 example=200
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="patient_list_id", type="integer"),
     *                     @OA\Property(property="patient_list_title", type="string"),
     *                     @OA\Property(property="board_type", type="string"),
     *                     @OA\Property(property="board_date", type="string", format="date"),
     *                     @OA\Property(property="no_of_patients", type="integer"),
     *                     @OA\Property(property="reference_number", type="string"),
     *                     @OA\Property(property="patient_list_file", type="string"),
     *                     @OA\Property(property="created_by", type="integer"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->can('View Patient List')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $lists = PatientList::with(['creator', 'patients.geographicalLocation', 'boardMembers'])
            // ->when(!$user->hasRole('ROLE ADMIN'), function ($q) use ($user) {
            //     // 👇 Example filter for non-admins
            //     $q->where('created_by', $user->id);
            // })
            ->withTrashed()
            ->get();

        return response()->json([
            'data' => $lists,
            'statusCode' => 200
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/patient-lists",
     *     summary="Create a new patient list",
     *     tags={"Patient Lists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="patient_list_title", type="string", example="Morning Board"),
     *             @OA\Property(property="board_type", type="string", enum={"Emergency","Routine"}, example="Emergency"),
     *             @OA\Property(property="board_date", type="string", format="date", example="2025-10-13"),
     *             @OA\Property(property="no_of_patients", type="integer", example=5),
     *             @OA\Property(property="patient_list_file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient list created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Patient list created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="patient_list_id", type="integer", example=1),
     *                 @OA\Property(property="patient_list_title", type="string", example="Morning Board"),
     *                 @OA\Property(property="board_type", type="string", example="Emergency"),
     *                 @OA\Property(property="board_date", type="string", format="date", example="2025-10-13"),
     *                 @OA\Property(property="no_of_patients", type="integer", example=5),
     *                 @OA\Property(property="reference_number", type="string", example="REFF-2025-10-13-EMG-005"),
     *                 @OA\Property(property="patient_list_file", type="string", example="uploads/patientLists/patient_list_13-10-2025.pdf"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-13T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-13T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Patient List')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'board_type' => ['required', 'in:Emergency,Routine'],
            'patient_list_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'board_date' => ['required', 'string'],
            'no_of_patients' => ['required', 'integer', 'min:1'],
            'user_id' => ['required', 'array'], // array of user IDs
            'user_id.*' => ['integer', 'exists:users,id'], // validate each ID
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('patient_list_file')) {
            $file = $request->file('patient_list_file');
            $newFileName = 'patient_list_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/patientLists/'), $newFileName);
            $filePath = 'uploads/patientLists/' . $newFileName;
        }

        // Create the patient list
        $list = PatientList::create([
            'patient_list_file' => $filePath,
            'board_type' => $request->board_type,
            'board_date' => $request->board_date ?? null,
            'no_of_patients' => $request->no_of_patients ?? null,
            'created_by' => $user->id,
        ]);

        // Attach board members to pivot table
        if ($request->filled('user_id')) {
            $list->boardMembers()->sync($request->user_id);
        }

        return response()->json([
            'data' => $list->load('boardMembers'), // load assigned users
            'message' => 'Medical Board Meeting created successfully',
            'statusCode' => 200
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/patient-lists/{id}",
     *     summary="Get a patient list by ID",
     *     tags={"Patient Lists"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Patient list ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient list retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Patient list retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="patient_list_id", type="integer", example=1),
     *                 @OA\Property(property="patient_list_title", type="string", example="Morning Board"),
     *                 @OA\Property(property="board_type", type="string", example="EMG"),
     *                 @OA\Property(property="board_date", type="string", format="date", example="2025-10-13"),
     *                 @OA\Property(property="no_of_patients", type="integer", example=5),
     *                 @OA\Property(property="reference_number", type="string", example="REFF-2025-10-13-EMG-005"),
     *                 @OA\Property(property="patient_list_file", type="string", example="uploads/patientLists/patient_list_13-10-2025.pdf"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-13T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-13T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Patient list not found")
     * )
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->can('View Patient List')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $list = PatientList::with(['creator', 'patients', 'boardMembers'])->find($id);

        if (!$list) {
            return response()->json([
                'message' => 'Patient list not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $list,
            'statusCode' => 200
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/patient-lists/{id}",
     *     summary="Update a patient list",
     *     tags={"Patient Lists"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient list ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="patient_list_title", type="string"),
     *             @OA\Property(property="board_type", type="string", enum={"Emergency","Routine"}),
     *             @OA\Property(property="board_date", type="string", format="date"),
     *             @OA\Property(property="no_of_patients", type="integer"),
     *             @OA\Property(property="patient_list_file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Patient list updated successfully"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updatePatientList(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->can('Update Patient List')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $list = PatientList::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'board_type' => ['nullable', 'in:Emergency,Routine'],
            'patient_list_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'board_date' => ['nullable', 'date'],
            'no_of_patients' => ['nullable', 'integer', 'min:1'],
            'user_id' => ['nullable', 'array'], // array of user IDs
            'user_id.*' => ['integer', 'exists:users,id'], // validate each ID
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        // Handle file upload
        $filePath = $list->patient_list_file;
        if ($request->hasFile('patient_list_file')) {
            if ($filePath && file_exists(public_path($filePath))) {
                unlink(public_path($filePath));
            }
            $file = $request->file('patient_list_file');
            $newFileName = 'patient_list_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/patientLists/'), $newFileName);
            $filePath = 'uploads/patientLists/' . $newFileName;
        }

        // Determine updated fields
        $boardType = $request->board_type ?? $list->board_type;
        $numPatients = $request->no_of_patients ?? $list->no_of_patients;
        $boardDateInput = $request->board_date ?? $list->board_date;

        // Parse board_date safely
        try {
            $boardDate = \Carbon\Carbon::parse($boardDateInput);
        } catch (\Exception $e) {
            $boardDate = \Carbon\Carbon::createFromTimestamp(strtotime($boardDateInput));
        }

        $formattedDateForRef = $boardDate->format('d/m/Y');
        $formattedDateForTitle = $boardDate->format('d/m/Y');

        $bt = ucfirst(strtolower($boardType));
        switch ($bt) {
            case 'Emergency':
                $boardTypeAbbr = 'EMG';
                break;
            case 'Routine':
                $boardTypeAbbr = 'RTN';
                break;
            default:
                $boardTypeAbbr = substr($boardType, 0, 3);
                break;
        }


        // Update the model
        $list->update([
            'patient_list_file' => $filePath,
            'board_type' => $boardType,
            'board_date' => $boardDate->toDateString(),
            'no_of_patients' => $numPatients,
            'reference_number' => sprintf(
                'MBM-%s-%s-%s-%s',
                $formattedDateForRef,
                $boardTypeAbbr,
                str_pad($numPatients, 3, '0', STR_PAD_LEFT),
                now()->format('H-i')
            ),
            'patient_list_title' => sprintf(
                'MBM of %s at %s',
                $formattedDateForTitle,
                now()->format('h:i a')
            ),
            'updated_by' => $user->id,
        ]);

        // Sync board members (if provided)
        if ($request->filled('user_id')) {
            $list->boardMembers()->sync($request->user_id);
        }

        return response()->json([
            'data' => $list->load(['creator', 'patients', 'boardMembers']),
            'message' => 'Patient list updated successfully with board members',
            'statusCode' => 200
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/patient-lists/{id}",
     *     summary="Soft delete a patient list",
     *     tags={"Patient Lists"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient list ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Patient list deleted successfully"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Patient list not found")
     * )
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->can('Delete Patient List')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $list = PatientList::find($id);
        if (!$list) {
            return response()->json([
                'message' => 'Patient list not found',
                'statusCode' => 404
            ], 404);
        }

        $list->delete();

        return response()->json([
            'message' => 'Patient list deleted successfully',
            'statusCode' => 200
        ]);
    }


    /**
     * @OA\Put(
     *     path="/api/patient-lists/unblock/{id}",
     *     summary="Restore a soft-deleted patient list",
     *     tags={"Patient Lists"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient list ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Patient list restored successfully"),
     *     @OA\Response(response=404, description="Patient list not found")
     * )
     */
    public function unBlockParentList($id)
    {
        $list = PatientList::withTrashed()->find($id);
        if (!$list) {
            return response()->json([
                'message' => 'Patient list not found',
                'statusCode' => 404
            ], 404);
        }

        $list->restore();

        return response()->json([
            'message' => 'Patient list restored successfully',
            'statusCode' => 200
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/patient-lists/{id}/patients",
     *     summary="Get all patients by patient list ID",
     *     tags={"Patient Lists"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient list ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of patients"),
     *     @OA\Response(response=404, description="Patient list not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function getAllPatientsByPatientListId(int $patientListId)
    {
        $user = auth()->user();
        if (!$user->can('View Patient List')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $list = PatientList::with(['patients.files', 'patients.geographicalLocation','boardMembers'])
            ->find($patientListId);

        if (!$list) {
            return response()->json([
                'message' => 'Patient list not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $list,
            'statusCode' => 200
        ]);
    }

    // public function assignPatientsToList(Request $request, int $patientListId)
    // {
    //     $user = auth()->user();

    //     // Check permission
    //     if (!$user->can('Create Patient List')) {
    //         return response()->json([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     // Validate request
    //     $validator = Validator::make($request->all(), [
    //         'patient_ids'   => ['required', 'array', 'min:1'],
    //         'patient_ids.*' => ['integer', 'exists:patients,patient_id'],
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'errors' => $validator->errors(),
    //             'statusCode' => 422,
    //         ], 422);
    //     }

    //     // Find the target list
    //     $patientList = PatientList::find($patientListId);
    //     if (!$patientList) {
    //         return response()->json([
    //             'message' => 'Patient list not found',
    //             'statusCode' => 404,
    //         ], 404);
    //     }

    //     // Check capacity
    //     $currentCount = $patientList->patients()->count();
    //     $remainingCapacity = $patientList->no_of_patients - $currentCount;

    //     $incomingCount = count($request->patient_ids);

    //     if ($incomingCount > $remainingCapacity) {
    //         return response()->json([
    //             'message' => "Cannot assign {$incomingCount} patients. Only {$remainingCapacity} spots left in this list.",
    //             'statusCode' => 422,
    //         ], 422);
    //     }

    //     // Attach patients to list (avoid duplicates automatically)
    //     $patientList->patients()->syncWithoutDetaching($request->patient_ids);

    //     return response()->json([
    //         'message' => 'Patients successfully assigned to Medical Board Meeting.',
    //         'data' => [
    //             'patient_list_id' => $patientList->patient_list_id,
    //             'assigned_patients' => $request->patient_ids,
    //             'total_assigned' => $patientList->patients()->count(),
    //         ],
    //         'statusCode' => 200,
    //     ], 200);
    // }
    public function assignPatientsToList(Request $request, int $patientListId)
{
    $user = auth()->user();

    // Check permission
    if (!$user->can('Create Patient List')) {
        return response()->json(['message' => 'Forbidden', 'statusCode' => 403], 403);
    }

    // Validate request
    $validator = Validator::make($request->all(), [
        'patient_ids'   => ['required', 'array', 'min:1'],
        'patient_ids.*' => ['integer', 'exists:patients,patient_id'],
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'errors' => $validator->errors(), 'statusCode' => 422], 422);
    }

    $patientList = PatientList::find($patientListId);
    if (!$patientList) {
        return response()->json(['message' => 'Patient list not found', 'statusCode' => 404], 404);
    }

    // Check capacity
    $currentCount = $patientList->patients()->count();
    $remainingCapacity = $patientList->no_of_patients - $currentCount;
    $incomingCount = count($request->patient_ids);

    if ($incomingCount > $remainingCapacity) {
        return response()->json([
            'message' => "Cannot assign {$incomingCount} patients. Only {$remainingCapacity} spots left.",
            'statusCode' => 422,
        ], 422);
    }

    // Use a Transaction to ensure both the pivot and the status update happen together
    try {
        DB::beginTransaction();

        // 1. Attach to pivot table
        $patientList->patients()->syncWithoutDetaching($request->patient_ids);

        // 2. Update status of the latest history for each patient to 'assigned'
        foreach ($request->patient_ids as $pId) {
            $patient = Patient::find($pId);
            $history = $patient->latestHistory;

            // Only update if current status is 'reviewed' (to follow your isValidTransition logic)
            if ($history && $history->status === 'reviewed') {
                $this->applyStatusUpdate($history, 'assigned');
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Patients successfully assigned to Medical Board Meeting.',
            'data' => [
                'patient_list_id' => $patientList->patient_list_id,
                'assigned_patients' => $request->patient_ids,
                'total_assigned' => $patientList->patients()->count(),
            ],
            'statusCode' => 200,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to assign patients: ' . $e->getMessage(),
            'statusCode' => 500
        ], 500);
    }
}

private function isValidTransition($current, $next)
    {
        $allowed = [
            'pending'   => ['reviewed'],
            'reviewed'  => ['assigned', 'requested'], // Director can assign to board or request info
            'assigned'  => ['requested', 'approved'], // Board's primary actions
            'requested' => ['reviewed', 'approved'],  // Path after info is provided
            'approved'  => ['confirmed', 'rejected'], // Moves to DG for final say
            'confirmed' => [],
            'rejected'  => [],
        ];

        return in_array($next, $allowed[$current] ?? []);
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

            case 'assigned':
                $history->board_comments = $comment;
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
