<?php

namespace App\Http\Controllers\API\Bills;

use App\Http\Controllers\Controller;
use App\Models\MonthlyBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthlyBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Monthly Bill|Create Monthly Bill|View Monthly Bill|Update Monthly Bill|Delete Hospital', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/monthly-bills",
     *     summary="Get all monthly bills",
     *     tags={"monthly-bills"},
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
     *                     @OA\Property(property="monthly_bill_id", type="integer"),
     *                     @OA\Property(property="current_monthly_bill_amount", type="integer"),
     *                     @OA\Property(property="after_audit_monthly_bill_amount", type="integer"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF']) || !$user->can('View Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $monthlyBills = MonthlyBill::withTrashed()->get();

        if ($monthlyBills) {
            return response([
                'data' => $monthlyBills,
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
     *     path="/api/monthly-bills",
     *     summary="Create monthly bill",
     *     tags={"monthly-bills"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_monthly_bill_amount", type="integer"),
     *             @OA\Property(property="after_audit_monthly_bill_amount", type="integer"),
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
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF']) || !$user->can('Create Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'current_monthly_bill_amount' => ['required', 'numeric'],
            'after_audit_monthly_bill_amount' => ['nullable', 'numeric'],
        ]);


        $monthlyBill = MonthlyBill::create([
            'current_monthly_bill_amount' => $data['current_monthly_bill_amount'],
            'after_audit_monthly_bill_amount' => $data['after_audit_monthly_bill_amount'],
            'created_by' => Auth::id(),
        ]);

        if ($monthlyBill) {
            return response([
                'data' => $monthlyBill,
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
     *     path="/api/monthly-bills/{monthlyBillId}",
     *     summary="Find monthly bill by ID",
     *     tags={"monthly-bills"},
     *     @OA\Parameter(
     *         name="monthlyBillId",
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
     *                 @OA\Property(property="monthly_bill_id", type="integer", example=1),
     *                 @OA\Property(property="current_monthly_bill_amount", type="integer"),
     *                 @OA\Property(property="after_audit_monthly_bill_amount", type="integer"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-10T10:44:31.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-10T10:44:31.000000Z"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF']) || !$user->can('View Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $monthlyBill = MonthlyBill::withTrashed()->find($id);

        if (!$monthlyBill) {
            return response([
                'message' => 'MonthlyBill not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $monthlyBill,
                'statusCode' => 200,
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/monthly-bills/{monthlyBillId}",
     *     summary="Update monthly-bill",
     *     tags={"monthly-bills"},
     *      @OA\Parameter(
     *         name="monthlyBillId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
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
     *                 @OA\Property(property="current_monthly_bill_amount", type="integer"),
     *                 @OA\Property(property="after_audit_monthly_bill_amount", type="integer" ),
     *                 @OA\Property(property="contact_number", type="string"),
     *                 @OA\Property(property="hospital_email", type="string"),
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         ),
     *     ),
     * ),
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF']) || !$user->can('Update Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'current_monthly_bill_amount' => ['required', 'numeric'],
            'after_audit_monthly_bill_amount' => ['nullable', 'numeric'],
        ]);

        $monthlyBill = MonthlyBill::find($id);

        if (!$monthlyBill) {
            return response([
                'message' => 'MonthlyBill not found',
                'statusCode' => 404,
            ]);
        }


        $monthlyBill->update([
            'current_monthly_bill_amount' => $data['current_monthly_bill_amount'],
            'after_audit_monthly_bill_amount' => $data['after_audit_monthly_bill_amount'],
            'created_by' => Auth::id(),
        ]);

        if ($monthlyBill) {
            return response([
                'data' => $monthlyBill,
                'message' => 'MonthlyBill updated successfully',
                'statusCode' => 200,
            ], 201);
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
     *     path="/api/monthly-bills/{monthlyBillId}",
     *     summary="Delete monthly id",
     *     tags={"monthly-bills"},
     *     @OA\Parameter(
     *         name="monthlyBillId",
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF']) || !$user->can('Delete Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $monthlyBill = MonthlyBill::withTrashed()->find($id);

        if (!$monthlyBill) {
            return response([
                'message' => 'MonthlyBill not found',
                'statusCode' => 404,
            ]);
        }

        $monthlyBill->delete();

        return response([
            'message' => 'MonthlyBill blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/monthly-bills/unBlock/{monthlyId}",
     *     summary="Unblock monthly bill",
     *     tags={"monthly-bills"},
     *     @OA\Parameter(
     *         name="monthlyId",
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
    public function unBlockMonthlyBill(int $id)
    {

        $monthlyBill = MonthlyBill::withTrashed()->find($id);

        if (!$monthlyBill) {
            return response([
                'message' => 'MonthlyBill not found',
                'statusCode' => 404,
            ], 404);
        }

        $monthlyBill->restore($id);

        return response([
            'message' => 'MonthlyBill unblocked successfully',
            'statusCode' => 200,
        ], 200);
    }
}