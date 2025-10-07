<?php

namespace App\Http\Controllers\API\Patients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PatientHistory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Patient Histories",
 *     description="CRUD operations for patient histories"
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
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="List of patient histories")
     * )
     */
    public function index()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('view patient histories')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized', 'statusCode' => 401], 401);
        }

        $histories = PatientHistory::with('patient')->get();

        return response()->json([
            'status' => 'success',
            'data' => $histories,
            'statusCode' => 200
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/patient-histories",
     *     tags={"Patient Histories"},
     *     summary="Create a patient history",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"patient_id","referring_doctor","file_number","referring_date"},
     *                 @OA\Property(property="patient_id", type="integer"),
     *                 @OA\Property(property="referring_doctor", type="string"),
     *                 @OA\Property(property="file_number", type="string"),
     *                 @OA\Property(property="referring_date", type="string", format="date"),
     *                 @OA\Property(property="history_of_presenting_illness", type="string"),
     *                 @OA\Property(property="physical_findings", type="string"),
     *                 @OA\Property(property="investigations", type="string"),
     *                 @OA\Property(property="diagnosis", type="string"),
     *                 @OA\Property(property="management_done", type="string"),
     *                 @OA\Property(property="history_file", type="string", format="binary", description="Upload file (pdf, jpg, jpeg, png)")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Patient history created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('create patient histories')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized', 'statusCode' => 401], 401);
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => ['required', 'exists:patients,patient_id'],
            'referring_doctor' => ['required', 'string', 'max:255'],
            'file_number' => ['required', 'string', 'max:255'],
            'referring_date' => ['required', 'date'],
            'history_of_presenting_illness' => ['nullable', 'string'],
            'physical_findings' => ['nullable', 'string'],
            'investigations' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'management_done' => ['nullable', 'string'],
            'history_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors(), 'statusCode' => 422], 422);
        }

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($request->hasFile('history_file')) {
                $file = $request->file('history_file');
                $extension = $file->getClientOriginalExtension();
                $newFileName = 'patient_history_' . date('h-i-s_a_d-m-Y') . '.' . $extension;
                $file->move(public_path('uploads/patientLists/'), $newFileName);
                $filePath = 'uploads/patientLists/' . $newFileName;
            }

            $history = PatientHistory::create(array_merge(
                $request->only([
                    'patient_id', 'referring_doctor', 'file_number', 'referring_date',
                    'history_of_presenting_illness', 'physical_findings', 'investigations',
                    'diagnosis', 'management_done'
                ]),
                ['history_file' => $filePath]
            ));

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Patient history created successfully',
                'data' => $history->load('patient'),
                'statusCode' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error', 'error' => $e->getMessage(), 'statusCode' => 500], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/patient-histories/{id}",
     *     tags={"Patient Histories"},
     *     summary="Get a patient history by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Patient history data"),
     *     @OA\Response(response=404, description="Patient history not found")
     * )
     */
    public function show($id)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('view patient histories')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized', 'statusCode' => 401], 401);
        }

        $history = PatientHistory::with('patient')->find($id);
        if (!$history) {
            return response()->json(['status' => 'error', 'message' => 'Patient history not found', 'statusCode' => 404], 404);
        }

        return response()->json(['status' => 'success', 'data' => $history, 'statusCode' => 200]);
    }

    /**
     * @OA\Patch(
     *     path="/api/patient-histories/{id}",
     *     tags={"Patient Histories"},
     *     summary="Update a patient history",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="patient_id", type="integer"),
     *                 @OA\Property(property="referring_doctor", type="string"),
     *                 @OA\Property(property="file_number", type="string"),
     *                 @OA\Property(property="referring_date", type="string", format="date"),
     *                 @OA\Property(property="history_of_presenting_illness", type="string"),
     *                 @OA\Property(property="physical_findings", type="string"),
     *                 @OA\Property(property="investigations", type="string"),
     *                 @OA\Property(property="diagnosis", type="string"),
     *                 @OA\Property(property="management_done", type="string"),
     *                 @OA\Property(property="history_file", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Patient history updated successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Patient history not found")
     * )
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('update patient histories')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized', 'statusCode' => 401], 401);
        }

        $history = PatientHistory::find($id);
        if (!$history) {
            return response()->json(['status' => 'error', 'message' => 'Patient history not found', 'statusCode' => 404], 404);
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => ['sometimes', 'exists:patients,patient_id'],
            'referring_doctor' => ['sometimes', 'string', 'max:255'],
            'file_number' => ['sometimes', 'string', 'max:255'],
            'referring_date' => ['sometimes', 'date'],
            'history_of_presenting_illness' => ['nullable', 'string'],
            'physical_findings' => ['nullable', 'string'],
            'investigations' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'management_done' => ['nullable', 'string'],
            'history_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors(), 'statusCode' => 422], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->only([
                'patient_id', 'referring_doctor', 'file_number', 'referring_date',
                'history_of_presenting_illness', 'physical_findings', 'investigations',
                'diagnosis', 'management_done'
            ]) as $field => $value) {
                $history->{$field} = $value;
            }

            if ($request->hasFile('history_file')) {
                $file = $request->file('history_file');
                $extension = $file->getClientOriginalExtension();
                $newFileName = 'patient_history_' . date('h-i-s_a_d-m-Y') . '.' . $extension;
                $file->move(public_path('uploads/patientLists/'), $newFileName);
                $history->history_file = 'uploads/patientLists/' . $newFileName;
            }

            $history->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Patient history updated successfully',
                'data' => $history->load('patient'),
                'statusCode' => 200
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error', 'error' => $e->getMessage(), 'statusCode' => 500], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/patient-histories/{id}",
     *     tags={"Patient Histories"},
     *     summary="Delete a patient history",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Patient history deleted successfully"),
     *     @OA\Response(response=404, description="Patient history not found")
     * )
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('delete patient histories')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized', 'statusCode' => 401], 401);
        }

        $history = PatientHistory::find($id);
        if (!$history) {
            return response()->json(['status' => 'error', 'message' => 'Patient history not found', 'statusCode' => 404], 404);
        }

        $history->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Patient history deleted successfully',
            'statusCode' => 200
        ]);
    }
}
