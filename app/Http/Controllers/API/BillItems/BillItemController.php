<?php

namespace App\Http\Controllers\API\BillItems;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillItem;
use Illuminate\Support\Facades\Validator;

class BillItemController extends Controller
{
    /**
     * Display a listing of all Bill Items.
     */
    public function index()
    {
        $billItems = BillItem::with('bill')->get();

        return response()->json([
            'data' => $billItems,
            'message' => 'Bill items retrieved successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Store a newly created Bill Item.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id'     => 'required|exists:bills,bill_id',
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $billItem = BillItem::create($validator->validated());

        return response()->json([
            'data' => $billItem,
            'message' => 'Bill item created successfully',
            'statusCode' => 201
        ], 201);
    }

    /**
     * Display the specified Bill Item.
     */
    public function show(string $id)
    {
        $billItem = BillItem::with('bill')->find($id);

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
     * Update the specified Bill Item.
     */
    public function update(Request $request, string $id)
    {
        $billItem = BillItem::find($id);

        if (!$billItem) {
            return response()->json([
                'message' => 'Bill item not found',
                'statusCode' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'bill_id'     => 'sometimes|exists:bills,bill_id',
            'description' => 'sometimes|string|max:255',
            'amount'      => 'sometimes|numeric|min:0',
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
     * Remove the specified Bill Item.
     */
    public function destroy(string $id)
    {
        $billItem = BillItem::find($id);

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
}
