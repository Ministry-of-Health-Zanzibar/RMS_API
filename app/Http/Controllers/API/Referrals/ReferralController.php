<?php

namespace App\Http\Controllers\API\Referrals;

use App\Models\Insurance;
use App\Models\Referral;
use App\Models\Bill;
use App\Models\Payment;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Referral|View Referral|Create Referral|View Referral|Update Referral|Delete Referral', ['only' => ['index', 'getReferralwithBills', 'store', 'show', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/referrals",
     *     summary="Get all referrals",
     *     tags={"referrals"},
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
     *                     @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="patient_id", type="integer"),
     *                     @OA\Property(property="hospital_id", type="integer"),
     *                     @OA\Property(property="referral_type_id", type="integer"),
     *                     @OA\Property(property="reason_id", type="integer"),
     *                     @OA\Property(property="start_date", type="string", format="date-time"),
     *                     @OA\Property(property="end_date", type="string", format="date-time"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="confirmed_by", type="string"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // $referrals = Referral::withTrashed()->get();
        $referrals = DB::table('referrals')
            ->join("patients", "patients.patient_id", '=', 'referrals.patient_id')
            ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
            ->select(
                "referrals.*",

                "patients.name as patient_name",
                "patients.date_of_birth",
                "patients.gender",
                "patients.phone",

                "reasons.referral_reason_name"
            )
            ->get();

        if ($referrals) {
            return response([
                'data' => $referrals,
                'statusCode' => 200,
            ], 200);
        } else {
            return response([
                'message' => 'No data found',
                'statusCode' => 200,
            ], 200);
        }
    }

    public function getReferralwithBills()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // $referrals = Referral::withTrashed()->get();
        $referrals = DB::table('referrals')
            ->join("patients", "patients.patient_id", '=', 'referrals.patient_id')
            ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
            ->leftjoin("bills", "bills.referral_id", '=', 'referrals.referral_id')
            ->select(
                "referrals.*",



                "patients.name as patient_name",
                "patients.date_of_birth",
                "patients.gender",
                "patients.phone",

                "reasons.referral_reason_name",

                "bills.bill_id",
                "bills.amount",
                "bills.sent_to",
                "bills.bill_status",
            )
            ->get();

        if ($referrals) {
            return response([
                'data' => $referrals,
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
     *     path="/api/referrals",
     *     summary="Create referral",
     *     tags={"referrals"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="patient_id", type="integer"),
     *             @OA\Property(property="referral_type_id", type="integer"),
     *             @OA\Property(property="reason_id", type="integer"),
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="end_date", type="string", format="date-time"),
     *             @OA\Property(property="status", type="string"),
     *         ),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Create Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'numeric'],
            'referral_type_id' => ['required', 'numeric'],
            'reason_id' => ['required', 'numeric'],
        ]);


        $referral = Referral::create([
            'patient_id' => $data['patient_id'],
            'referral_type_id' => $data['referral_type_id'],
            'reason_id' => $data['reason_id'],
            'status' => 'Pending',
            'confirmed_by' => Auth::id(),
            'created_by' => Auth::id(),
        ]);

        if ($referral) {
            return response([
                'data' => $referral,
                'message' => 'Referral created successfully.',
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
     *     path="/api/referrals/{referral_id}",
     *     summary="Find referral by ID",
     *     tags={"referrals"},
     *     @OA\Parameter(
     *         name="referral_id",
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
     *                 @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="patient_id", type="integer"),
     *                     @OA\Property(property="referral_type_id", type="integer"),
     *                     @OA\Property(property="reason_id", type="integer"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="confirmed_by", type="string"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // $referral = Referral::withTrashed()->find($id);
        $referral = DB::table('referrals')
            ->join("patients", "patients.patient_id", '=', 'referrals.patient_id')
            ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
            ->select(
                "referrals.*",

                "patients.name as patient_name",
                "patients.date_of_birth",
                "patients.gender",
                "patients.phone",

                "reasons.referral_reason_name"
            )
            ->where("referrals.referral_id", '=', $id)
            ->first();


        if (!$referral) {
            return response([
                'message' => 'Referral not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $referral,
                'statusCode' => 200,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/referrals/{referral_id}",
     *     summary="Update referral",
     *     tags={"referrals"},
     *      @OA\Parameter(
     *         name="referral_id",
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
     *                    @OA\Property(property="patient_id", type="integer"),
     *                    @OA\Property(property="referral_type_id", type="integer"),
     *                    @OA\Property(property="reason_id", type="integer"),
     *                    @OA\Property(property="status"),
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Update Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'numeric'],
            'referral_type_id' => ['required', 'numeric'],
            'reason_id' => ['required', 'numeric'],
        ]);


        $referral = Referral::findOrFail($id);
        $referral->update([
            'patient_id' => $data['patient_id'],
            'referral_type_id' => $data['referral_type_id'],
            'reason_id' => $data['reason_id'],
            'confirmed_by' => Auth::id(),
            'created_by' => Auth::id(),
        ]);

        if ($referral) {
            return response([
                'data' => $referral,
                'message' => 'Referral updated successfully.',
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
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/referrals/{referral_id}",
     *     summary="Delete referral",
     *     tags={"referrals"},
     *     @OA\Parameter(
     *         name="referral_id",
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('Delete Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $referral = Referral::withTrashed()->find($id);

        if (!$referral) {
            return response([
                'message' => 'Referral not found',
                'statusCode' => 404,
            ]);
        }

        $referral->delete();

        return response([
            'message' => 'Referral blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/referrals/unBlock/{referral_id}",
     *     summary="Unblock referral",
     *     tags={"referrals"},
     *     @OA\Parameter(
     *         name="referral_id",
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
    public function unBlockReferral(int $id)
    {

        $referral = Referral::withTrashed()->find($id);

        if (!$referral) {
            return response([
                'message' => 'Referral not found',
                'statusCode' => 404,
            ], 404);
        }

        $referral->restore($id);

        return response([
            'message' => 'Referral unblocked successfully',
            'statusCode' => 200,
        ], 200);
    }


    public function getReferralsWithBills($referral_id)
    {
        $referrals = Referral::with('bills')
            ->where('referral_id', $referral_id)
            ->first();

        if (!$referrals) {
            return response()->json(['message' => 'No referrals with bills found'], 404);
        }
        // Append full image URL
        if ($referrals->referral_letter_file) {
            $referrals->documentUrl = asset('storage/' . $referrals->referral_letter_file);
        } else {
            $referrals->documentUrl = null;
        }

        $referrals->bills = $referrals->bills ?? [];
        return response()->json($referrals);
    }


    public function getReferralById(int $referral_id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $referral = DB::table('referrals')
            ->join("patients", "patients.patient_id", '=', 'referrals.patient_id')
            ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
            ->select(
                "referrals.*",

                "patients.name as patient_name",
                "patients.date_of_birth",
                "patients.gender",
                "patients.phone",

                "reasons.referral_reason_name"
            )
            ->where('referrals.referral_id', '=', $referral_id)
            ->first();

        if ($referral) {
            return response([
                'data' => $referral,
                'statusCode' => 200,
            ], 200);
        } else {
            return response([
                'message' => 'Referral not found',
                'statusCode' => 404,
            ], 200);
        }
    }


}