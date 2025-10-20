<?php

namespace App\Http\Controllers\API\BillPayments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillPayment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class BillPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    /**
     * Display all Bill Payments
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('View Payment')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billPayments = BillPayment::with(['bill', 'payment'])->get();

        return response()->json([
            'data' => $billPayments,
            'message' => 'Bill payments retrieved successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Store a new Bill Payment
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Payment')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'bill_id'          => 'required|exists:bills,bill_id',
            'payment_id'       => 'required|exists:payments,payment_id',
            'allocated_amount' => 'required|numeric|min:0',
            'allocation_date'  => 'nullable|string|max:255',
            'status'           => 'nullable|in:Pending,Partial,Settled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $billPayment = BillPayment::create($validator->validated());

        return response()->json([
            'data' => $billPayment,
            'message' => 'Bill payment created successfully',
            'statusCode' => 201
        ], 201);
    }

    /**
     * Display a specific Bill Payment
     */
    public function show(string $id)
    {
        $billPayment = BillPayment::with(['bill', 'payment'])->find($id);

        if (!$billPayment) {
            return response()->json([
                'message' => 'Bill payment not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $billPayment,
            'message' => 'Bill payment retrieved successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Update a specific Bill Payment
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->can('Update Payment')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billPayment = BillPayment::find($id);

        if (!$billPayment) {
            return response()->json([
                'message' => 'Bill payment not found',
                'statusCode' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'bill_id'          => 'sometimes|exists:bills,bill_id',
            'payment_id'       => 'sometimes|exists:payments,payment_id',
            'allocated_amount' => 'sometimes|numeric|min:0',
            'allocation_date'  => 'nullable|string|max:255',
            'status'           => 'nullable|in:Pending,Partial,Settled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $billPayment->update($validator->validated());

        return response()->json([
            'data' => $billPayment,
            'message' => 'Bill payment updated successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Delete a Bill Payment
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        if (!$user->can('Delete Payment')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billPayment = BillPayment::find($id);

        if (!$billPayment) {
            return response()->json([
                'message' => 'Bill payment not found',
                'statusCode' => 404
            ], 404);
        }

        $billPayment->delete();

        return response()->json([
            'message' => 'Bill payment deleted successfully',
            'statusCode' => 200
        ], 200);
    }
}
