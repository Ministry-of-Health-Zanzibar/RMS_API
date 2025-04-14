<?php

namespace App\Http\Controllers\API\Insuarances;

use App\Models\Insurance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class InsuaranceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Insuarance|Create Insuarance|View Insuarance|Update Insuarance|Delete Insuarance', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    /** 
     * @OA\Get(
     *     path="/api/insuarances",
     *     summary="Get all insuarances",
     *     tags={"insuarances"},
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
     *                     @OA\Property(property="insurance_id", type="integer"),
     *                     @OA\Property(property="insurance_code", type="string"),
     *                     @OA\Property(property="patient_id", type="integer"),
     *                     @OA\Property(property="insurance_provider_name", type="string"),
     *                     @OA\Property(property="policy_number", type="string"),
     *                     @OA\Property(property="valid_until", type="string", format="date-time"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('View Insuarance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $insuarances = Insurance::withTrashed()->get();

        if ($insuarances) {
            return response([
                'data' => $insuarances,
                'statusCode' => 200,
            ], 200);
        } else {
            return response([
                'message' => 'No data found',
                'statusCode' => 200,
            ], 200);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/insuarances",
     *     summary="Create insuarance",
     *     tags={"insuarances"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="patient_id", type="integer"),
     *                     @OA\Property(property="insurance_provider_name", type="string"),
     *                     @OA\Property(property="policy_number", type="string"),
     *                     @OA\Property(property="valid_until", type="string", format="date-time"),
     *         ),
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
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Create Insuarance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'numeric'],
            'insurance_provider_name' => ['nullable', 'string'],
            'policy_number' => ['nullable', 'string'],
            'valid_until' => ['nullable', 'date'],
        ]);


        // Create Insurance
        $insuarance = Insurance::create([
            'patient_id' => $data['patient_id'],
            'insurance_provider_name' => $data['insurance_provider_name'],
            'policy_number' => $data['policy_number'],
            'valid_until' => $data['valid_until'],
            'created_by' => Auth::id(),
        ]);

        if ($insuarance) {
            return response([
                'data' => $insuarance,
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
     *     path="/api/insuarances/{insuarance_id}",
     *     summary="Find insuarance by ID",
     *     tags={"insuarances"},
     *     @OA\Parameter(
     *         name="insuarance_id",
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
     *                 @OA\Property(property="insurance_id", type="integer"),
     *                     @OA\Property(property="insurance_code", type="string"),
     *                     @OA\Property(property="patient_id", type="integer"),
     *                     @OA\Property(property="insurance_provider_name", type="string"),
     *                     @OA\Property(property="policy_number", type="string"),
     *                     @OA\Property(property="valid_until", type="string", format="date-time"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('View Insuarance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $insuarance = Insurance::withTrashed()->find($id);

        if (!$insuarance) {
            return response([
                'message' => 'Insurance not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $insuarance,
                'statusCode' => 200,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/insuarances/{insuarance_id}",
     *     summary="Update insuarance",
     *     tags={"insuarances"},
     *      @OA\Parameter(
     *         name="insuarance_id",
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
     *                    @OA\Property(property="patient_id", type="integer"),
     *                     @OA\Property(property="insurance_provider_name", type="string"),
     *                     @OA\Property(property="policy_number", type="string"),
     *                     @OA\Property(property="valid_until", type="string", format="date-time"),
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Create Insuarance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'numeric'],
            'insurance_provider_name' => ['nullable', 'string'],
            'policy_number' => ['nullable', 'string'],
            'valid_until' => ['nullable', 'date'],
        ]);


        // Update Insurance
        $insuarance = Insurance::findOrFail($id);
        $insuarance->update([
            'patient_id' => $data['patient_id'],
            'insurance_provider_name' => $data['insurance_provider_name'],
            'policy_number' => $data['policy_number'],
            'valid_until' => $data['valid_until'],
            'created_by' => Auth::id(),
        ]);

        if ($insuarance) {
            return response([
                'data' => $insuarance,
                'message' => 'Insurance updated successfully',
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
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/insuarances/{insuarance_id}",
     *     summary="Delete insuarance",
     *     tags={"insuarances"},
     *     @OA\Parameter(
     *         name="insuarance_id",
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Delete Insuarance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $insuarance = Insurance::withTrashed()->find($id);

        if (!$insuarance) {
            return response([
                'message' => 'Insuarance not found',
                'statusCode' => 404,
            ]);
        }

        $insuarance->delete();

        return response([
            'message' => 'Insuarance blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/insuarances/unBlock/{insuarance_id}",
     *     summary="Unblock insuarance",
     *     tags={"insuarances"},
     *     @OA\Parameter(
     *         name="insuarance_id",
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
    public function unBlockInsuarance(int $id)
    {

        $insuarance = Insurance::withTrashed()->find($id);

        if (!$insuarance) {
            return response([
                'message' => 'Insuarance not found',
                'statusCode' => 404,
            ], 404);
        }

        $insuarance->restore($id);

        return response([
            'message' => 'Insuarance unbocked successfully',
            'statusCode' => 200,
        ], 200);
    }

}