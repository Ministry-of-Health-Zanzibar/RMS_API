<?php

namespace App\Http\Controllers\API\Bills;

use Carbon\Carbon;
use App\Models\Bill;
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
     *     summary="Get all bills",
     *     tags={"bills"},
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
     *                     @OA\Property(property="bill_id", type="integer", example=1),
     *                     @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="amount", type="double"),
     *                     @OA\Property(property="notes", type="string"),
     *                     @OA\Property(property="sent_to", type="string"),
     *                     @OA\Property(property="sent_date", type="string", format="date-time"),
     *                     @OA\Property(property="bill_file", type="string"),
     *                     @OA\Property(property="created_by", type="integer", example=1),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $bills = Bill::withTrashed()->get();

        if ($bills->isEmpty()) {
            return response([
                'message' => 'No data found',
                'statusCode' => 200,
            ], 200);
        } else {

            // Append full doc URL
            $bills = $bills->map(function ($bill) {
                $bill->billDocumentUrl = $bill->bill_file
                    ? asset('storage/' . $bill->bill_file)
                    : null;
                return $bill;
            });

            return response([
                'data' => $bills,
                'statusCode' => 200,
            ], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/bills",
     *     summary="Create bill",
     *     tags={"bills"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *                     @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="amount", type="double"),
     *                     @OA\Property(property="notes", type="string"),
     *                     @OA\Property(property="sent_to", type="string"),
     *                     @OA\Property(property="sent_date", type="string", format="date-time"),
     *                     @OA\Property(property="bill_file", type="string"),
     *         )
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
     *             @OA\Property(property="statusCode", type="integer", example="201")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Create Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_id' => ['required', 'numeric'],
            'amount' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'sent_to' => ['nullable', 'string'],
            'bill_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:1024'],
        ]);


        // Only handle the file after validation passes
        $path = null;
        if (isset($data['bill_file'])) {
            $path = $data['bill_file']->store('documents', 'public');
        }

        Referral::findOrFail($data['referral_id']);

        // Create Bill
        $bill = Bill::create([
            'referral_id' => $data['referral_id'],
            'amount' => $data['amount'],
            'notes' => $data['notes'],
            'sent_to' => $data['sent_to'],
            'sent_date' => Carbon::now(),
            'bill_file' => $path,
            'created_by' => Auth::id(),
        ]);

        if ($bill) {
            return response([
                'data' => $bill,
                'message' => 'Bill created successfully',
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
     *     path="/api/bills/{billId}",
     *     summary="Find bill by ID",
     *     tags={"bills"},
     *     @OA\Parameter(
     *         name="billId",
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
     *                 @OA\Property(property="bill_id", type="integer", example=1),
     *                     @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="amount", type="double"),
     *                     @OA\Property(property="notes", type="string"),
     *                     @OA\Property(property="sent_to", type="string"),
     *                     @OA\Property(property="sent_date", type="string", format="date-time"),
     *                     @OA\Property(property="bill_file", type="string"),
     *                     @OA\Property(property="created_by", type="integer", example=1),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Bill')) {
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
        } else {
            // Append full image URL
            if ($bill->bill_file) {
                $bill->billDocumentUrl = asset('storage/' . $bill->bill_file);
            } else {
                $bill->billDocumentUrl = null;
            }

            return response([
                'data' => $bill,
                'statusCode' => 200,
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/bills/update/{billId}",
     *     summary="Update bill",
     *     tags={"bills"},
     *      @OA\Parameter(
     *         name="billId",
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
     *                 @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="amount", type="double"),
     *                     @OA\Property(property="notes", type="string"),
     *                     @OA\Property(property="sent_to", type="string"),
     *                     @OA\Property(property="sent_date", type="string", format="date-time"),
     *                     @OA\Property(property="bill_file", type="string"),
     *                 ),
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function updateBill(Request $request, int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Update Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_id' => ['required', 'numeric'],
            'amount' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'sent_to' => ['nullable', 'string'],
            // 'bill_file' => ['nullable', 'string'],
            'bill_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:1024'],
        ]);

        $bill = Bill::findOrFail($id);

        // Handle file upload if provided
        if ($request->hasFile('bill_file')) {
            $path = $request->file('bill_file')->store('documents', 'public');
            $data['bill_file'] = $path;
        } else {
            unset($data['bill_file']);
        }

        $data['created_by'] = Auth::id();

        $bill->update($data);

        return response([
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
     *     tags={"bills"},
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
     *     tags={"bills"},
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
     *     summary="Get all bills, patient and payment",
     *     tags={"bills"},
     *   @OA\Parameter(
     *         name="billId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
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
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="bill_id", type="integer", example=1),
     *                     @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="amount", type="double"),
     *                     @OA\Property(property="notes", type="string"),
     *                     @OA\Property(property="sent_to", type="string"),
     *                     @OA\Property(property="sent_date", type="string", format="date-time"),
     *                     @OA\Property(property="bill_file", type="string"),
     *                     @OA\Property(property="created_by", type="integer", example=1),
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
    // public function getPatientBillAndPaymentByBillId(int $billId)
    // {
    //     $user = auth()->user();
    //     if (
    //         !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) ||
    //         !$user->can('View Patient')
    //     ) {
    //         return response([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     $data = DB::table('bills')
    //         ->join('referrals', 'referrals.referral_id', '=', 'bills.referral_id')
    //         ->join('patients', 'patients.patient_id', '=', 'referrals.patient_id')
    //         ->leftJoin('payments', 'payments.bill_id', '=', 'bills.bill_id')
    //         ->where('bills.bill_id', $billId)
    //         ->select(
    //             'patients.patient_id',
    //             'patients.name as patient_name',
    //             'patients.date_of_birth',
    //             'patients.gender',
    //             'patients.phone',
    //             'patients.location',
    //             'patients.job',
    //             'patients.position',
    //             'bills.bill_id',
    //             'bills.amount as bill_amount',
    //             'bills.sent_to',
    //             'bills.sent_date',
    //             'bills.bill_status',
    //             'bills.bill_file',
    //             'payments.payment_id',
    //             'payments.amount_paid',
    //             'payments.payment_method',
    //             'payments.created_at as payment_date'
    //         )
    //         ->get();

    //     if ($data->isEmpty()) {
    //         return response([
    //             'message' => 'No data found',
    //             'statusCode' => 404,
    //         ], 404);
    //     }

    //     return response([
    //         'data' => $data,
    //         'statusCode' => 200,
    //     ], 200);
    // }
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
                'bills.amount as bill_amount',
                'bills.sent_to',
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
            'bill_amount' => $bill->bill_amount,
            'sent_to' => $bill->sent_to,
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