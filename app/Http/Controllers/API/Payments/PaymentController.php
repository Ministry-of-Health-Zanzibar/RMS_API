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
        // $payments = Payment::latest()->paginate(10);
        $payments = Payment::latest()->get(); // <- get() executes the query

        return response()->json([
            'data' => $payments,
            'statusCode' => 200
        ]);
    }

    
    /**
     * @OA\Post(
     *     path="/api/payments",
     *     tags={"Payments"},
     *     summary="Create a new payment",
     *     description="Create a payment with payer, amount, currency, method, reference number, voucher number, and payment date",
     *     security={{"bearerAuth":{}}}, 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount_paid"},
     *             @OA\Property(property="payer", type="string", example="John Doe", description="Who made the payment"),
     *             @OA\Property(property="amount_paid", type="number", format="float", example=5000, description="Amount paid"),
     *             @OA\Property(property="currency", type="string", example="USD", description="Currency code"),
     *             @OA\Property(property="payment_method", type="string", example="PBZ Bank", description="Payment method or bank name"),
     *             @OA\Property(property="reference_number", type="string", example="REF20250822001", description="External payment reference"),
     *             @OA\Property(property="voucher_number", type="string", example="VCH-00123", description="Internal voucher number"),
     *             @OA\Property(property="payment_date", type="string", format="date", example="2025-08-31", description="Date when payment was made")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment created successfully."),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user(); // get current logged-in user

        $validated = $request->validate([
            'payer'             => 'required|string|max:255',
            'amount_paid'       => 'required|numeric|min:0',
            'currency'          => 'required|string|max:10', // e.g. TZS, USD
            'payment_method'    => 'nullable|string|max:255',
            'reference_number'  => 'nullable|string|max:255',
            'voucher_number'    => 'nullable|string|max:255',
            'payment_date'      => 'nullable|string', // defaults to today if not provided
        ]);

        $validated['created_by'] = $user->id;

        // set default payment_date if not provided
        if (empty($validated['payment_date'])) {
            $validated['payment_date'] = now();
        }

        $payment = Payment::create($validated);

        return response()->json([
            'message' => 'Payment created successfully.',
            'payment' => $payment,
            'statusCode' => 200,
        ], 200);
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
     *     description="Update an existing payment details like payer, amount, currency, method, reference number, voucher number, and payment date",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount_paid"},
     *             @OA\Property(property="payer", type="string", example="John Doe"),
     *             @OA\Property(property="amount_paid", type="number", format="float", example=6000),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="payment_method", type="string", example="CASH"),
     *             @OA\Property(property="reference_number", type="string", example="REF20250822001"),
     *             @OA\Property(property="voucher_number", type="string", example="VCH-00123"),
     *             @OA\Property(property="payment_date", type="string", format="date", example="2025-08-31")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment updated successfully."),
     *             @OA\Property(property="payment", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'payer'             => 'sometimes|string|max:255',
            'amount_paid'       => 'required|numeric|min:0',
            'currency'          => 'sometimes|string|max:10',
            'payment_method'    => 'nullable|string|max:255',
            'reference_number'  => 'nullable|string|max:255',
            'voucher_number'    => 'nullable|string|max:255',
            'payment_date'      => 'nullable|string',
        ]);

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully.',
            'payment' => $payment,
            'statusCode' => 200,
        ], 200);
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

        return response()->json([
            'message' => 'Payment deleted successfully.',
            'statusCode' => 200,
        ]);
    }
}
