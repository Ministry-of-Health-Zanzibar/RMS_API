<?php

namespace App\Http\Controllers\API\Bills;

use App\Http\Controllers\Controller;
use App\Models\MonthlyBill;
use App\Models\Hospital;
use DB;
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
        if (!$user->can('View Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $monthlyBills = DB::table('monthly_bills')
            ->join('hospitals', 'hospitals.hospital_id', '=', 'monthly_bills.hospital_id')
            ->select(
                'hospitals.hospital_name',
                'monthly_bills.hospital_id',
                DB::raw('SUM(current_monthly_bill_amount) as total_monthly_bill'),
                DB::raw('SUM(after_audit_monthly_bill_amount) as total_after_audit_monthly_bill')
            )
            ->groupBy('monthly_bills.hospital_id', 'hospitals.hospital_name')
            ->get();

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
     *             @OA\Property(property="hospital_id", type="integer"),
     *             @OA\Property(property="bill_date", type="date"),
     *             @OA\Property(property="bill_file", type="string"),
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
        if (!$user->can('Create Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'current_monthly_bill_amount' => ['required', 'numeric'],
            'after_audit_monthly_bill_amount' => ['nullable', 'numeric'],
            'hospital_id' => ['numeric'],
            'bill_date' => ['nullable', 'date'],
            'bill_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:5120'], // 5MB max
        ]);


        // Only handle the file after validation passes
        $path = null;
        if (isset($data['bill_file'])) {
            $path = $data['bill_file']->store('documents', 'public');
        }


        $monthlyBill = MonthlyBill::create([
            'current_monthly_bill_amount' => $data['current_monthly_bill_amount'],
            'after_audit_monthly_bill_amount' => $data['after_audit_monthly_bill_amount'],
            'hospital_id' => $data['hospital_id'],
            'bill_date' => $data['bill_date'],
            'bill_file' => $path,
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
        if (!$user->can('View Monthly Bill')) {
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
            // Append full image URL
            if ($monthlyBill->bill_file) {
                $monthlyBill->billUrl = asset('storage/' . $monthlyBill->bill_file);
            } else {
                $monthlyBill->billUrl = null;
            }

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
     * @OA\Post(
     *     path="/api/monthly-bills/update/{monthlyBillId}",
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
     * @OA\Property(property="hospital_id", type="integer"),
     *             @OA\Property(property="bill_date", type="date"),
     *             @OA\Property(property="bill_file", type="string"),
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         ),
     *     ),
     * ),
     */
    public function updateMonthlyBill(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->can('Update Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'current_monthly_bill_amount' => ['required', 'numeric'],
            'after_audit_monthly_bill_amount' => ['nullable', 'numeric'],
            'hospital_id' => ['numeric'],
            'bill_date' => ['nullable', 'date'],
            'bill_file' => ['nullable', 'mimes:pdf,doc,docx,jpg,png'],
        ]);

        // $monthlyBill = MonthlyBill::find($id);
        $monthlyBill = MonthlyBill::findOrFail($id);


        // Handle file upload if provided
        if ($request->hasFile('bill_file')) {
            $path = $request->file('bill_file')->store('documents', 'public');
            $data['bill_file'] = $path;
        } else {
            unset($data['bill_file']);
        }

        $data['created_by'] = Auth::id();

        $monthlyBill->update($data);

        return response([
            'data' => $monthlyBill,
            'statusCode' => 200,
        ], 200);

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
        if (!$user->can('Delete Monthly Bill')) {
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



    /**
     * @OA\Get(
     *     path="/api/monthly-bills/by-hospital/{hospitalId}",
     *     tags={"monthly-bills"},
     *     summary="Get all monthly bills by hospital ID",
     *     description="Returns hospital details and all monthly bills for the specified hospital.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="hospitalId",
     *         in="path",
     *         required=true,
     *         description="Hospital ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="hospital",
     *                     type="object",
     *                     @OA\Property(property="hospital_id", type="integer", example=1),
     *                     @OA\Property(property="hospital_name", type="string", example="Mnazi Mmoja Hospital")
     *                 ),
     *                 @OA\Property(
     *                     property="all_bills",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="monthly_bill_id", type="integer", example=101),
     *                         @OA\Property(property="current_monthly_bill_amount", type="number", format="float", example="500000.00"),
     *                         @OA\Property(property="after_audit_monthly_bill_amount", type="number", format="float", example="480000.00"),
     *                         @OA\Property(property="bill_date", type="string", format="date", example="2025-01-15"),
     *                         @OA\Property(property="bill_file", type="string", example="jan_bill.pdf"),
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - user does not have permission",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hospital not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Hospital not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function viewBillsByHospitalId(int $hospitalId)
    {
        $user = auth()->user();
        if (!$user->can('View Monthly Bill')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Fetch hospital
        $hospital = Hospital::find($hospitalId);

        if (!$hospital) {
            return response([
                'message' => 'Hospital not found',
                'statusCode' => 404,
            ], 404);
        }

        // Fetch all bills for this hospital
        $hospitalBills = MonthlyBill::withTrashed()
            ->where('hospital_id', $hospitalId)
            ->get()
            ->map(function ($bill) {
                $bill->billUrl = $bill->bill_file ? asset('storage/' . $bill->bill_file) : null;
                return $bill;
            });

        return response([
            'data' => [
                'hospital' => $hospital,       // hospital details
                'all_bills' => $hospitalBills  // all bills for that hospital
            ],
            'statusCode' => 200,
        ]);
    }
}
