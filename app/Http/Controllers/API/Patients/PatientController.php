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
     *     tags={"Patients"},
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
    // public function index()
    // {
    //     $user = auth()->user();

    //     if (!$user->can('View Patient')) {
    //         return response([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     if ($user->hasAnyRole(['ROLE ADMIN'])) {

    //         $patients = Patient::with([
    //             'patientList',
    //             'files',
    //             'insurances',
    //             'geographicalLocation',
    //             'referrals.reason',
    //             'referrals.hospital',
    //             'referrals.creator',
    //         ])
    //         ->withTrashed()
    //         ->get();

    //     } elseif ($user->hasAnyRole(['ROLE HOSPITAL USER'])) {

    //         $patients = Patient::with([
    //             'patientList',
    //             'files',
    //             'geographicalLocation',
    //             'referrals.reason',
    //             'referrals.hospital',
    //             'referrals.creator',
    //         ])
    //         ->where('created_by', $user->id)
    //         ->get();

    //     } else {

    //         $patients = Patient::with([
    //             'patientList',
    //             'files',
    //             'geographicalLocation',
    //             'referrals.reason',
    //             'referrals.hospital',
    //             'referrals.creator',
    //         ])
    //         ->get();
    //     }

    //     return response([
    //         'data' => $patients,
    //         'statusCode' => 200,
    //     ], 200);
    // }

    public function index()
    {
        $user = auth()->user();

        if (!$user->can('View Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Common relations
        $relations = [
            'patientList',
            'files',
            'insurances',
            'geographicalLocation',
            'referrals.reason',
            'referrals.hospital',
            'referrals.creator',

            // ✅ ADD CREATOR + HOSPITALS (same as show)
            'creator' => function ($query) {
                $query->with(['hospitals' => function ($q) {
                    $q->select(
                        'hospitals.hospital_id',
                        'hospitals.hospital_name'
                    );
                }]);
            },
        ];

        if ($user->hasAnyRole(['ROLE ADMIN'])) {

            $patients = Patient::with($relations)
                ->withTrashed()
                ->get();

        } elseif ($user->hasAnyRole(['ROLE HOSPITAL USER'])) {

            $patients = Patient::with($relations)
                ->where('created_by', $user->id)
                ->get();

        } else {

            $patients = Patient::with($relations)->get();
        }

        return response([
            'data' => $patients,
            'statusCode' => 200,
        ], 200);
    }


    public function patientsHistories()
    {
        $user = auth()->user();

        if (!$user->canAny(['View Patient', 'View History'])) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $query = Patient::query()
            ->with(['latestHistory', 'creator'])
            ->join('users', 'users.id', '=', 'patients.created_by')
            ->leftJoin('hospital_user', 'hospital_user.user_id', '=', 'users.id')
            ->leftJoin('hospitals', 'hospitals.hospital_id', '=', 'hospital_user.hospital_id')
            ->whereHas('patientHistories')
            ->latest('patients.created_at');

        /**
         * ROLE: HOSPITAL USER
         */
        if ($user->hasRole('ROLE HOSPITAL USER')) {
            $query->where('patients.created_by', $user->id);
        }

        /**
         * ROLE: MEDICAL BOARD MEMBER
         */
        if ($user->hasRole('ROLE MEDICAL BOARD MEMBER')) {
            $query->whereHas('patientHistories', function ($q) {
                $q->where('status', 'reviewed');
            });

            $query->with(['latestHistory' => function ($q) {
                $q->where('status', 'reviewed');
            }]);
        }

        /**
         * ROLE: ADMIN + MKURUGENZI TIBA
         */
        if ($user->hasAnyRole(['ROLE ADMIN', 'ROLE MKURUGENZI TIBA'])) {
            $query->withTrashed();
        }

        // Select full patient columns + hospital name + role
        $patients = $query
            ->select(
                'patients.*',
                'hospitals.hospital_name as hospital',
                'hospital_user.role as hospital_role'
            )
            ->groupBy(
                'patients.patient_id',
                'hospitals.hospital_name',
                'hospital_user.role'
            )
            ->get();

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
     *     tags={"Patients"},
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
     *                 ),
     *                 @OA\Property(property="insurance_provider_name", type="string", description="Insurance provider name"),
     *                 @OA\Property(property="card_number", type="string", description="Insurance card number"),
     *                 @OA\Property(property="valid_until", type="string", format="date", description="Insurance valid until date"),
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

        // Normalize boolean from Angular ("true"/"false" → true/false)
        $request->merge([
            'has_insurance' => filter_var($request->has_insurance, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);

        $data = Validator::make($request->all(), [
            'name'              => ['required', 'string'],
            'matibabu_card'     => ['nullable', 'string'],
            'zan_id'            => ['nullable', 'string'],
            'date_of_birth'     => ['required', 'string'],
            'gender'            => ['required', 'string'],
            'phone'             => ['nullable', 'string'],
            'location_id'       => ['nullable', 'string', 'exists:geographical_locations,location_id'],
            'job'               => ['nullable', 'string'],
            'position'          => ['nullable', 'string'],

            // Make patient_list_id optional
            'patient_list_id'   => ['nullable', 'numeric', 'exists:patient_lists,patient_list_id'],

            'patient_file.*'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx'],
            'description'       => ['nullable', 'string'],

            // Optional insurance validation
            'has_insurance'           => ['required', 'boolean'],
            'insurance_provider_name' => ['nullable', 'string'],
            'card_number'             => ['nullable', 'string'],
            'valid_until'             => ['nullable', 'string'],
        ]);

        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $data->errors(),
                'statusCode' => 422,
            ], 422);
        }

        $patientList = null;

        // If a patient_list_id is given, validate and check capacity
        if ($request->filled('patient_list_id')) {
            $patientList = \App\Models\PatientList::find($request->patient_list_id);

            if (!$patientList) {
                return response()->json([
                    'message' => "Invalid Patient List ID: {$request->patient_list_id}",
                    'statusCode' => 404
                ], 404);
            }

            // Check patient count limit using pivot
            $existingCount = \App\Models\Patient::whereHas('patientList', function ($query) use ($patientList) {
                $query->where('patient_lists.patient_list_id', $patientList->patient_list_id);
            })->count();

            if ($existingCount >= $patientList->no_of_patients) {
                return response()->json([
                    'message' => "The Medical Board (ID: {$patientList->patient_list_id}) already reached its patient limit ({$patientList->no_of_patients}).",
                    'statusCode' => 422,
                ], 422);
            }
        }

        // Create the patient (patient_list_id optional)
        $patient = \App\Models\Patient::create([
            'name'            => $request['name'],
            'matibabu_card'   => $request['matibabu_card'],
            'zan_id'          => $request['zan_id'],
            'date_of_birth'   => $request['date_of_birth'],
            'gender'          => $request['gender'],
            'phone'           => $request['phone'],
            'location_id'     => $request['location_id'],
            'job'             => $request['job'],
            'position'        => $request['position'],
            // use optional() helper instead of nullsafe operator
            'patient_list_id' => optional($patientList)->patient_list_id,
            'created_by'      => Auth::id(),
        ]);

        // Attach to list via pivot only if list provided
        if ($patientList) {
            $patient->patientList()->attach($patientList->patient_list_id);
        }

        // Optional Insurance creation
        if ($request->filled('has_insurance') && $request->boolean('has_insurance') === true) {
            $insuranceProvider = $request->insurance_provider_name ?: null;
            $cardNumber        = $request->card_number ?: null;
            $validUntil        = $request->valid_until ?: null;

            // Only create if not already existing for that patient
            $existingInsurance = \App\Models\Insurance::where('patient_id', $patient->patient_id)->first();

            if (!$existingInsurance) {
                \App\Models\Insurance::create([
                    'patient_id'             => $patient->patient_id,
                    'insurance_provider_name'=> $insuranceProvider,
                    'card_number'            => $cardNumber,
                    'valid_until'            => $validUntil,
                ]);
            }
        }

        // File Upload
        if ($request->hasFile('patient_file')) {
            $files = $request->file('patient_file');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $newFileName = 'patient_file_' . date('h-i-s_a_d-m-Y') . '.' . $extension;
                $file->move(public_path('uploads/patientFiles/'), $newFileName);
                $filePath = 'uploads/patientFiles/' . $newFileName;

                \App\Models\PatientFile::create([
                    'patient_id'  => $patient->patient_id,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $filePath,
                    'file_type'   => $file->getClientMimeType(),
                    'description' => $request->input('description') ?? null,
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        // Response
        return response([
            'data' => $patient->load(['files', 'insurances']),
            'message' => $patientList
                ? 'Patient created successfully and linked to patient list.'
                : 'Patient created successfully (not linked to any patient list).',
            'statusCode' => 201,
        ], 201);
    }


    public function storePatientAndHistory(Request $request)
    {
        $user = auth()->user();

        if (!$user->can('Create Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $request->merge([
            'has_insurance' => filter_var($request->has_insurance, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);

        $data = Validator::make($request->all(), [
            'name'              => ['required', 'string'],
            'matibabu_card'     => ['required', 'string'], // Made required to ensure lookup works
            'zan_id'            => ['nullable', 'string'],
            'date_of_birth'     => ['required', 'string'],
            'gender'            => ['required', 'string'],
            'phone'             => ['nullable', 'string'],
            'location_id'       => ['nullable', 'string', 'exists:geographical_locations,location_id'],
            'job'               => ['nullable', 'string'],
            'position'          => ['nullable', 'string'],
            // 'patient_file.*'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx'],

            'referring_doctor'              => ['nullable', 'string'],
            'file_number'                   => ['nullable', 'string'],
            'referring_date'                => ['nullable', 'string'],
            'reason_id'                     => ['required', 'numeric', 'exists:reasons,reason_id'],
            'case_type'                     => ['nullable', 'in:Emergency,Routine'],
            'history_of_presenting_illness' => ['nullable', 'string'],
            'physical_findings'             => ['nullable', 'string'],
            'investigations'                => ['nullable', 'string'],
            'management_done'               => ['nullable', 'string'],
            'board_comments'                => ['nullable', 'string'],
            'history_file'                  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],

            'has_insurance'           => ['required', 'boolean'],
            'insurance_provider_name' => ['nullable', 'string'],
            'card_number'             => ['nullable', 'string'],
            'valid_until'             => ['nullable', 'string'],
        ]);

        if ($data->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $data->errors(),
                'statusCode' => 422,
            ], 422);
        }

        $request->merge(['case_type' => $request->case_type ?? 'Routine']);

        DB::beginTransaction();

        try {
            // ============================================================
            // UPSERT PATIENT (Find by card, Update if exists, or Create)
            // ============================================================
            $patient = \App\Models\Patient::updateOrCreate(
                ['matibabu_card' => $request->matibabu_card], // Search criteria
                [
                    'name'          => $request->name,
                    'zan_id'        => $request->zan_id,
                    'date_of_birth' => $request->date_of_birth,
                    'gender'        => $request->gender,
                    'phone'         => $request->phone,
                    'location_id'   => $request->location_id,
                    'job'           => $request->job,
                    'position'      => $request->position,
                    'created_by'    => Auth::id(), // Only sets on creation if you use firstOrCreate, but updateOrCreate resets it. If you want to keep original creator, use logic below.
                ]
            );

            // =======================
            // ALWAYS CREATE NEW HISTORY
            // =======================
            $patientHistory = \App\Models\PatientHistory::create([
                'patient_id'                    => $patient->patient_id,
                'referring_doctor'              => $request->referring_doctor,
                'file_number'                   => $request->file_number,
                'referring_date'                => $request->referring_date,
                'reason_id'                     => $request->reason_id,
                'case_type'                     => $request->case_type,
                'history_of_presenting_illness' => $request->history_of_presenting_illness,
                'physical_findings'             => $request->physical_findings,
                'investigations'                => $request->investigations,
                'management_done'               => $request->management_done,
                'board_comments'                => $request->board_comments,
                'status'                        => 'pending',
            ]);

            // =======================
            // OPTIONAL INSURANCE
            // =======================
            if ($request->boolean('has_insurance')) {
                \App\Models\Insurance::updateOrCreate(
                    ['patient_id' => $patient->patient_id],
                    [
                        'insurance_provider_name' => $request->insurance_provider_name,
                        'card_number'             => $request->card_number,
                        'valid_until'             => $request->valid_until,
                    ]
                );
            }

            // =======================
            // FILE UPLOAD (HISTORY) - SINGLE FILE
            // =======================
            if ($request->hasFile('history_file')) {
                $file = $request->file('history_file');

                // Generate a unique name
                $fileName = 'history_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Move the file to the destination
                $file->move(public_path('uploads/historyFiles'), $fileName);

                // Save the single path string to the database
                $patientHistory->update([
                    'history_file' => 'uploads/historyFiles/' . $fileName
                ]);
            }

            DB::commit();

            return response([
                'data' => [
                    'patient' => $patient,
                    'history' => $patientHistory
                ],
                'message' => 'Record processed successfully (Patient updated/synced and new history created)',
                'statusCode' => 201,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response([
                'message' => 'Failed to process request',
                'error' => $e->getMessage(),
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
     *     tags={"Patients"},
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
                'insurances',
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
     *     tags={"Patients"},
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

        // Authorization
        if (!$user->can('Update Patient')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Normalize input
        $request->merge([
            'has_insurance' => filter_var($request->has_insurance, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);

        // Validation
        $validator = Validator::make($request->all(), [
            'name'              => ['required', 'string'],
            'matibabu_card'     => ['nullable', 'string'],
            'zan_id'            => ['nullable', 'string'],
            'date_of_birth'     => ['nullable', 'date'],
            'gender'            => ['nullable', 'string'],
            'phone'             => ['nullable', 'string'],
            'location_id'       => ['nullable', 'string', 'exists:geographical_locations,location_id'],
            'job'               => ['nullable', 'string'],
            'position'          => ['nullable', 'string'],
            'patient_list_id'   => ['numeric', 'exists:patient_lists,patient_list_id'],
            'patient_file.*'    => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xlsx'],
            'description'       => ['nullable', 'string'],
            'has_insurance'           => ['nullable', 'boolean'],
            'insurance_provider_name' => ['nullable', 'string'],
            'card_number'             => ['nullable', 'string'],
            'valid_until'             => ['nullable', 'date'],
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

        // Check capacity for each list
        $patientList = \App\Models\PatientList::find($request->patient_list_id);

        if (!$patientList) {
            return response()->json([
                'message' => "Invalid Patient List ID: {$request->patient_list_id}",
                'statusCode' => 404
            ], 404);
        }

        $existingCount = \App\Models\Patient::whereHas('patientList', function ($query) use ($patientList) {
            $query->where('patient_lists.patient_list_id', $patientList->patient_list_id);
        })->count();

        if ($existingCount >= $patientList->no_of_patients) {
            return response()->json([
                'message' => "The Medical Board (ID: {$patientList->patient_list_id}) already reached its patient limit ({$patientList->no_of_patients}).",
                'statusCode' => 422,
            ], 422);
        }

        // Update patient basic info
        $patient->update($request->only([
            'name', 'matibabu_card', 'zan_id', 'date_of_birth',
            'gender', 'phone', 'location_id', 'job', 'position'
        ]));

        // Sync pivot table with new patient lists
        $patient->patientList()->sync($request->patient_list_id);

        // Handle file uploads
        if ($request->hasFile('patient_file')) {
            $files = $request->file('patient_file');
            if (!is_array($files)) $files = [$files];

            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $newFileName = 'patient_file_' . date('h-i-s_a_d-m-Y') . '.' . $extension;
                $file->move(public_path('uploads/patientFiles/'), $newFileName);

                PatientFile::create([
                    'patient_id'  => $patient->patient_id,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => 'uploads/patientFiles/' . $newFileName,
                    'file_type'   => $file->getClientMimeType(),
                    'description' => $request->input('description'),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        // Handle insurance
        if ($request->has('has_insurance')) {
            if ($request->boolean('has_insurance')) {
                \App\Models\Insurance::updateOrCreate(
                    ['patient_id' => $patient->patient_id],
                    [
                        'insurance_provider_name' => $request->insurance_provider_name ?: null,
                        'card_number'             => $request->card_number ?: null,
                        'valid_until'             => $request->valid_until ?: null,
                    ]
                );
            } else {
                \App\Models\Insurance::where('patient_id', $patient->patient_id)->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $patient->load(['files', 'insurances', 'patientLists']),
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
     *     tags={"Patients"},
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
     *     tags={"Patients"},
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
            $query->whereIn('status', ['Cancelled', 'Expired', 'Closed', 'Pending']);
        })
        ->get();

        return response([
            'data' => $patients,
            'statusCode' => 200,
        ], 200);
    }


    /**
     * @OA\Get(
     *     path="/api/patients/histories/{patientId}",
     *     summary="Get medical histories of a patient by ID",
     *     tags={"Patients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient medical histories retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="patient",
     *                     type="object",
     *                     @OA\Property(property="patient_id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="Jane Doe"),
     *                     @OA\Property(property="gender", type="string", example="Female"),
     *                     @OA\Property(property="date_of_birth", type="string", format="date", example="1995-09-10")
     *                 ),
     *                 @OA\Property(
     *                     property="medical_histories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="patient_histories_id", type="integer", example=1),
     *                         @OA\Property(property="referring_doctor", type="string", example="Dr. Ali"),
     *                         @OA\Property(property="file_number", type="string", example="FILE123"),
     *                         @OA\Property(property="referring_date", type="string", format="date", example="2025-03-15"),
     *                         @OA\Property(property="history_of_presenting_illness", type="string", example="Severe headache and nausea."),
     *                         @OA\Property(property="physical_findings", type="string", example="BP: 120/80, HR: 75 bpm"),
     *                         @OA\Property(property="investigations", type="string", example="CT scan, Blood tests"),
     *                         @OA\Property(property="management_done", type="string", example="Pain management and follow-up"),
     *                         @OA\Property(property="board_comments", type="string", example="Monitor condition"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-15T08:20:00Z")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function getMedicalHistory($patient_id)
    {
        $user = auth()->user();

        if (!$user->can('View Patient')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $patient = Patient::with([
            'patientHistories' => function ($q) {
                $q->latest();
            },
            'patientHistories.reason',
        ])->where('patient_id', $patient_id)->first();


        if (!$patient) {
            return response()->json([
                'message' => 'Patient not found',
                'statusCode' => 404,
            ], 404);
        }

        return response()->json([
            'data' => [
                'patient' => $patient,
                'medical_histories' => $patient->patientHistories,
            ],
            'statusCode' => 200,
        ]);
    }

    /**
     * Get a list of Matibabu Cards for autocomplete suggestions.
     * Only returns cards for patients with 'Closed/Cancelled' referrals
     * and a 'rejected' latest history.
     */
    public function autocompleteMatibabuCards(Request $request)
    {
        $user = auth()->user();

        if (!$user->can('View Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $term = $request->query('term');

        if (empty($term)) {
            return response()->json(['data' => [], 'statusCode' => 200], 200);
        }

        $cards = Patient::where('matibabu_card', 'LIKE', "%{$term}%")
            ->whereHas('referrals', function ($query) {
                $query->whereIn('status', ['Closed', 'Cancelled']);
            })
            ->whereHas('latestHistory', function ($query) {
                $query->where('status', 'rejected');
            })
            ->distinct()
            ->limit(15)
            ->pluck('matibabu_card');

        return response()->json([
            'data' => $cards,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * Search for an eligible patient by Matibabu Card.
     * Criteria:
     * 1. Status in referral table is 'Closed' or 'Cancelled'
     * 2. Latest patient history status is 'rejected'
     */
    public function searchByMatibabu(Request $request)
    {
        $user = auth()->user();

        if (!$user->can('View Patient')) {
            return response(['message' => 'Forbidden', 'statusCode' => 403], 403);
        }

        $validator = Validator::make($request->all(), [
            'matibabu_card' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'statusCode' => 422], 422);
        }

        $card = $request->matibabu_card;

        // 1. Check if the patient exists first
        $patientExists = Patient::where('matibabu_card', $card)->first();

        // IF CARD DOES NOT EXIST: Return nothing (or 204) to allow form filling
        if (!$patientExists) {
            return response()->json([
                'message' => 'New patient Matibabu Card detected',
                'data' => null,
                'statusCode' => 204 // 204 means "No Content", perfect for "continue filling form"
            ]);
        }

        // 2. If patient exists, check medical eligibility
        $eligiblePatient = Patient::where('matibabu_card', $card)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Path A: Check referral status if they exist
                    $q->whereHas('referrals', function ($subQuery) {
                        $subQuery->whereIn('status', ['Closed', 'Cancelled']);
                    });
                })
                ->orWhere(function ($q) {
                    // Path B: ONLY if no referrals exist
                    $q->whereDoesntHave('referrals')
                        ->whereHas('latestHistory', function ($subQuery) {
                        // Ensure the status is 'rejected' ON the latest record
                        $subQuery->where('status', 'rejected')
                                ->whereRaw('patient_histories_id = (select max(patient_histories_id) from patient_histories where patient_id = patients.patient_id)');
                    });
                });
            })
            ->with(['latestHistory', 'referrals', 'geographicalLocation', 'creator'])
            ->first();

        // IF EXISTS BUT NOT ELIGIBLE
        if (!$eligiblePatient) {
            return response()->json([
                'message' => 'This patient is not eligible (Referral not Closed/Cancelled or History not Rejected).',
                'statusCode' => 403, // 403 Forbidden is often used for "Not Eligible" logic
            ], 200);
        }

        // IF EXISTS AND ELIGIBLE
        return response()->json([
            'data' => $eligiblePatient,
            'message' => 'Eligible patient retrieved successfully.',
            'statusCode' => 200,
        ], 200);
    }

}
