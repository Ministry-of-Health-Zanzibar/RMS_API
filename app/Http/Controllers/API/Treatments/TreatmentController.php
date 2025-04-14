<?php

namespace App\Http\Controllers\API\Treatments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Treatment;
use Illuminate\Support\Facades\Auth;


class TreatmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Treatment|Create Treatment|View Treatment|Update Treatment|Delete Treatment', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    /** 
     * @OA\Get(
     *     path="/api/treatments",
     *     summary="Get all treatments",
     *     tags={"treatments"},
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
     *                     @OA\Property(property="referral_id", type="integer"),
        *                 @OA\Property(property="received_date", type="string", format="date"),
        *                 @OA\Property(property="started_date", type="string", format="date"),
        *                 @OA\Property(property="ended_date", type="string", format="date"),
        *                 @OA\Property(property="treatment_status", type="string"),
        *                 @OA\Property(property="measurements", type="string"),
        *                 @OA\Property(property="disease", type="string"),
        *                 @OA\Property(property="treatment_file", type="string", description="Uploaded file name")
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('View Treatment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $Treatments = Treatment::withTrashed()->get();

        if ($Treatments) {
            return response([
                'data' => $Treatments,
                'statusCode' => 200,
            ], 200);
        } else {
            return response([
                'message' => 'No data found',
                'statusCode' => 500,
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/treatments",
     *     summary="Create treatment",
     *     tags={"treatments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="referral_id", type="integer"),
 *                 @OA\Property(property="received_date", type="string", format="date"),
 *                 @OA\Property(property="started_date", type="string", format="date"),
 *                 @OA\Property(property="ended_date", type="string", format="date"),
 *                 @OA\Property(property="treatment_status", type="string"),
 *                 @OA\Property(property="measurements", type="string"),
 *                 @OA\Property(property="disease", type="string"),
 *                 @OA\Property(property="treatment_file", type="string", description="Uploaded file name"),
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
            if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Create Treatment')) {
                return response([
                    'message' => 'Forbidden',
                    'statusCode' => 403
                ], 403);
            }

            $data = $request->validate([
                'referral_id' => ['required', 'numeric'],
                'received_date' => ['nullable', 'date'],
                'started_date' => ['nullable', 'date'],
                'ended_date' => ['nullable', 'date'],
                'treatment_status' => ['nullable', 'string'],
                'measurements' => ['nullable', 'string'],
                'disease'=>['nullable','string'],
                'treatment_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // max 5MB
            ]);

        // Only handle the file after validation passes
        $path = null;
        if (isset($data['treatment_file'])) {
            $path = $data['treatment_file']->store('documents', 'public');
        }
        // Create Treatment
        $treatment = Treatment::create([
            'referral_id' => $data['referral_id'],
            'received_date' => $data['received_date'],
            'started_date' => $data['started_date'],
            'ended_date' => $data['ended_date'],
            'treatment_status' => $data['treatment_status'],
            'measurements' => $data['measurements'],
            'disease' => $data['disease'],
            'treatment_file' => $path,
            'created_by' => Auth::id(),
        ]);

        if ($treatment) {
            return response([
                'data' => $treatment,
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
     *     path="/api/treatments/{treatmentId}",
     *     summary="Find treatment by ID",
     *     tags={"treatments"},
     *     @OA\Parameter(
     *         name="treatmentId",
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
     *                @OA\Property(property="referral_id", type="integer"),
 *                    @OA\Property(property="received_date", type="string", format="date"),
 *                    @OA\Property(property="started_date", type="string", format="date"),
 *                    @OA\Property(property="ended_date", type="string", format="date"),
 *                    @OA\Property(property="treatment_status", type="string"),
 *                    @OA\Property(property="measurements", type="string"),
 *                    @OA\Property(property="disease", type="string"),
 *                    @OA\Property(property="treatment_file", type="string", description="Uploaded file name"),
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL, ROLE STAFF']) || !$user->can('View Treatment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $treatment = Treatment::withTrashed()->find($id);

        if (!$treatment) {
            return response([
                'message' => 'treatment not found',
                'statusCode' => 404,
            ]);
        } else {
            // Append full image URL 
            if ($treatment->treatment_file) {
                $treatment->documentUrl = asset('storage/' . $treatment->treatment_file);
            } else {
                $treatment->documentUrl = null;
            }

            return response([
                'data' => $treatment,
                'statusCode' => 200,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/treatments/update/{treatmentId}",
     *     summary="Update treatment",
     *     tags={"treatments"},
     *      @OA\Parameter(
     *         name="treatmentId",
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
     *             @OA\Property(property="referral_id", type="integer"),
 *                 @OA\Property(property="received_date", type="string", format="date"),
 *                 @OA\Property(property="started_date", type="string", format="date"),
 *                 @OA\Property(property="ended_date", type="string", format="date"),
 *                 @OA\Property(property="treatment_status", type="string"),
 *                 @OA\Property(property="measurements", type="string"),
 *                 @OA\Property(property="disease", type="string"),
 *                 @OA\Property(property="treatment_file", type="string", description="Uploaded file name"),
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
{
    
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Update Treatment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }
    
        $data = $request->validate([
            'referral_id' => ['required', 'numeric'],
            'received_date' => ['nullable', 'date'],
            'started_date' => ['nullable', 'date'],
            'ended_date' => ['nullable', 'date'],
            'treatment_status' => ['nullable', 'string'],
            'measurements' => ['nullable', 'string'],
            'disease' => ['nullable', 'string'],
            'treatment_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 2MB
        ]);
    
        $treatment = Treatment::findOrFail($id);

        // Handle file upload if provided
        if ($request->hasFile('treatment_file')) {
            $path = $request->file('treatment_file')->store('documents', 'public');
            $data['treatment_file'] = $path;
        } else {
            unset($data['treatment_file']);
        }

        $data['created_by'] = Auth::id();

        $treatment->update($data);

        return response([
            'data' => $treatment,
            'statusCode' => 200,
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/treatments/{treatmentId}",
     *     summary="Delete treatment",
     *     tags={"treatments"},
     *     @OA\Parameter(
     *         name="treatmentId",
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
    public function destroy(string $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Delete Treatment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $treatment = Treatment::withTrashed()->find($id);

        if (!$treatment) {
            return response([
                'message' => 'Treatment not found',
                'statusCode' => 404,
            ]);
        }

        $treatment->delete();

        return response([
            'message' => 'Treatment blocked successfully',
            'statusCode' => 200,
        ], 200);

    }
/**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/treatments/unBlock/{treatmentId}",
     *     summary="Unblock treatment",
     *     tags={"treatments"},
     *     @OA\Parameter(
     *         name="treatmentId",
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
    public function unBlockTreatment(int $id)
    {

        $treatment = Treatment::withTrashed()->find($id);

        if (!$treatment) {
            return response([
                'message' => 'Treatment not found',
                'statusCode' => 404,
            ], 404);
        }

        $treatment->restore($id);

        return response([
            'message' => 'Treatment unbocked successfully',
            'statusCode' => 200,
        ], 200);
    }

}
