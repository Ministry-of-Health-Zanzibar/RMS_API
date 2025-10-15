<?php

namespace App\Http\Controllers\API\Patients;

use App\Models\Patient;
use App\Models\PatientFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/patients",
     *     summary="Get all patients",
     *     tags={"patients"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="patient_id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="date_of_birth", type="string", format="date-time"),
     *                     @OA\Property(property="gender", type="string"),
     *                     @OA\Property(property="phone", type="string"),
     *                     @OA\Property(property="location", type="string"),
     *                     @OA\Property(property="job", type="string"),
     *                     @OA\Property(property="position", type="string"),
     *                     @OA\Property(property="patient_list_id", type="integer"),
     *                     @OA\Property(property="created_by", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->can('View Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        if ($user->hasAnyRole(['ROLE ADMIN'])) {
            $patients = Patient::with([
                'patientList',          // patient list info
                'files',                // all patient files
                'geographicalLocation',
                'referrals.reason',     // referrals + reason
                'referrals.hospital',   // referrals + hospital
                'referrals.creator',  // referral created by user
            ])
            ->withTrashed()
            ->get();
        } else {
             $patients = Patient::with([
                'patientList',          // patient list info
                'files',                // all patient files
                'geographicalLocation',
                'referrals.reason',     // referrals + reason
                'referrals.hospital',   // referrals + hospital
                'referrals.creator',  // referral created by user
            ])
            ->get();
        }

        
        return response([
            'data' => $patients,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/patients",
     *     summary="Create patient",
     *     tags={"patients"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","patient_list_id"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="matibabu_card", type="string"),
     *                 @OA\Property(property="zan_id", type="string"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date"),
     *                 @OA\Property(property="gender", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="job", type="string"),
     *                 @OA\Property(property="position", type="string"),
     *                 @OA\Property(property="patient_list_id", type="integer"),
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional patient file (PDF, Image, Doc, etc.)"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Optional file description"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Patient created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient created successfully."),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = Validator::make($request->all(),[
            'name'              => ['required', 'string'],
            'matibabu_card'     => ['nullable', 'string'],
            'zan_id'            => ['nullable', 'string'],
            'date_of_birth'     => ['required', 'string'],
            'gender'            => ['required', 'string'],
            'phone'             => ['nullable', 'string'],
            'location_id'       => ['nullable', 'numeric', 'exists:geographical_locations,location_id'],
            'job'               => ['nullable', 'string'],
            'position'          => ['nullable', 'string'],
            'patient_list_id'   => ['required', 'numeric', 'exists:patient_lists,patient_list_id'],
            'patient_file.*'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx'], // add file validation
            'description'       => ['nullable', 'string'],
        ]);

        // Check if validation fails
        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $data->errors(),
                'statusCode' => 422,
            ], 422);
        }

        $location_id = $request->location_id;

        // Create Patient
        $patient = Patient::create([
            'name'              => $request['name'],
            'matibabu_card'     => $request['matibabu_card'],
            'zan_id'            => $request['zan_id'],
            'date_of_birth'     => $request['date_of_birth'],
            'gender'            => $request['gender'],
            'phone'             => $request['phone'],
            'location_id'       => $request['location_id'],
            'job'               => $request['job'],
            'position'          => $request['position'],
            'patient_list_id'   => $request['patient_list_id'],
            'created_by'        => Auth::id(),
        ]);

        // Handle File Upload if exists
       if ($request->hasFile('patient_file')) {

            $files = $request->file('patient_file');

            // Make sure $files is always an array
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {

                // Extract the file extension
                $extension = $file->getClientOriginalExtension();

                // Generate a unique file name
                $newFileName = 'patient_file_' .  date('h-i-s_a_d-m-Y') . '.' . $extension;

                // Move the file to public/uploads/patientFiles/
                $file->move(public_path('uploads/patientFiles/'), $newFileName);

                // Save the relative path
                $filePath = 'uploads/patientFiles/' . $newFileName;

                // Save record in the database
                PatientFile::create([
                    'patient_id'  => $patient->patient_id,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $filePath,
                    'file_type'   => $file->getClientMimeType(),
                    'description' => $request->input('description') ?? null,
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        if ($patient) {
            return response([
                'data' => $patient->load('files'), // load files relationship if needed
                'message' => 'Patient created successfully with file.',
                'statusCode' => 201,
            ], 201);
        } else {
            return response([
                'message' => 'Internal server error',
                'statusCode' => 500,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/patients/{patientId}",
     *     summary="Find patient by ID",
     *     tags={"patients"},
     *     @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="patient_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date-time"),
     *                 @OA\Property(property="gender", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="job", type="string"),
     *                 @OA\Property(property="position", type="string"),
     *                 @OA\Property(property="patient_list_id", type="integer"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-10T10:44:31.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-10T10:44:31.000000Z"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->can('View Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $patient = Patient::with([
                'patientList',          // patient list info
                'files',                // all patient files
                'geographicalLocation',
                'referrals.reason',     // referrals + reason
                'referrals.hospital',   // referrals + hospital
                'referrals.creator',    // referral created by user
            ])->where('patient_id', (int)$id)
            ->get();


        if (!$patient) {
            return response([
                'message' => 'Patient not found',
                'statusCode' => 404,
            ],404);
        } else {
            return response([
                'data' => $patient,
                'statusCode' => 200,
            ],200);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/patients/update/{patientId}",
     *     summary="Update patient",
     *     tags={"patients"},
     *     @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="matibabu_card", type="string"),
     *                 @OA\Property(property="zan_id", type="string"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date"),
     *                 @OA\Property(property="gender", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="job", type="string"),
     *                 @OA\Property(property="position", type="string"),
     *                 @OA\Property(property="patient_list_id", type="integer"),
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional new file to attach"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Optional file description"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient updated successfully."),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Patient not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function updatePatient(Request $request, int $id)
    {
        $user = auth()->user();

        // Authorization check
        if (!$user->can('Update Patient')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name'             => ['required', 'string'],
            'matibabu_card'    => ['nullable', 'string'],
            'zan_id'           => ['nullable', 'string'],
            'date_of_birth'    => ['nullable', 'date'],
            'gender'           => ['nullable', 'string'],
            'phone'            => ['nullable', 'string'],
            'location_id'      => ['nullable', 'numeric', 'exists:geographical_locations,location_id'],
            'job'              => ['nullable', 'string'],
            'position'         => ['nullable', 'string'],
            'patient_list_id'  => ['nullable', 'numeric', 'exists:patient_lists,patient_list_id'],
            'patient_file.*'   => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx'],
            'description'      => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        // Find patient
        $patient = Patient::findOrFail($id);

        // Update patient data
        $patient->update([
            'name'            => $request->input('name'),
            'matibabu_card'   => $request->input('matibabu_card'),
            'zan_id'          => $request->input('zan_id'),
            'date_of_birth'   => $request->input('date_of_birth'),
            'gender'          => $request->input('gender'),
            'phone'           => $request->input('phone'),
            'location_id'     => $request->input('location_id'),
            'job'             => $request->input('job'),
            'position'        => $request->input('position'),
            'patient_list_id' => $request->input('patient_list_id'),
        ]);

        // Handle single or multiple file uploads
        if ($request->hasFile('patient_file')) {
            $files = $request->file('patient_file');

            // Ensure $files is always an array
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $newFileName = 'patient_file_' . date('h-i-s_a_d-m-Y') . '.' . $extension;

                // Move file to public/uploads/patientFiles/
                $file->move(public_path('uploads/patientFiles/'), $newFileName);
                $filePath = 'uploads/patientFiles/' . $newFileName;

                // Save file record in database
                PatientFile::create([
                    'patient_id'  => $patient->patient_id,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $filePath,
                    'file_type'   => $file->getClientMimeType(),
                    'description' => $request->input('description') ?? null,
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $patient->load('files'),
            'message' => 'Patient updated successfully.',
            'statusCode' => 200,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/patients/{patientId}",
     *     summary="Delete patient",
     *     tags={"patients"},
     *     @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $user = auth()->user();
        if (!$user->can('Delete Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $patient = Patient::withTrashed()->find($id);

        if (!$patient) {
            return response([
                'message' => 'Patient not found',
                'statusCode' => 404,
            ],404);
        }

        $patient->delete();

        return response([
            'message' => 'Patient blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/patients/unblock/{patientId}",
     *     summary="Unblock patient",
     *     tags={"patients"},
     *     @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function unBlockPatient(int $id)
    {

        $patient = Patient::withTrashed()->find($id);

        if (!$patient) {
            return response([
                'message' => 'Patient not found',
                'statusCode' => 404,
            ], 404);
        }

        $patient->restore($id);

        return response([
            'message' => 'Patient unbocked successfully',
            'statusCode' => 200,
        ], 200);
    }


    
    public function getAllPatientsWithInsurance(int $patient_id)
    {
        $patient = Patient::with('insurances')
            ->where('patient_id', $patient_id)
            ->first();

        if (!$patient) {
            return response()->json([
                'message' => 'Patient not found',
                'statusCode' => 404,
            ], 404);
        }

        if ($patient->patient_list_id) {
            $patient->documentUrl = asset('storage/' . $patient->patient_list_id);
        } else {
            $patient->documentUrl = null;
        }
        $patient->insurances = $patient->insurances ?? [];
        
        return response([
            'data' => $patient,
            'statusCode' => 200,
        ], 200);
    }

    public function getAllPatients()
    {
        $user = auth()->user();

        if (!$user->can('View Patient')) 
        {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $patients = Patient::with([
            'patientList',
            'files',
            'geographicalLocation',
            'referrals.reason',
            'referrals.hospital',
            'referrals.creator',
        ])
        ->whereDoesntHave('referrals') // patients with no referrals
        ->orWhereHas('referrals', function ($query) {
            $query->whereIn('status', ['Cancelled', 'Expired', 'Closed']);
        })
        ->get();

        return response([
            'data' => $patients,
            'statusCode' => 200,
        ], 200);
    }
}
