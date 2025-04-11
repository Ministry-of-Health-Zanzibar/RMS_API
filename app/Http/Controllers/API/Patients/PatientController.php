<?php

namespace App\Http\Controllers\API\Patients;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Patient|Create Patient|View Patient|Update Patient|Delete Patient', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
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
     *                     @OA\Property(property="referral_letter_file", type="string"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('View Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $patients = Patient::withTrashed()->get();

        if ($patients->isEmpty()) {
            return response([
                'message' => 'No data found',
                'statusCode' => 500,
            ], 500);
        } else {

            // Append full doc URL 
            $patients = $patients->map(function ($patient) {
                $patient->documentUrl = $patient->referral_letter_file
                    ? asset('storage/' . $patient->referral_letter_file)
                    : null;
                return $patient;
            });

            return response([
                'data' => $patients,
                'statusCode' => 200,
            ], 200);
        }
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
     *         @OA\JsonContent(
     *             type="object",
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="date_of_birth", type="string", format="date-time"),
     *              @OA\Property(property="gender", type="string"),
     *              @OA\Property(property="phone", type="string"),
     *              @OA\Property(property="location", type="string"),
     *              @OA\Property(property="job", type="string"),
     *              @OA\Property(property="position", type="string"),
     *              @OA\Property(property="referral_letter_file", type="string"),
     *         )
     *     ),
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
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="statusCode", type="integer", example="201")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Create Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'job' => ['nullable', 'string'],
            'position' => ['nullable', 'string'],
            'referral_letter_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:5120'], // 5MB max
            // 'referral_letter_file' => ['nullable', 'string'],
        ]);


        // Handle file upload
        // $path = null;
        // if ($request->hasFile('referral_letter_file')) {
        //     $path = $request->file('referral_letter_file')->store('documents', 'public');
        // }

        // Only handle the file after validation passes
        $path = null;
        if (isset($data['referral_letter_file'])) {
            $path = $data['referral_letter_file']->store('documents', 'public');
        }


        // Create Patient
        $patient = Patient::create([
            'name' => $data['name'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'phone' => $data['phone'],
            'location' => $data['location'],
            'job' => $data['job'],
            'position' => $data['position'],
            'referral_letter_file' => $path,
            'created_by' => Auth::id(),
        ]);

        if ($patient) {
            return response([
                'data' => $patient,
                'statusCode' => 201,
            ], status: 201);
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
     *         @OA\Schema(type="integer")
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
     *                 @OA\Property(property="referral_letter_file", type="string"),
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
    public function show(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('View Patient')) {
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
            ]);
        } else {
            // Append full image URL 
            if ($patient->referral_letter_file) {
                $patient->documentUrl = asset('storage/' . $patient->referral_letter_file);
            } else {
                $patient->documentUrl = null;
            }

            return response([
                'data' => $patient,
                'statusCode' => 200,
            ]);
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
     *      @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *      ),
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
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date-time"),
     *                 @OA\Property(property="gender", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="location", type="string"),
     *                 @OA\Property(property="job", type="string"),
     *                 @OA\Property(property="position", type="string"),
     *                 @OA\Property(property="referral_letter_file", type="string"),
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function updatePatient(Request $request, int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Update Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'job' => ['nullable', 'string'],
            'position' => ['nullable', 'string'],
            'referral_letter_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:1024'], // 1MB
        ]);

        $patient = Patient::findOrFail($id);

        // Handle file upload if provided
        if ($request->hasFile('referral_letter_file')) {
            $path = $request->file('referral_letter_file')->store('documents', 'public');
            $data['referral_letter_file'] = $path;
        } else {
            unset($data['referral_letter_file']);
        }

        $data['created_by'] = Auth::id();

        $patient->update($data);

        return response([
            'data' => $patient,
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Delete Patient')) {
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
            ]);
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
}