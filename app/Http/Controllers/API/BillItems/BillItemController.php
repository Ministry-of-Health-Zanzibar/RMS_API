<?php

namespace App\Http\Controllers\API\BillItems;

use App\Http\Controllers\Controller;
use App\Models\BillItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BillItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Bill Item|Create Bill Item|Update Bill Item|Delete Bill Item', ['only' => ['index', 'store', 'show', 'update', 'destroy', 'restore']]);
    }

    /**
     * @OA\Get(
     *     path="/api/bill-items",
     *     summary="Get all bill items",
     *     tags={"Bill Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="bill_item_id", type="integer", example=1),
     *                     @OA\Property(property="bill_id", type="integer", example=1),
     *                     @OA\Property(property="description", type="string", example="X-ray Charges"),
     *                     @OA\Property(property="amount", type="number", format="float", example=5000),
     *                     @OA\Property(property="created_by", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Bill items retrieved successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Bill Item')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billItems = BillItem::withTrashed()->with('bill')->get();

        if ($billItems->isEmpty()) {
            return response()->json([
                'message' => 'No data found',
                'statusCode' => 200
            ], 200);
        }

        return response()->json([
            'data' => $billItems,
            'message' => 'Bill items retrieved successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/bill-items",
     *     summary="Create a new bill item",
     *     tags={"Bill Items"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"bill_id","description","amount"},
     *             @OA\Property(property="bill_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Laboratory Fee"),
     *             @OA\Property(property="amount", type="number", format="float", example=2500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bill item created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Bill item created successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="statusCode", type="integer", example=422)
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Create Bill Item')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|exists:bills,bill_id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $billItem = BillItem::create($validator->validated() + ['created_by' => Auth::id()]);

        return response()->json([
            'data' => $billItem,
            'message' => 'Bill item created successfully',
            'statusCode' => 201
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/bill-items/{id}",
     *     summary="Get a specific bill item",
     *     tags={"Bill Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill item retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="bill_item_id", type="integer", example=1),
     *                 @OA\Property(property="bill_id", type="integer", example=1),
     *                 @OA\Property(property="description", type="string", example="X-ray Charges"),
     *                 @OA\Property(property="amount", type="number", format="float", example=5000),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *             ),
     *             @OA\Property(property="message", type="string", example="Bill item retrieved successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill item not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Bill item not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Bill Item')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billItem = BillItem::withTrashed()->with('bill')->find($id);

        if (!$billItem) {
            return response()->json([
                'message' => 'Bill item not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $billItem,
            'message' => 'Bill item retrieved successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/bill-items/{id}",
     *     summary="Update a bill item",
     *     tags={"Bill Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="bill_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="amount", type="number", format="float", example=3000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill item updated successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill item not found",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Update Bill Item')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billItem = BillItem::find($id);
        if (!$billItem) {
            return response()->json([
                'message' => 'Bill item not found',
                'statusCode' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'bill_id' => 'sometimes|exists:bills,bill_id',
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $billItem->update($validator->validated());

        return response()->json([
            'data' => $billItem,
            'message' => 'Bill item updated successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/bill-items/{id}",
     *     summary="Delete a bill item",
     *     tags={"Bill Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill item deleted successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill item not found",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Delete Bill Item')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billItem = BillItem::withTrashed()->find($id);

        if (!$billItem) {
            return response()->json([
                'message' => 'Bill item not found',
                'statusCode' => 404
            ], 404);
        }

        $billItem->delete();

        return response()->json([
            'message' => 'Bill item deleted successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/bill-items/restore/{id}",
     *     summary="Restore a soft-deleted bill item",
     *     tags={"Bill Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill item restored successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill item not found",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function restore(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Update Bill Item')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billItem = BillItem::withTrashed()->find($id);

        if (!$billItem) {
            return response()->json([
                'message' => 'Bill item not found',
                'statusCode' => 404
            ], 404);
        }

        $billItem->restore();

        return response()->json([
            'message' => 'Bill item restored successfully',
            'statusCode' => 200
        ], 200);
    }
}
