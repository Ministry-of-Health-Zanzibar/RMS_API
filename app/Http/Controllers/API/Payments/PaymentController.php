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
     *     description="Create a payment for a monthly bill, including optional reference and voucher numbers",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"monthly_bill_id","amount_paid"},
     *             @OA\Property(property="monthly_bill_id", type="integer", example=1, description="ID of the monthly bill"),
     *             @OA\Property(property="amount_paid", type="number", format="float", example=5000),
     *             @OA\Property(property="payment_method", type="string", example="PBZ Bank"),
     *             @OA\Property(property="reference_number", type="string", example="REF20250822001", description="External payment reference"),
     *             @OA\Property(property="voucher_number", type="string", example="VCH-00123", description="Internal voucher number")
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
            'monthly_bill_id'   => 'required', // can be single or array
            'amount_paid'       => 'required|numeric|min:0',
            'payment_method'    => 'nullable|string|max:255',
            'reference_number'  => 'nullable|string|max:255',
            'voucher_number'    => 'nullable|string|max:255',
        ]);

        $validated['paid_by'] = $user->id;

        $payments = [];

        // If it's an array → multiple payments
        if (is_array($request->monthly_bill_id)) {
            foreach ($request->monthly_bill_id as $billId) {
                // validate each bill exists
                if (!\App\Models\MonthlyBill::where('monthly_bill_id', $billId)->exists()) {
                    return response()->json([
                        'message' => "Monthly bill with ID {$billId} not found."
                    ], 422);
                }

                $data = $validated;
                $data['monthly_bill_id'] = $billId;

                $payments[] = Payment::create($data);
            }
        } else {
            // Single payment
            $payments[] = Payment::create($validated);
        }

        return response()->json([
            'message' => 'Payment(s) created successfully.',
            'payments' => $payments,
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
     *     description="Update an existing payment's amount, method, reference number, and voucher number",
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
     *             @OA\Property(property="amount_paid", type="number", format="float", example=6000),
     *             @OA\Property(property="payment_method", type="string", example="CASH"),
     *             @OA\Property(property="reference_number", type="string", example="REF20250822001"),
     *             @OA\Property(property="voucher_number", type="string", example="VCH-00123")
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
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'voucher_number' => 'nullable|string|max:255',
            // 'paid_by' should usually not be updated
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
