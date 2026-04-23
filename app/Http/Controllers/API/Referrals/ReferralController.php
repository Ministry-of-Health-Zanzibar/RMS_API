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
    // public function index()
    // {
    //     $user = auth()->user();

    //     if (!$user->can('View Referral')) {
    //         return response(['message' => 'Forbidden', 'statusCode' => 403], 403);
    //     }

    //     // 1. Build Query with eager loading
    //     $query = Referral::with(['patient.patientHistories', 'reason', 'hospital', 'diagnoses'])
    //         ->where('status', '<>', 'Requested');

    //     // 2. Apply Role-based restriction
    //     if (!$user->hasRole(['ROLE DIRECTOR GENERAL', 'ROLE ADMIN'])) {
    //         $query->where('status', '<>', 'Pending');
    //     }

    //     // 3. Get results and group
    //     // Note: If data is huge, consider ->paginate() instead of ->get()
    //     $referrals = $query->latest()->get() 
    //         ->groupBy('referral_number')
    //         ->map(function ($group) {
    //             $first = $group->first();

    //             return [
    //                 'referral_number' => $first->referral_number,
    //                 'patient'         => $first->patient,
    //                 'diagnoses'       => $first->diagnoses,
    //                 'reason'          => $first->reason,
    //                 // Using map for clean status string
    //                 'status'          => $group->pluck('status')->unique()->sort()->implode(', '),
    //                 'hospitals'       => $group->pluck('hospital')->unique('hospital_id')->values(),
    //                 'referrals'       => $group->map(function ($ref) {
    //                     return [
    //                         'referral_id' => $ref->referral_id,
    //                         'status'      => $ref->status,
    //                         'hospital'    => $ref->hospital,
    //                         'created_at'  => $ref->created_at,
    //                         // ... add other fields if strictly necessary
    //                     ];
    //                 })->values(),
    //                 // Meta-data for easier sorting
    //                 'has_pending'     => $group->contains('status', 'Pending'),
    //                 'latest_activity' => $group->max('created_at'),
    //             ];
    //         })
    //         // 4. Sort: Pending first, then by date
    //         ->sort(function ($a, $b) {
    //             if ($a['has_pending'] !== $b['has_pending']) {
    //                 return $b['has_pending'] <=> $a['has_pending'];
    //             }
    //             return $b['latest_activity'] <=> $a['latest_activity'];
    //         })
    //         ->values();

    //     return response([
    //         'data' => $referrals,
    //         'statusCode' => 200,
    //     ], 200);
    // }
    public function index()
{
    $user = auth()->user();
    $dataEntryEmails = ['medicalboard@mohz.go.tz', 'hospital@mohz.go.tz', 'mkurugenzi@mohz.go.tz', 'dguser@mohz.go.tz'];

    if (!$user->can('View Referral')) {
        return response(['message' => 'Forbidden', 'statusCode' => 403], 403);
    }

    $isDataEntryUser = in_array($user->email, $dataEntryEmails);

    $query = Referral::with(['patient.patientHistories', 'reason', 'hospital', 'diagnoses'])
        ->where('status', '<>', 'Requested');

    // --- LOGIC YA KUTENGANISHA ---
    if ($isDataEntryUser) {
        $query->whereHas('patient.creator', function($q) use ($dataEntryEmails) {
            $q->whereIn('email', $dataEntryEmails);
        });
    } else {
        $query->whereHas('patient.creator', function($q) use ($dataEntryEmails) {
            $q->whereNotIn('email', $dataEntryEmails);
        });
    }

    if (!$user->hasRole(['ROLE DIRECTOR GENERAL', 'ROLE ADMIN'])) {
        $query->where('status', '<>', 'Pending');
    }

    $referrals = $query->latest()->get() 
        ->groupBy('referral_number')
        ->map(function ($group) {
            $first = $group->first();
            return [
                'referral_number' => $first->referral_number,
                'patient'         => $first->patient,
                'diagnoses'       => $first->diagnoses,
                'reason'          => $first->reason,
                'status'          => $group->pluck('status')->unique()->sort()->implode(', '),
                'hospitals'       => $group->pluck('hospital')->unique('hospital_id')->values(),
                'referrals'       => $group->map(function ($ref) {
                    return [
                        'referral_id' => $ref->referral_id,
                        'status'      => $ref->status,
                        'hospital'    => $ref->hospital,
                        'created_at'  => $ref->created_at,
                    ];
                })->values(),
                'has_pending'     => $group->contains('status', 'Pending'),
                'latest_activity' => $group->max('created_at'),
            ];
        })
        ->sort(function ($a, $b) {
            if ($a['has_pending'] !== $b['has_pending']) {
                return $b['has_pending'] <=> $a['has_pending'];
            }
            return $b['latest_activity'] <=> $a['latest_activity'];
        })
        ->values();

    return response(['data' => $referrals, 'statusCode' => 200], 200);
}




    public function getReferralwithBills()
    {
        $user = auth()->user();
        if (!$user->can('View Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

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

                "bills.*",
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
        if (
            !$user->can('Create Referral')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'patient_id' => ['required', 'numeric', 'exists:patients,patient_id'],
            'reason_id'  => ['required', 'numeric', 'exists:reasons,reason_id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        // --- Generate referral number ---
        $today = now()->format('Y-m-d'); // e.g. 2025-09-01
        $count = Referral::whereDate('created_at', $today)->count() + 1;
        $referralNumber = 'REF-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $referral = Referral::create([
            'patient_id'       => $request['patient_id'],
            'reason_id'        => $request['reason_id'],
            'status'           => 'Pending',
            'referral_number'  => $referralNumber,
            'created_by'       => Auth::id(),
        ]);

        if ($referral) {
            return response([
                'data'       => $referral,
                'message'    => 'Referral created successfully.',
                'statusCode' => 201,
            ], 201);
        } else {
            return response([
                'message'    => 'Internal server error',
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

        if (!$user->can('View Referral')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $referral = Referral::with([
            'patient' => function ($query) {
                $query->with([
                    'geographicalLocation',
                    'files',
                    'patientList.boardMembers',
                    'patientHistories' => function ($q) {
                        // Order by ID descending to make the newest record first
                        $q->orderBy('patient_histories_id', 'desc')->with([
                            'diagnoses',        // doctor diagnoses
                            'boardDiagnoses',   // board diagnoses
                            'reason',           // doctor reason
                            'boardReason',      // board reason
                        ]);
                    },
                ]);
            },
            'hospital',
            'hospitalLetters',
            'referralLetters',
            'parent',
            'children',
            'bills',
            'confirmedBy',
            'creator',
            'diagnoses',
        ])
        ->where('referral_id', $id)
        ->first();

        if (!$referral) {
            return response()->json([
                'message' => 'Referral not found',
                'statusCode' => 404,
            ], 404);
        }

        // --- AGE CALCULATION LOGIC ---
        // Extract the patient from the referral
        $patient = $referral->patient;

        if ($patient && $patient->date_of_birth) {
            // Check if the value is just a number (like '8')
            if (is_numeric($patient->date_of_birth)) {
                $patient->age_details = [
                    'years'  => 0, 'months' => 0, 'days' => 0,
                    'string' => "Invalid Date Data"
                ];
            } else {
                try {
                    $dob = \Carbon\Carbon::parse($patient->date_of_birth);
                    $now = \Carbon\Carbon::now();
                    $diff = $dob->diff($now);

                    $patient->age_details = [
                        'years'  => $diff->y,
                        'months' => $diff->m,
                        'days'   => $diff->d,
                        'string' => "{$diff->y}y {$diff->m}m {$diff->d}d"
                    ];
                } catch (\Exception $e) {
                    // Handle cases where data is "broken" but not numeric
                    $patient->age_details = ['string' => "Unknown"];
                }
            }
        }

        return response()->json([
            'data' => $referral,
            'statusCode' => 200,
        ], 200);
    }

    public function getHospitalLettersByReferralId($id)
    {
        $user = auth()->user();
        if (
            !$user->can('View Referral')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // 1. Find the referral
        $referral = Referral::find($id);

        if (!$referral) {
            return response()->json([
                'message' => 'Referral not found',
                'statusCode' => 404
            ], 404);
        }

        // 2. Get root referral
        $rootReferralId = $referral->parent_referral_id ?? $referral->referral_id;

        // 3. Get all referrals in the chain
        $referrals = Referral::with([
            'patient.geographicalLocation',
            'patient.patientList',
            'patient.files',
            'reason',
            'hospital',
            'hospitalLetters.followups'
        ])->where('referral_id', $rootReferralId)
        ->orWhere('parent_referral_id', $rootReferralId)
        ->get();

        if ($referrals->isEmpty()) {
            return response()->json([
                'message' => 'No related referrals found',
                'statusCode' => 404
            ], 404);
        }

        // 4. Build merged response
        $patient   = $referrals->first()->patient;
        $reason    = $referrals->first()->reason;
        $hospitals = $referrals->pluck('hospital')->unique('hospital_id')->values();
        $letters   = $referrals->pluck('hospitalLetters')->flatten(1)->values();
        $referralArr = $referrals->map(function ($r) {
            return [
                'referral_id'        => $r->referral_id,
                'parent_referral_id' => $r->parent_referral_id,
                'hospital_id'        => $r->hospital_id,
                'status'             => $r->status,
                'created_at'         => $r->created_at,
                'updated_at'         => $r->updated_at,
            ];
        });

        $result = [
            'referral_number'  => $referrals->first()->referral_number,
            'status'           => $referrals->pluck('status')->unique()->join(', '),
            'patient'          => $patient,
            'reason'           => $reason,
            'hospitals'        => $hospitals,
            'referrals'        => $referralArr,
            'hospital_letters' => $letters,
        ];

        return response()->json([
            'data'       => $result,
            'statusCode' => 200
        ]);
    }

    public function getReferralsByHospitalId(int $hospitalId, int $billFileId)
    {
        $user = auth()->user();

        // Permission check
        if (!$user->can('View Referral')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Fetch referrals that have NOT been billed in this bill file
        $referrals = DB::table('referrals')
            ->join("patients", "patients.patient_id", '=', 'referrals.patient_id')
            ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
            ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
            ->leftJoin("bills", function ($join) use ($billFileId) {
                $join->on("bills.referral_id", '=', "referrals.referral_id")
                    ->where("bills.bill_file_id", '=', $billFileId);
            })
            ->select(
                "referrals.referral_id",
                "referrals.referral_number",
                "patients.name as patient_name"
            )
            ->where("hospitals.hospital_id", '=', $hospitalId)
            ->whereNull("bills.referral_id") // exclude referrals already billed in this file
            ->get();

        return response()->json([
            'data' => $referrals,
            'statusCode' => 200,
        ], 200);
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
        if (!$user->can('Update Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'patient_id' => ['required', 'numeric'],
            'reason_id' => ['required', 'numeric'],
            'hospital_id' => ['nullable', 'numeric']
        ]);

        $referral = Referral::findOrFail($id);
        $referral->update([
            'patient_id' => $data['patient_id'],
            'reason_id' => $data['reason_id'],
            'hospital_id' => $data['hospital_id'] ?? null,
            'created_by' => Auth::id(),
        ]);

        if ($referral) {
            return response([
                'data' => $referral,
                'message' => 'Referral updated successfully.',
                'statusCode' => 201,
            ], 201);
        } else {
            return response([
                'message' => 'Internal server error',
                'statusCode' => 500,
            ], 500);
        }
    }

    public function chooseHospitalAndConfirmReferral(Request $request, int $id)
    {
        $user = auth()->user();
        if (!$user->can('Update Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'hospital_id' => ['required', 'numeric']
        ]);

        $referral = Referral::findOrFail($id);
        $referral->update([
            'hospital_id' => $data['hospital_id'],
            'status' => 'Confirmed',
            'confirmed_by' => Auth::id(),
        ]);

        if ($referral) {
            return response([
                'data' => $referral,
                'message' => 'Referral confirmed successfully.',
                'statusCode' => 201,
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
        if (!$user->can('Delete Referral')) {
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
        $user = auth()->user();
        if (!$user->can('View Referral')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

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
        if (!$user->can('View Referral')) {
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
