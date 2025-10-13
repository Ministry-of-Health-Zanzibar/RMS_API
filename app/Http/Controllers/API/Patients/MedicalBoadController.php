<?php

namespace App\Http\Controllers\API\Patients;

use App\Models\PatientList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MedicalBoadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Patient List|Create Patient List|Update Patient List|Delete Patient List', 
            ['only' => ['index', 'store', 'show', 'updatePatientList', 'destroy', 'unBlockParentList', 'getAllPatientsByPatientListId']]);
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

        $lists = PatientList::with(['creator', 'patients.geographicalLocation'])
            ->when(!$user->hasRole('ROLE ADMIN'), fn($q) => $q) // normal users see only their lists?
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
     *             @OA\Property(property="board_type", type="string", enum={"EMG","RTN"}, example="EMG"),
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
            'patient_list_title' => ['required', 'string', 'max:255'],
            'board_type' => ['required', 'in:Emergency,Routine'],
            'patient_list_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'board_date' => ['required', 'string'],
            'no_of_patients' => ['required', 'integer', 'min:1'],
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

        $list = PatientList::create([
            'patient_list_title' => $request->patient_list_title,
            'patient_list_file' => $filePath,
            'board_type' => $request->board_type,
            'board_date' => $request->board_date ?? null,
            'no_of_patients' => $request->no_of_patients ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'data' => $list,
            'message' => 'Patient list created successfully',
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

        $list = PatientList::with(['creator', 'patients'])->find($id);

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
            'patient_list_title' => ['required', 'string', 'max:255'],
            'board_type' => ['nullable', 'in:Emergency,Routine'],
            'patient_list_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'board_date' => ['nullable', 'date'],
            'no_of_patients' => ['nullable', 'integer', 'min:1'],
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

        $list->update([
            'patient_list_title' => $request->patient_list_title,
            'patient_list_file' => $filePath,
            'board_type' => $request->board_type ?? $list->board_type,
            'board_date' => $request->board_date ?? $list->board_date,
            'no_of_patients' => $request->no_of_patients ?? $list->no_of_patients,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'data' => $list->load(['creator', 'patients']),
            'message' => 'Patient list updated successfully',
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

        $list = PatientList::with(['patients.files', 'patients.geographicalLocation'])
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
}
