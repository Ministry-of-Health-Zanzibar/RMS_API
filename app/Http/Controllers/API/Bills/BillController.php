<?php

namespace App\Http\Controllers\API\Bills;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\BillFile;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Bill|Create Bill|View Bill|Update Bill|Delete Bill', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }


    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/bills",
     *     tags={"Bills"},
     *     summary="Get list of bills with related information",
     *     description="Retrieve all bills including bill items, bill file, payments, referral details, patients, reasons, and hospitals.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Bills retrieved successfully OR no data found",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="statusCode", type="integer", example=200),
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="bill_id", type="integer", example=1),
     *                             @OA\Property(property="referral_id", type="integer", example=5),
     *                             @OA\Property(property="total_amount", type="number", format="float", example=15000),
     *                             @OA\Property(property="bill_status", type="string", example="PENDING"),
     *                             @OA\Property(
     *                                 property="billItems",
     *                                 type="array",
     *                                 @OA\Items(
     *                                     type="object",
     *                                     @OA\Property(property="item_id", type="integer", example=10),
     *                                     @OA\Property(property="description", type="string", example="X-ray service"),
     *                                     @OA\Property(property="amount", type="number", example=5000)
     *                                 )
     *                             ),
     *                             @OA\Property(
     *                                 property="billFile",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="bill_file_id", type="integer", example=2),
     *                                 @OA\Property(property="bill_file_title", type="string", example="Mnazi Mmoja Hospital August 2025 Bill"),
     *                                 @OA\Property(property="bill_file", type="string", example="uploads/bills/august2025.pdf")
     *                             ),
     *                             @OA\Property(
     *                                 property="payments",
     *                                 type="array",
     *                                 @OA\Items(
     *                                     type="object",
     *                                     @OA\Property(property="payment_id", type="integer", example=10),
     *                                     @OA\Property(property="amount_paid", type="number", example=6000),
     *                                     @OA\Property(property="currency", type="string", example="USD"),
     *                                     @OA\Property(property="payment_method", type="string", example="CASH")
     *                                 )
     *                             ),
     *                             @OA\Property(
     *                                 property="referral",
     *                                 type="object",
     *                                 @OA\Property(property="referral_id", type="integer", example=5),
     *                                 @OA\Property(
     *                                     property="patient",
     *                                     type="object",
     *                                     nullable=true,
     *                                     @OA\Property(property="patient_id", type="integer", example=101),
     *                                     @OA\Property(property="first_name", type="string", example="Jane"),
     *                                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                                     @OA\Property(property="dob", type="string", format="date", example="1990-02-15"),
     *                                     @OA\Property(property="gender", type="string", example="Female")
     *                                 ),
     *                                 @OA\Property(
     *                                     property="reason",
     *                                     type="object",
     *                                     nullable=true,
     *                                     @OA\Property(property="reason_id", type="integer", example=12),
     *                                     @OA\Property(property="description", type="string", example="Surgery Treatment")
     *                                 ),
     *                                 @OA\Property(
     *                                     property="hospital",
     *                                     type="object",
     *                                     @OA\Property(property="hospital_id", type="integer", example=3),
     *                                     @OA\Property(property="hospital_name", type="string", example="Mnazi Mmoja Hospital"),
     *                                     @OA\Property(property="hospital_code", type="string", example="MMH001"),
     *                                     @OA\Property(property="address", type="string", example="Stone Town, Zanzibar"),
     *                                     @OA\Property(property="phone", type="string", example="+255 777 123 456")
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="statusCode", type="integer", example=200),
     *                     @OA\Property(property="message", type="string", example="No data found")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();
        if (
            !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) ||
            !$user->can('View Bill')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $bills = Bill::with([
            'billItems',
            'billFile',
            'payments',
            'referral.patient',
            'referral.reason',
            'referral.hospital'
        ])->withTrashed()->get();

        // Transform each bill to ensure arrays
        $bills = $bills->map(function($bill) {
            $bill->patient = $bill->patient;
            $bill->reason = $bill->reason;
            $bill->hospital = $bill->hospital;
            return $bill;
        });

        return response([
            'data' => $bills,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/bills",
     *     summary="Create a new bill",
     *     tags={"Bills"},
     *     description="Create a new bill for a referral linked to a bill file",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"referral_id","total_amount","bill_file_id"},
     *             @OA\Property(property="referral_id", type="integer", example=1),
     *             @OA\Property(property="total_amount", type="number", format="float", example=5000),
     *             @OA\Property(property="bill_period_start", type="string", example="2025-08-01"),
     *             @OA\Property(property="bill_period_end", type="string", example="2025-08-31"),
     *             @OA\Property(property="bill_file_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bill created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Bill created successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (
            !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) ||
            !$user->can('Create Bill')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_id' => ['required', 'numeric'],
            'total_amount' => ['required', 'numeric'],
            'bill_period_start' => ['nullable', 'string'],
            'bill_period_end' => ['nullable', 'string'],
            'bill_file_id' => ['required', 'numeric'],
        ]);

        // Ensure referral exists
        $referral = Referral::find($data['referral_id']);
        if (!$referral) {
            return response([
                'message' => 'Referral not found',
                'statusCode' => 404,
            ], 404);
        }

        // Ensure bill_file exists
        $billFile = BillFile::find($data['bill_file_id']);
        if (!$billFile) {
            return response([
                'message' => 'Bill file not found',
                'statusCode' => 404,
            ], 404);
        }

        // Calculate already used amount in this bill_file
        $usedAmount = Bill::where('bill_file_id', $data['bill_file_id'])
            ->sum('total_amount');

        // Check if adding this bill would exceed the limit
        if ($usedAmount + $data['total_amount'] > $billFile->bill_file_amount) {
            return response([
                'message' => 'The total amount exceeds the allowed bill file amount of ' . $billFile->bill_file_amount,
                'statusCode' => 422
            ], 422);
        }

        // Create Bill
        $billData = [
            'referral_id' => $data['referral_id'],
            'total_amount' => $data['total_amount'] ?? 0,
            'bill_period_start' => $data['bill_period_start'] ?? null,
            'bill_period_end' => $data['bill_period_end'] ?? null,
            'sent_date' => Carbon::now(),
            'bill_file_id' => $data['bill_file_id'] ?? null,
            'bill_status' => 'Pending',
            'created_by' => Auth::id(),
        ];

        $bill = Bill::create($billData);

        if ($bill) {
            return response([
                'data' => $bill,
                'message' => 'Bill created successfully',
                'statusCode' => 200,
            ], 200);
        }

        return response([
            'message' => 'Internal server error',
            'statusCode' => 500,
        ], 500);
    }


    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/bills/{billId}",
     *     tags={"Bills"},
     *     summary="Find bill by ID with related information",
     *     description="Retrieve a single bill including bill items, bill file, payments, referral details, patients, reasons, and hospitals.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="billId",
     *         in="path",
     *         required=true,
     *         description="ID of the bill to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill retrieved successfully",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="statusCode", type="integer", example=200),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="bill_id", type="integer", example=1),
     *                         @OA\Property(property="referral_id", type="integer", example=5),
     *                         @OA\Property(property="total_amount", type="number", format="float", example=15000),
     *                         @OA\Property(property="bill_status", type="string", example="PENDING"),
     *                         @OA\Property(
     *                             property="billItems",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="item_id", type="integer", example=10),
     *                                 @OA\Property(property="description", type="string", example="X-ray service"),
     *                                 @OA\Property(property="amount", type="number", example=5000)
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="billFile",
     *                             type="object",
     *                             nullable=true,
     *                             @OA\Property(property="bill_file_id", type="integer", example=2),
     *                             @OA\Property(property="bill_file_title", type="string", example="August 2025 Bill"),
     *                             @OA\Property(property="bill_file", type="string", example="uploads/bills/august2025.pdf")
     *                         ),
     *                         @OA\Property(
     *                             property="payments",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="payment_id", type="integer", example=10),
     *                                 @OA\Property(property="amount_paid", type="number", example=6000),
     *                                 @OA\Property(property="currency", type="string", example="USD"),
     *                                 @OA\Property(property="payment_method", type="string", example="CASH")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="referral",
     *                             type="object",
     *                             @OA\Property(property="referral_id", type="integer", example=5),
     *                             @OA\Property(
     *                                 property="patient",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="patient_id", type="integer", example=101),
     *                                 @OA\Property(property="first_name", type="string", example="Jane"),
     *                                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                                 @OA\Property(property="dob", type="string", format="date", example="1990-02-15"),
     *                                 @OA\Property(property="gender", type="string", example="Female")
     *                             ),
     *                             @OA\Property(
     *                                 property="reason",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="reason_id", type="integer", example=12),
     *                                 @OA\Property(property="description", type="string", example="Surgery Treatment")
     *                             ),
     *                             @OA\Property(
     *                                 property="hospital",
     *                                 type="object",
     *                                 @OA\Property(property="hospital_id", type="integer", example=3),
     *                                 @OA\Property(property="hospital_name", type="string", example="Mnazi Mmoja Hospital"),
     *                                 @OA\Property(property="hospital_code", type="string", example="MMH001"),
     *                                 @OA\Property(property="address", type="string", example="Stone Town, Zanzibar"),
     *                                 @OA\Property(property="phone", type="string", example="+255 777 123 456")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="statusCode", type="integer", example=404),
     *                     @OA\Property(property="message", type="string", example="Bill not found")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $user = auth()->user();
        if (
            !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) ||
            !$user->can('View Bill')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $bill = Bill::with([
            'billItems',
            'billFile',
            'payments',
            'referral.patient',
            'referral.reason',
            'referral.hospital'
        ])->withTrashed()->find($id);

        if (!$bill) {
            return response([
                'message' => 'Bill not found',
                'statusCode' => 404,
            ], 404);
        }

        return response([
            'data' => $bill,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/bills-by-bill-file/{billFileId}",
     *     tags={"Bills"},
     *     summary="Find bill by ID with related information",
     *     description="Retrieve list of bills including bill items, bill file, payments, referral details, patients, reasons, and hospitals.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="billFileId",
     *         in="path",
     *         required=true,
     *         description="ID of the bill to retrieve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill retrieved successfully",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="statusCode", type="integer", example=200),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="bill_id", type="integer", example=1),
     *                         @OA\Property(property="referral_id", type="integer", example=5),
     *                         @OA\Property(property="total_amount", type="number", format="float", example=15000),
     *                         @OA\Property(property="bill_status", type="string", example="PENDING"),
     *                         @OA\Property(
     *                             property="billItems",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="item_id", type="integer", example=10),
     *                                 @OA\Property(property="description", type="string", example="X-ray service"),
     *                                 @OA\Property(property="amount", type="number", example=5000)
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="billFile",
     *                             type="object",
     *                             nullable=true,
     *                             @OA\Property(property="bill_file_id", type="integer", example=2),
     *                             @OA\Property(property="bill_file_title", type="string", example="August 2025 Bill"),
     *                             @OA\Property(property="bill_file", type="string", example="uploads/bills/august2025.pdf")
     *                         ),
     *                         @OA\Property(
     *                             property="payments",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="payment_id", type="integer", example=10),
     *                                 @OA\Property(property="amount_paid", type="number", example=6000),
     *                                 @OA\Property(property="currency", type="string", example="USD"),
     *                                 @OA\Property(property="payment_method", type="string", example="CASH")
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="referral",
     *                             type="object",
     *                             @OA\Property(property="referral_id", type="integer", example=5),
     *                             @OA\Property(
     *                                 property="patient",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="patient_id", type="integer", example=101),
     *                                 @OA\Property(property="first_name", type="string", example="Jane"),
     *                                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                                 @OA\Property(property="dob", type="string", format="date", example="1990-02-15"),
     *                                 @OA\Property(property="gender", type="string", example="Female")
     *                             ),
     *                             @OA\Property(
     *                                 property="reason",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="reason_id", type="integer", example=12),
     *                                 @OA\Property(property="description", type="string", example="Surgery Treatment")
     *                             ),
     *                             @OA\Property(
     *                                 property="hospital",
     *                                 type="object",
     *                                 @OA\Property(property="hospital_id", type="integer", example=3),
     *                                 @OA\Property(property="hospital_name", type="string", example="Mnazi Mmoja Hospital"),
     *                                 @OA\Property(property="hospital_code", type="string", example="MMH001"),
     *                                 @OA\Property(property="address", type="string", example="Stone Town, Zanzibar"),
     *                                 @OA\Property(property="phone", type="string", example="+255 777 123 456")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="statusCode", type="integer", example=404),
     *                     @OA\Property(property="message", type="string", example="Bill not found")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     */
    public function getBillsByBillFile(int $id)
    {
        $user = auth()->user();
        if (
            !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) ||
            !$user->can('View Bill')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $bills = Bill::with([
            'billItems',
            'billFile',
            'payments',
            'referral.patient',
            'referral.reason',
            'referral.hospital'
        ])
        ->withTrashed()
        ->where('bill_file_id', $id)
        ->get();

        return response([
            'data' => $bills,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/bills/{billId}",
     *     summary="Update an existing bill",
     *     tags={"Bills"},
     *     description="Update a bill's referral, amount, period, status, or bill file",
     *     @OA\Parameter(
     *         name="billId",
     *         in="path",
     *         required=true,
     *         description="Bill ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="referral_id", type="integer", example=1),
     *             @OA\Property(property="total_amount", type="number", format="float", example=5500),
     *             @OA\Property(property="bill_period_start", type="string", example="2025-08-01"),
     *             @OA\Property(property="bill_period_end", type="string", example="2025-08-31"),
     *             @OA\Property(property="bill_file_id", type="integer", example=2),
     *             @OA\Property(property="bill_status", type="string", enum={"Pending","Partially Paid","Paid"}, example="Pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Bill updated successfully."),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bill not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bill not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function updateBill(Request $request, int $id)
    {
        $user = auth()->user();

        // Ensure user has role AND permission
        if (!($user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) 
            && $user->can('Update Bill'))) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Find the Bill
        $bill = Bill::findOrFail($id);

        // Validate input
        $data = $request->validate([
            'referral_id' => ['required', 'numeric'],
            'total_amount' => ['nullable', 'numeric'],
            'bill_period_start' => ['nullable', 'string'],
            'bill_period_end' => ['nullable', 'string'],
            'bill_file_id' => ['nullable', 'numeric', 'exists:bill_files,bill_file_id'],
            'bill_status' => ['nullable', 'string', 'in:Pending,Partially Paid,Paid'],
        ]);

        // Update created_by to the current user
        $data['created_by'] = Auth::id();

        // Update the bill
        $bill->update($data);

        return response()->json([
            'data' => $bill,
            'message' => 'Bill updated successfully.',
            'statusCode' => 200,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/bills/{billId}",
     *     summary="Delete patient",
     *     tags={"Bills"},
     *     @OA\Parameter(
     *         name="billId",
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Delete Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $bill = Bill::withTrashed()->find($id);

        if (!$bill) {
            return response([
                'message' => 'Bill not found',
                'statusCode' => 404,
            ]);
        }

        $bill->delete();

        return response([
            'message' => 'Bill blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/bills/unblock/{billId}",
     *     summary="Unblock bill",
     *     tags={"Bills"},
     *     @OA\Parameter(
     *         name="billId",
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
    public function unBlockBill(int $id)
    {

        $bill = Bill::withTrashed()->find($id);

        if (!$bill) {
            return response([
                'message' => 'Bill not found',
                'statusCode' => 404,
            ], 404);
        }

        $bill->restore($id);

        return response([
            'message' => 'Bill unbocked successfully',
            'statusCode' => 200,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/bills/getPatientBillAndPaymentByBillId/{billId}",
     *     summary="Get patient, bill and payments by bill ID",
     *     tags={"Bills"},
     *     @OA\Parameter(
     *         name="billId",
     *         in="path",
     *         required=true,
     *         description="Bill ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="bill_id", type="integer", example=1),
     *                 @OA\Property(property="bill_total_amount", type="number", format="float", example=5000),
     *                 @OA\Property(property="bill_period_end", type="string", example="2025-08-31"),
     *                 @OA\Property(property="sent_date", type="string", format="date-time"),
     *                 @OA\Property(property="bill_status", type="string", example="Pending"),
     *                 @OA\Property(property="bill_file", type="string"),
     *                 @OA\Property(property="patient", type="object",
     *                     @OA\Property(property="patient_id", type="integer", example=10),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="date_of_birth", type="string", example="1990-01-01"),
     *                     @OA\Property(property="gender", type="string", example="Male"),
     *                     @OA\Property(property="phone", type="string", example="255712345678"),
     *                     @OA\Property(property="location", type="string", example="Dar es Salaam"),
     *                     @OA\Property(property="job", type="string", example="Teacher"),
     *                     @OA\Property(property="position", type="string", example="Senior")
     *                 ),
     *                 @OA\Property(property="payments", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="payment_id", type="integer", example=1),
     *                         @OA\Property(property="amount_paid", type="number", format="float", example=2000),
     *                         @OA\Property(property="payment_method", type="string", example="CASH"),
     *                         @OA\Property(property="payment_date", type="string", format="date-time")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No data found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No data found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function getPatientBillAndPaymentByBillId(int $billId)
    {
        $user = auth()->user();
        if (
            !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) ||
            !$user->can('View Patient')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Fetch the bill with patient and referral info
        $bill = DB::table('bills')
            ->join('referrals', 'referrals.referral_id', '=', 'bills.referral_id')
            ->join('patients', 'patients.patient_id', '=', 'referrals.patient_id')
            ->where('bills.bill_id', $billId)
            ->select(
                'patients.patient_id',
                'patients.name as patient_name',
                'patients.date_of_birth',
                'patients.gender',
                'patients.phone',
                'patients.location',
                'patients.job',
                'patients.position',
                'bills.bill_id',
                'bills.total_amount as bill_total_amount',
                'bills.bill_period_end',
                'bills.sent_date',
                'bills.bill_status',
                'bills.bill_file'
            )
            ->first();

        if (!$bill) {
            return response([
                'message' => 'No data found',
                'statusCode' => 404,
            ], 404);
        }

        // Fetch payments for this bill
        $payments = DB::table('payments')
            ->where('bill_id', $billId)
            ->select(
                'payment_id',
                'amount_paid',
                'payment_method',
                'created_at as payment_date'
            )
            ->get();

        // Format response
        $response = [
            'bill_id' => $bill->bill_id,
            'bill_total_amount' => $bill->bill_total_amount,
            'bill_period_end' => $bill->bill_period_end,
            'sent_date' => $bill->sent_date,
            'bill_status' => $bill->bill_status,
            'bill_file' => $bill->bill_file,
            'patient' => [
                'patient_id' => $bill->patient_id,
                'name' => $bill->patient_name,
                'date_of_birth' => $bill->date_of_birth,
                'gender' => $bill->gender,
                'phone' => $bill->phone,
                'location' => $bill->location,
                'job' => $bill->job,
                'position' => $bill->position,
            ],
            'payments' => $payments,
        ];

        return response([
            'data' => $response,
            'statusCode' => 200,
        ], 200);
    }

}