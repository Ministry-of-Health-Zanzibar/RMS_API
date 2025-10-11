<?php

namespace App\Http\Controllers\API\Insurances;

use App\Models\Insurance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class InsuranceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Insurance|Create Insurance|View Insurance|Update Insurance|Delete Insurance', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/insurances",
     *     summary="Get all insurances",
     *     tags={"insurances"},
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
     *                     @OA\Property(property="card_number", type="string"),
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
        if (!$user->can('View Insurance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $insurances = Insurance::withTrashed()->get();

        if ($insurances) {
            return response([
                'data' => $insurances,
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
     *     path="/api/insurances",
     *     summary="Create insurance",
     *     tags={"insurances"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="patient_id", type="integer"),
     *                     @OA\Property(property="insurance_provider_name", type="string"),
     *                     @OA\Property(property="card_number", type="string"),
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
        if (!$user->can('Create Insurance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'numeric'],
            'insurance_provider_name' => ['nullable', 'string'],
            'card_number' => ['nullable', 'string'],
            'valid_until' => ['nullable', 'date'],
        ]);


        // Create Insurance
        $insurance = Insurance::create([
            'patient_id' => $data['patient_id'],
            'insurance_provider_name' => $data['insurance_provider_name'],
            'card_number' => $data['card_number'],
            'valid_until' => $data['valid_until'],
            'created_by' => Auth::id(),
        ]);

        if ($insurance) {
            return response([
                'data' => $insurance,
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
     *     path="/api/insurances/{insurance_id}",
     *     summary="Find insurance by ID",
     *     tags={"insurances"},
     *     @OA\Parameter(
     *         name="insurance_id",
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
     *                     @OA\Property(property="card_number", type="string"),
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
        if (!$user->can('View Insurance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $insurance = Insurance::withTrashed()->find($id);

        if (!$insurance) {
            return response([
                'message' => 'Insurance not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $insurance,
                'statusCode' => 200,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/insurances/{insurance_id}",
     *     summary="Update insurance",
     *     tags={"insurances"},
     *      @OA\Parameter(
     *         name="insurance_id",
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
     *                     @OA\Property(property="card_number", type="string"),
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
        if (!$user->can('Create Insurance')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'numeric'],
            'insurance_provider_name' => ['nullable', 'string'],
            'card_number' => ['nullable', 'string'],
            'valid_until' => ['nullable', 'date'],
        ]);


        // Update Insurance
        $insurance = Insurance::findOrFail($id);
        $insurance->update([
            'patient_id' => $data['patient_id'],
            'insurance_provider_name' => $data['insurance_provider_name'],
            'card_number' => $data['card_number'],
            'valid_until' => $data['valid_until'],
            'created_by' => Auth::id(),
        ]);

        if ($insurance) {
            return response([
                'data' => $insurance,
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
     *     path="/api/insurances/{insurance_id}",
     *     summary="Delete insurance",
     *     tags={"insurances"},
     *     @OA\Parameter(
     *         name="insurance_id",
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
        if (!$user->can('Delete Insurance')) {
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
        }

        $insuarance->delete();

        return response([
            'message' => 'Insurance blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/insurances/unBlock/{insuarance_id}",
     *     summary="Unblock insuarance",
     *     tags={"insurances"},
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
                'message' => 'Insurance not found',
                'statusCode' => 404,
            ], 404);
        }

        $insuarance->restore($id);

        return response([
            'message' => 'Insurance unblocked successfully',
            'statusCode' => 200,
        ], 200);
    }

}
