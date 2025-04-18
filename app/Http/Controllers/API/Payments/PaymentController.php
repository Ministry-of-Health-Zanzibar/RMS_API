<?php

namespace App\Http\Controllers\API\Payments;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="API Endpoints for Managing Payments"
 * )
 */
class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Payment|Create Payment|View Payment|Update Payment|Delete Payment', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }
    /**
     * @OA\Get(
     *     path="/api/payments",
     *     tags={"Payments"},
     *     summary="Get a list of payments",
     *     @OA\Response(response=200, description="List of payments")
     * )
     */
    public function index()
    {
        $payments = Payment::latest()->paginate(10);
        return response()->json($payments);
    }

    /**
     * @OA\Post(
     *     path="/api/payments",
     *     tags={"Payments"},
     *     summary="Create a new payment",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bill_id","amount_paid"},
     *             @OA\Property(property="bill_id", type="integer", example=1),
     *             @OA\Property(property="amount_paid", type="number", example=5000),
     *             @OA\Property(property="payment_method", type="string", example="PBZ Bank")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Payment created successfully")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,bill_id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $payment = Payment::create($validated);

        return response()->json([
            'message' => 'Payment created successfully.',
            'payment' => $payment,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     summary="Get a single payment by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Payment data")
     * )
     */
    public function show(string $id)
    {
        $payment = Payment::findOrFail($id);
        return response()->json($payment);
    }

    /**
     * @OA\Put(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     summary="Update a payment",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount_paid"},
     *             @OA\Property(property="amount_paid", type="number", example=6000),
     *             @OA\Property(property="payment_method", type="string", example="CASH")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment updated successfully")
     * )
     */
    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully.',
            'payment' => $payment,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     summary="Delete a payment",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Payment deleted successfully")
     * )
     */
    public function destroy(string $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully.']);
    }
}
