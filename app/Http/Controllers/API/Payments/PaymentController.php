<?php

namespace App\Http\Controllers\API\Payments;

use App\Models\Payment;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


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
        $user = auth()->user();
        if (!$user->can('View Payment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }
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
     *     summary="Create a payment and allocate to bills from a bill file",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="bill_file_id", type="integer", example=1, description="ID of the bill file from which bills will be fetched"),
     *             @OA\Property(property="payer", type="string", example="John Doe", description="Ministry of Health Zanzibar"),
     *             @OA\Property(property="amount_paid", type="number", format="float", example=500000, description="Amount paid by the payer"),
     *             @OA\Property(property="currency", type="string", example="TZS", description="Currency of the payment"),
     *             @OA\Property(property="payment_method", type="string", example="Bank Transfer", description="Optional payment method"),
     *             @OA\Property(property="reference_number", type="string", example="REF12345", description="Optional reference number"),
     *             @OA\Property(property="voucher_number", type="string", example="VCH98765", description="Optional voucher number"),
     *             @OA\Property(property="payment_date", type="string", format="date-time", example="2025-09-01T10:00:00", description="Optional payment date; defaults to now if not provided")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment created and allocated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Payment created and allocated successfully."),
     *             @OA\Property(property="payment", type="object",
     *                 @OA\Property(property="payment_id", type="integer", example=1),
     *                 @OA\Property(property="payer", type="string", example="John Doe"),
     *                 @OA\Property(property="amount_paid", type="number", format="float", example=500000),
     *                 @OA\Property(property="currency", type="string", example="TZS"),
     *                 @OA\Property(property="payment_method", type="string", example="Bank Transfer"),
     *                 @OA\Property(property="reference_number", type="string", example="REF12345"),
     *                 @OA\Property(property="voucher_number", type="string", example="VCH98765"),
     *                 @OA\Property(property="payment_date", type="string", format="date-time", example="2025-09-01T10:00:00"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="bill_allocations", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="bill_payment_id", type="integer", example=1),
     *                     @OA\Property(property="bill_id", type="integer", example=101),
     *                     @OA\Property(property="payment_id", type="integer", example=1),
     *                     @OA\Property(property="allocated_amount", type="number", format="float", example=250000),
     *                     @OA\Property(property="allocation_date", type="string", format="date-time", example="2025-09-01T10:05:00"),
     *                     @OA\Property(property="status", type="string", example="Partial")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="statusCode", type="integer", example=422)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Payment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'bill_file_id'      => 'required|exists:bill_files,bill_file_id',
            'payer'             => 'required|string|max:255',
            'amount_paid'       => 'required|numeric|min:0',
            'currency'          => 'required|string|max:10',
            'payment_method'    => 'nullable|string|max:255',
            'reference_number'  => 'nullable|string|max:255',
            'voucher_number'    => 'nullable|string|max:255',
            'payment_date'      => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'    => 'Validation Error',
                'errors'     => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $validated = $validator->validated();
        $validated['created_by'] = $user->id;
        $validated['payment_date'] = $validated['payment_date'] ?? now();

        // Calculate total remaining for the bill_file_id (Pending or Partially Paid)
        $bills = Bill::where('bill_file_id', $validated['bill_file_id'])
            ->whereIn('bill_status', ['Pending', 'Partially Paid'])
            ->orderBy('bill_id')
            ->get();

        $totalDue = 0;
        foreach ($bills as $bill) {
            $alreadyAllocated = $bill->payments()->sum('allocated_amount');
            $totalDue += $bill->total_amount - $alreadyAllocated;
        }

        // Validate amount_paid does not exceed totalDue
        if ($validated['amount_paid'] > $totalDue) {
            return response()->json([
                'message'    => 'The paid amount cannot exceed the total remaining amount for this bill file.',
                'statusCode' => 422,
            ], 422);
        }

        // Use transaction for safety
        $result = DB::transaction(function () use ($validated, $user, $bills) {
            $remainingAmount = $validated['amount_paid'];

            // Create Payment
            $payment = Payment::create([
                'payer'            => $validated['payer'],
                'amount_paid'      => $validated['amount_paid'],
                'currency'         => $validated['currency'],
                'payment_method'   => $validated['payment_method'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'voucher_number'   => $validated['voucher_number'] ?? null,
                'payment_date'     => $validated['payment_date'],
                'created_by'       => $user->id,
            ]);

            $billPayments = [];

            foreach ($bills as $bill) {
                if ($remainingAmount <= 0) break;

                $alreadyAllocated = $bill->payments()->sum('allocated_amount');
                $billRemaining = $bill->total_amount - $alreadyAllocated;
                $allocation = min($remainingAmount, $billRemaining);

                $status = ($allocation + $alreadyAllocated) >= $bill->total_amount ? 'Paid' : 'Partially Paid';

                // Create bill payment
                $billPayment = BillPayment::create([
                    'bill_id'          => $bill->bill_id,
                    'payment_id'       => $payment->payment_id,
                    'allocated_amount' => $allocation,
                    'allocation_date'  => now(),
                    'status'           => $status,
                ]);

                $billPayments[] = $billPayment;

                // Update bill status
                $bill->update(['bill_status' => $status]);

                $remainingAmount -= $allocation;
            }

            return [
                'payment' => $payment,
                'bill_allocations' => $billPayments
            ];
        });

        return response()->json([
            'message'          => 'Payment created and allocated successfully.',
            'payment'          => $result['payment'],
            'bill_allocations' => $result['bill_allocations'],
            'statusCode'       => 200,
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
        $user = auth()->user();
        if (!$user->can('View Payment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $payment = Payment::findOrFail($id);
        return response()->json($payment);
    }

    /**
     * @OA\Put(
     *     path="/api/payments/{id}",
     *     tags={"Payments"},
     *     summary="Update a payment and optionally re-allocate across bills",
     *     description="Update an existing payment details like payer, amount, currency, method, reference number, voucher number, payment date, and optionally re-allocate payment across bills linked to a bill file.",
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
     *             @OA\Property(property="bill_file_id", type="integer", description="Optional: Bill file ID to re-allocate payment across bills referencing this file", example=2),
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
     *         description="Payment updated and re-allocated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment updated and re-allocated successfully."),
     *             @OA\Property(property="payment", type="object"),
     *             @OA\Property(property="bill_allocations", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->can('Update Payment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Find the payment
        $payment = Payment::findOrFail($id);

        // Validate request
        $validator = Validator::make($request->all(),[
            'bill_file_id'      => 'sometimes|exists:bill_files,bill_file_id',
            'payer'             => 'sometimes|string|max:255',
            'amount_paid'       => 'required|numeric|min:0',
            'currency'          => 'sometimes|string|max:10',
            'payment_method'    => 'nullable|string|max:255',
            'reference_number'  => 'nullable|string|max:255',
            'voucher_number'    => 'nullable|string|max:255',
            'payment_date'      => 'nullable|date',
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'message'    => 'Validation Error',
                'errors'     => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $validated = $validator->validated();

        // Default payment_date to now if not provided
        if (empty($validated['payment_date'])) {
            $validated['payment_date'] = now();
        }

        DB::transaction(function () use ($validated, $payment, $user) {

            // Update the payment record
            $payment->update([
                'payer'            => $validated['payer'] ?? $payment->payer,
                'amount_paid'      => $validated['amount_paid'],
                'currency'         => $validated['currency'] ?? $payment->currency,
                'payment_method'   => $validated['payment_method'] ?? $payment->payment_method,
                'reference_number' => $validated['reference_number'] ?? $payment->reference_number,
                'voucher_number'   => $validated['voucher_number'] ?? $payment->voucher_number,
                'payment_date'     => $validated['payment_date'] ?? $payment->payment_date,
                'updated_by'       => $user->id,
            ]);

            $billPayments = [];

            // Re-allocate payment if bill_file_id is provided
            if (!empty($validated['bill_file_id'])) {

                // Remove previous allocations for this payment
                BillPayment::where('payment_id', $payment->payment_id)->delete();

                // Fetch all pending bills for the bill file
                $bills = Bill::where('bill_file_id', $validated['bill_file_id'])
                            ->orderBy('bill_id') // optional: allocate in order
                            ->get();

                $remainingAmount = $validated['amount_paid'];

                foreach ($bills as $bill) {
                    if (!$bill || $remainingAmount <= 0) break;

                    // Calculate total already allocated to this bill
                    $totalAllocated = BillPayment::where('bill_id', $bill->bill_id)->sum('allocated_amount');

                    // Remaining amount for this bill
                    $billRemaining = $bill->total_amount - $totalAllocated;

                    $allocation = min($remainingAmount, $billRemaining);

                    // Determine allocation status
                    $status = $allocation >= $billRemaining ? 'Paid' : 'Partially Paid';

                    // Create bill payment record
                    $billPayment = BillPayment::create([
                        'bill_id'         => $bill->bill_id,
                        'payment_id'      => $payment->payment_id,
                        'allocated_amount'=> $allocation,
                        'allocation_date' => now(),
                        'status'          => $status,
                    ]);

                    $billPayments[] = $billPayment;

                    // Update the bill's status
                    $bill->bill_status = $status;
                    $bill->save();

                    $remainingAmount -= $allocation;
                }
            }
        });

        return response()->json([
            'message'          => 'Payment updated and re-allocated successfully.',
            'payment'          => $payment,
            'bill_allocations' => BillPayment::where('payment_id', $payment->payment_id)->get(),
            'statusCode'       => 200,
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
        $user = auth()->user();
        if (!$user->can('Delete Payment')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully.',
            'statusCode' => 200,
        ]);
    }
}
