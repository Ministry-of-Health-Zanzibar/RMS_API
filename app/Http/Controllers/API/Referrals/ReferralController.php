<?php

namespace App\Http\Controllers\API\Referrals;

use App\Models\Insurance;
use App\Models\PatientHistory;
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

    // public function index()
    // {
    //     $user = auth()->user();
    //     $dataEntryEmails = ['medicalboard@mohz.go.tz', 'hospital@mohz.go.tz', 'mkurugenzi@mohz.go.tz', 'dguser@mohz.go.tz'];

    //     if (!$user->can('View Referral')) {
    //         return response(['message' => 'Forbidden', 'statusCode' => 403], 403);
    //     }

    //     $isDataEntryUser = in_array($user->email, $dataEntryEmails);

    //     $query = Referral::with(['patient', 'reason', 'hospital', 'diagnoses'])
    //         ->where('status', '<>', 'Requested');

    //     // --- LOGIC YA KUTENGANISHA ---
    //     if ($isDataEntryUser) {
    //         $query->whereHas('patient.creator', function ($q) use ($dataEntryEmails) {
    //             $q->whereIn('email', $dataEntryEmails);
    //         });
    //     } else {
    //         $query->whereHas('patient.creator', function ($q) use ($dataEntryEmails) {
    //             $q->whereNotIn('email', $dataEntryEmails);
    //         });
    //     }

    //     if (!$user->hasRole(['ROLE DIRECTOR GENERAL', 'ROLE ADMIN'])) {
    //         $query->where('status', '<>', 'Pending');
    //     }

    //     // -----------------------------
    //     // VIRTUAL REFERRALS (UNCHANGED)
    //     // -----------------------------
    //     $noReferralHistories = PatientHistory::with(['patient', 'diagnoses', 'reason'])
    //     ->whereDoesntHave('referrals')
    //     ->whereIn('status', ['requested', 'approved'])
    //     ->whereHas('patient.creator', function ($q) use ($dataEntryEmails, $isDataEntryUser) {
    //         if ($isDataEntryUser) {
    //             $q->whereIn('email', $dataEntryEmails);
    //         } else {
    //             $q->whereNotIn('email', $dataEntryEmails);
    //         }
    //     })
    //     ->latest()
    //     ->get();
        
    //     $boardedOutHistories = PatientHistory::with(['patient', 'diagnoses', 'reason'])
    //     ->whereHas('boardedOutLetters')
    //     ->whereHas('patient.creator', function ($q) use ($dataEntryEmails, $isDataEntryUser) {
    //         if ($isDataEntryUser) {
    //             $q->whereIn('email', $dataEntryEmails);
    //         } else {
    //             $q->whereNotIn('email', $dataEntryEmails);
    //         }
    //     })
    //     ->latest()
    //     ->get();

    //     $boardedOutVirtuals = $boardedOutHistories->map(function ($history) {

    //         $boardedOut = $history->boardedOutLetters->last(); // latest letter
        
    //         return [
    //             'referral_number' => 'BO-' . $history->patient_histories_id,
    //             'patient'         => $history->patient,
    //             'diagnoses'       => $history->diagnoses,
    //             'reason'          => $history->reason,
    //             'status'          => 'BoardedOut', // 🔥 KEY
    //             'hospitals'       => collect([]),
    //             'referrals'       => [],
    //             'has_pending'     => false,
    //             'latest_activity' => $boardedOut?->created_at ?? $history->updated_at,
    //             'is_boarded_out'  => true,
    //             'history_id'      => $history->patient_histories_id,
        
    //             // optional extra data
    //             'boarded_out' => [
    //                 'receiver' => $boardedOut?->receiver,
    //                 'reference_number' => $boardedOut?->reference_number,
    //                 'reference_date' => $boardedOut?->reference_date,
    //                 'recommendations' => $boardedOut?->recommendations,
    //             ]
    //         ];
    //     });

    //     $virtualReferrals = $noReferralHistories->map(function ($history) {
    //         return [
    //             'referral_number' => 'N/A-' . $history->patient_histories_id,
    //             'patient'         => $history->patient,
    //             'diagnoses'       => $history->diagnoses,
    //             'reason'          => $history->reason,
    //             'status'          => 'Pending',
    //             'hospitals'       => collect([]),
    //             'referrals'       => [],
    //             'has_pending'     => true,
    //             'latest_activity' => $history->updated_at,
    //             'is_recommendation_only' => true,
    //             'history_id'      => $history->patient_histories_id
    //         ];
    //     });

    //     // -----------------------------
    //     // REAL REFERRALS
    //     // -----------------------------
    //     $referrals = $query->latest()->get()
    //         ->groupBy('referral_number')
    //         ->map(function ($group) {

    //             $first = $group->first();

    //             // 🔥 SAFE FIX: get latest history directly (no relation dependency)
    //             $historyId = PatientHistory::where('patient_id', $first->patient_id ?? null)
    //                 ->latest('created_at')
    //                 ->value('patient_histories_id');

    //             return [
    //                 'referral_number' => $first->referral_number,
    //                 'patient'         => $first->patient,
    //                 'diagnoses'       => $first->diagnoses,
    //                 'reason'          => $first->reason,
    //                 'status'          => $group->pluck('status')->unique()->sort()->implode(', '),
    //                 'hospitals'       => $group->pluck('hospital')->unique('hospital_id')->values(),
    //                 'referrals'       => $group->map(function ($ref) {
    //                     return [
    //                         'referral_id' => $ref->referral_id,
    //                         'status'      => $ref->status,
    //                         'hospital'    => $ref->hospital,
    //                         'created_at'  => $ref->created_at,
    //                     ];
    //                 })->values(),
    //                 'has_pending'     => $group->contains('status', 'Pending'),
    //                 'latest_activity' => $group->max('created_at'),
    //                 'history_id'      => $historyId,
    //             ];
    //         })
    //         ->sort(function ($a, $b) {
    //             if ($a['has_pending'] !== $b['has_pending']) {
    //                 return $b['has_pending'] <=> $a['has_pending'];
    //             }
    //             return $b['latest_activity'] <=> $a['latest_activity'];
    //         })
    //         ->values();

    //     // -----------------------------
    //     // FINAL MERGE
    //     // -----------------------------
    //     $finalData = $referrals
    //         ->concat($virtualReferrals)
    //         ->concat($boardedOutVirtuals)
    //         ->sort(function ($a, $b) {
    //             if ($a['has_pending'] !== $b['has_pending']) {
    //                 return $b['has_pending'] <=> $a['has_pending'];
    //             }
    //             return $b['latest_activity'] <=> $a['latest_activity'];
    //         })
    //         ->values();

    //     return response(['data' => $finalData, 'statusCode' => 200], 200);
    // }
    public function index()
    {
        $user = auth()->user();

        $dataEntryEmails = [
            'medicalboard@mohz.go.tz',
            'hospital@mohz.go.tz',
            'mkurugenzi@mohz.go.tz',
            'dguser@mohz.go.tz'
        ];

        if (!$user->can('View Referral')) {
            return response(['message' => 'Forbidden', 'statusCode' => 403], 403);
        }

        $isDataEntryUser = in_array($user->email, $dataEntryEmails);

        // -----------------------------
        // REAL REFERRALS
        // -----------------------------
        $query = Referral::with(['patient', 'reason', 'hospital', 'diagnoses'])
            ->where('status', '<>', 'Requested');

        if ($isDataEntryUser) {
            $query->whereHas('patient.creator', function ($q) use ($dataEntryEmails) {
                $q->whereIn('email', $dataEntryEmails);
            });
        } else {
            $query->whereHas('patient.creator', function ($q) use ($dataEntryEmails) {
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

                $history = PatientHistory::where('patient_id', $first->patient_id ?? null)
                ->latest('created_at')
                ->first();

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
                    'history_id' => $history?->patient_histories_id,
                    'history'    => $history
                        ? $this->formatHistory($history)
                        : null,
                ];
            })
            ->values();

        // -----------------------------
        // VIRTUAL (REQUESTED + APPROVED)
        // -----------------------------
        $noReferralHistories = PatientHistory::with(['patient', 'diagnoses', 'reason'])
            ->whereDoesntHave('referrals')
            ->whereDoesntHave('boardedOutLetters') // prevent duplicates
            ->whereIn('status', ['requested', 'approved'])
            ->whereHas('patient.creator', function ($q) use ($dataEntryEmails, $isDataEntryUser) {
                if ($isDataEntryUser) {
                    $q->whereIn('email', $dataEntryEmails);
                } else {
                    $q->whereNotIn('email', $dataEntryEmails);
                }
            })
            ->latest()
            ->get();

        $virtualReferrals = $noReferralHistories->map(function ($history) {

            return [
                'referral_number' => 'N/A-' . $history->patient_histories_id,

                'patient'   => $history->patient,
                'diagnoses' => $history->diagnoses,
                'reason'    => $history->reason,

                'history' => $this->formatHistory($history),

                'status' => 'Pending',

                'hospitals' => [null],

                'referrals' => [
                    [
                        'referral_id' => null,
                        'status'      => 'Pending',
                        'hospital'    => null,
                        'created_at'  => $history->created_at,
                    ]
                ],

                'has_pending'     => true,
                'latest_activity' => $history->updated_at,

                'is_recommendation_only' => true,
                'history_id' => $history->patient_histories_id,
            ];
        });

        // -----------------------------
        // BOARDED OUT
        // -----------------------------
        $boardedOutHistories = PatientHistory::with(['patient', 'diagnoses', 'reason', 'boardedOutLetters'])
            ->whereHas('boardedOutLetters')
            ->whereHas('patient.creator', function ($q) use ($dataEntryEmails, $isDataEntryUser) {
                if ($isDataEntryUser) {
                    $q->whereIn('email', $dataEntryEmails);
                } else {
                    $q->whereNotIn('email', $dataEntryEmails);
                }
            })
            ->latest()
            ->get();

            $boardedOutVirtuals = $boardedOutHistories->map(function ($history) {

                $boardedOut = $history->boardedOutLetters->last();
            
                $isBoardedOut = !is_null($boardedOut);
            
                return [
                    'referral_number' => $isBoardedOut
                        ? 'BO-' . $history->patient_histories_id
                        : 'NBO-' . $history->patient_histories_id,
            
                    'patient'   => $history->patient,
                    'diagnoses' => $history->diagnoses,
                    'reason'    => $history->reason,
            
                    'history' => $this->formatHistory($history),
            
                    'status' => $isBoardedOut ? 'BoardedOut' : 'Pending',
            
                    'hospitals' => [null],
            
                    'referrals' => [
                        [
                            'referral_id' => null,
                            'status'      => $isBoardedOut ? 'BoardedOut' : 'Pending',
                            'hospital'    => null,
                            'created_at'  => $boardedOut?->created_at ?? $history->created_at,
                        ]
                    ],
            
                    'has_pending' => !$isBoardedOut,
            
                    'latest_activity' => $boardedOut?->created_at
                        ?? $history->updated_at,
            
                    'is_boarded_out' => $isBoardedOut,
            
                    'history_id' => $history->patient_histories_id,
            
                    'boarded_out' => [
                        'receiver' => $boardedOut?->receiver,
                        'reference_number' => $boardedOut?->reference_number,
                        'reference_date' => $boardedOut?->reference_date,
                        'recommendations' => $boardedOut?->recommendations,
                    ]
                ];
            });

        // -----------------------------
        // FINAL MERGE + SORT
        // -----------------------------
        $finalData = collect()
            ->concat($referrals)
            ->concat($virtualReferrals)
            ->concat($boardedOutVirtuals)
            ->sort(function ($a, $b) {
                if ($a['has_pending'] !== $b['has_pending']) {
                    return $b['has_pending'] <=> $a['has_pending'];
                }
                return strtotime($b['latest_activity']) <=> strtotime($a['latest_activity']);
            })
            ->values();

        return response([
            'data' => $finalData,
            'statusCode' => 200
        ], 200);
    }

    private function formatHistory($history)
    {
        if (!$history) return null;

        return [
            'patient_histories_id' => $history->patient_histories_id,
            'case_type' => $history->case_type ?? 'N/A',
            'board_comments' => $history->board_comments ?? 'N/A',
            'status' => $history->status ?? 'unknown',
        ];
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
    // public function show(int $id)
    // {
    //     $user = auth()->user();

    //     if (!$user->can('View Referral')) {
    //         return response()->json([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     // =========================
    //     // 1. TRY NORMAL REFERRAL
    //     // =========================
    //     $referral = Referral::with([
    //         'patient' => function ($query) {
    //             $query->with([
    //                 'geographicalLocation',
    //                 'files',
    //                 'patientList.boardMembers',
    //                 'patientHistories' => function ($q) {
    //                     $q->orderBy('patient_histories_id', 'desc')->with([
    //                         'diagnoses',
    //                         'boardDiagnoses',
    //                         'reason',
    //                         'boardReason',
    //                     ]);
    //                 },
    //             ]);
    //         },
    //         'hospital',
    //         'hospitalLetters',
    //         'referralLetters',
    //         'parent',
    //         'children',
    //         'bills',
    //         'confirmedBy',
    //         'creator',
    //         'diagnoses',
    //     ])
    //     ->where('referral_id', $id)
    //     ->first();

    //     // =========================
    //     // 2. IF NOT FOUND → TRY HISTORY
    //     // =========================
    //     if (!$referral) {

    //         $history = PatientHistory::with([
    //             'patient' => function ($query) {
    //                 $query->with([
    //                     'geographicalLocation',
    //                     'files',
    //                     'patientList.boardMembers',
    //                     'patientHistories' => function ($q) {
    //                         $q->orderBy('patient_histories_id', 'desc')->with([
    //                             'diagnoses',
    //                             'boardDiagnoses',
    //                             'reason',
    //                             'boardReason',
    //                             'boardedOutLetters'
    //                         ]);
    //                     },
    //                 ]);
    //             },
    //             'diagnoses',
    //             'boardDiagnoses',
    //             'reason',
    //             'boardReason',
    //             'boardedOutLetters'
    //         ])
    //         ->where('patient_histories_id', $id)
    //         ->first();

    //         if (!$history) {
    //             return response()->json([
    //                 'message' => 'Referral not found',
    //                 'statusCode' => 404,
    //             ], 404);
    //         }

    //         // =========================
    //         // 🔥 CONVERT HISTORY → REFERRAL FORMAT
    //         // =========================
    //         $referral = new \stdClass();
    //         $hasBoardedOut = $history->boardedOutLetters()->exists();
    //         $referral->is_boarded_out = $hasBoardedOut;
    //         $referral->boarded_out_letter = $history->boardedOutLetters()->latest()->first();

    //         $referral->referral_id = null;
    //         $referral->referral_number = 'N/A-' . $history->patient_histories_id;
    //         $referral->status = $hasBoardedOut ? 'BoardedOut' : 'Pending';
    //         $referral->hospital = null;
    //         $referral->hospitalLetters = [];
    //         $referral->referralLetters = [];
    //         $referral->parent = null;
    //         $referral->children = [];
    //         $referral->bills = [];
    //         $referral->confirmedBy = null;
    //         $referral->creator = null;
    //         $referral->diagnoses = $history->diagnoses;

    //         // attach patient (IMPORTANT)
    //         $referral->patient = $history->patient;

    //         // flag for frontend (optional but useful)
    //         $referral->is_recommendation_only = true;
    //         $referral->history_id = $history->patient_histories_id;
    //     }

    //     // =========================
    //     // 3. AGE CALCULATION
    //     // =========================
    //     $patient = $referral->patient ?? null;

    //     if ($patient && $patient->date_of_birth) {
    //         if (is_numeric($patient->date_of_birth)) {
    //             $patient->age_details = [
    //                 'years'  => 0, 'months' => 0, 'days' => 0,
    //                 'string' => "Invalid Date Data"
    //             ];
    //         } else {
    //             try {
    //                 $dob = \Carbon\Carbon::parse($patient->date_of_birth);
    //                 $now = \Carbon\Carbon::now();
    //                 $diff = $dob->diff($now);

    //                 $patient->age_details = [
    //                     'years'  => $diff->y,
    //                     'months' => $diff->m,
    //                     'days'   => $diff->d,
    //                     'string' => "{$diff->y}y {$diff->m}m {$diff->d}d"
    //                 ];
    //             } catch (\Exception $e) {
    //                 $patient->age_details = ['string' => "Unknown"];
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'data' => $referral,
    //         'statusCode' => 200,
    //     ], 200);
    // }
    public function show(Request $request, int $id)
    {
        $user = auth()->user();

        if (!$user->can('View Referral')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $type = $request->query('type', 'referral');

        /**
         * ===================================
         * LOAD RELATIONSHIPS
         * ===================================
         */
        $relations = [
            'patient' => function ($query) {
                $query->with([
                    'geographicalLocation',
                    'files',
                    'patientList.boardMembers',
                    'patientHistories' => function ($q) {
                        $q->orderBy('patient_histories_id', 'desc')->with([
                            'diagnoses',
                            'boardDiagnoses',
                            'reason',
                            'boardReason',
                            'boardedOutLetters'
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
        ];

        /**
         * ===================================
         * REFERRAL MODE
         * ===================================
         */
        if ($type === 'referral') {

            $referral = Referral::with($relations)
                ->where('referral_id', $id)
                ->first();

            if (!$referral) {
                return response()->json([
                    'message' => 'Referral not found',
                    'statusCode' => 404,
                ], 404);
            }
        }

        /**
         * ===================================
         * HISTORY MODE
         * ===================================
         */
        else if ($type === 'history') {

            $history = PatientHistory::with([
                'patient' => function ($query) {
                    $query->with([
                        'geographicalLocation',
                        'files',
                        'patientList.boardMembers',
                        'patientHistories' => function ($q) {
                            $q->orderBy('patient_histories_id', 'desc')->with([
                                'diagnoses',
                                'boardDiagnoses',
                                'reason',
                                'boardReason',
                                'boardedOutLetters'
                            ]);
                        },
                    ]);
                },
                'diagnoses',
                'boardDiagnoses',
                'reason',
                'boardReason',
                'boardedOutLetters'
            ])
            ->where('patient_histories_id', $id)
            ->first();

            if (!$history) {
                return response()->json([
                    'message' => 'History not found',
                    'statusCode' => 404,
                ], 404);
            }

            $hasBoardedOut = $history->boardedOutLetters()->exists();

            $referral = new \stdClass();

            $referral->is_boarded_out = $hasBoardedOut;
            $referral->boarded_out_letter = $history->boardedOutLetters()->latest()->first();

            $referral->referral_id = null;
            $referral->referral_number = 'N/A-' . $history->patient_histories_id;
            $referral->status = $hasBoardedOut ? 'BoardedOut' : 'Pending';

            $referral->hospital = null;
            $referral->hospitalLetters = [];
            $referral->referralLetters = [];
            $referral->parent = null;
            $referral->children = [];
            $referral->bills = [];
            $referral->confirmedBy = null;
            $referral->creator = null;

            $referral->diagnoses = $history->diagnoses;
            $referral->patient = $history->patient;

            $referral->is_recommendation_only = true;
            $referral->history_id = $history->patient_histories_id;
        }

        else {
            return response()->json([
                'message' => 'Invalid type',
                'statusCode' => 422,
            ], 422);
        }

        /**
         * ===================================
         * AGE CALCULATION
         * ===================================
         */
        $patient = $referral->patient ?? null;

        if ($patient && $patient->date_of_birth) {

            try {

                if (is_numeric($patient->date_of_birth)) {

                    $patient->age_details = [
                        'years' => 0,
                        'months' => 0,
                        'days' => 0,
                        'string' => 'Invalid Date Data'
                    ];

                } else {

                    $dob = \Carbon\Carbon::parse($patient->date_of_birth);
                    $diff = $dob->diff(now());

                    $patient->age_details = [
                        'years'  => $diff->y,
                        'months' => $diff->m,
                        'days'   => $diff->d,
                        'string' => "{$diff->y}y {$diff->m}m {$diff->d}d"
                    ];
                }

            } catch (\Exception $e) {

                $patient->age_details = [
                    'string' => 'Unknown'
                ];
            }
        }

        return response()->json([
            'data' => $referral,
            'statusCode' => 200,
        ]);
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
        if ($patient && $patient->date_of_birth) {
            if (is_numeric($patient->date_of_birth)) {
                $patient->age_details = [
                    'years'  => 0,
                    'months' => 0,
                    'days'   => 0,
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
                    $patient->age_details = [
                        'years'  => 0,
                        'months' => 0,
                        'days'   => 0,
                        'string' => "Unknown"
                    ];
                }
            }
        }

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
