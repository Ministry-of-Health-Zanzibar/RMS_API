<?php

namespace App\Http\Controllers\API\Charts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get referral trend by date and status.
     */
    // public function referralTrend(Request $request)
    // {
    //     // 🧭 Automatically detect the full date range
    //     $firstReferral = Referral::min('created_at');
    //     $lastReferral = Referral::max('created_at');

    //     $startDate = $request->input('start_date', $firstReferral ?? now()->toDateString());
    //     $endDate = $request->input('end_date', $lastReferral ?? now()->toDateString());

    //     // 🩺 Group referrals by date and diagnosis
    //     $trend = DB::table('referrals')
    //         ->join('diagnosis_referral', 'referrals.referral_id', '=', 'diagnosis_referral.referral_id')
    //         ->join('diagnoses', 'diagnosis_referral.diagnosis_id', '=', 'diagnoses.diagnosis_id')
    //         ->select(
    //             DB::raw('DATE(referrals.created_at) as date'),
    //             'diagnoses.diagnosis_name',
    //             DB::raw('COUNT(referrals.referral_id) as total')
    //         )
    //         ->whereBetween('referrals.created_at', [$startDate, $endDate])
    //         ->groupBy('date', 'diagnoses.diagnosis_name')
    //         ->orderBy('date')
    //         ->get();

    //     // 🗓️ Extract all unique dates and diagnosis names
    //     $dates = $trend->pluck('date')->unique()->values();
    //     $diagnoses = $trend->pluck('diagnosis_name')->unique()->values();

    //     // 🧮 Format data for the chart
    //     $formatted = [];
    //     foreach ($diagnoses as $diagnosis) {
    //         $formatted[$diagnosis] = $dates->map(function ($date) use ($trend, $diagnosis) {
    //             return [
    //                 'date' => $date,
    //                 'total' => $trend
    //                     ->where('diagnosis_name', $diagnosis)
    //                     ->where('date', $date)
    //                     ->sum('total'),
    //             ];
    //         });
    //     }

    //     // 🎯 Limit to top 10 diagnoses (optional)
    //     if ($request->input('limit_top', true)) {
    //         $topDiagnoses = collect($formatted)
    //             ->map(fn($items) => collect($items)->sum('total'))
    //             ->sortDesc()
    //             ->take(10)
    //             ->keys();

    //         $formatted = collect($formatted)
    //             ->filter(fn($v, $k) => $topDiagnoses->contains($k));
    //     }

    //     // ✅ Return clean JSON
    //     return response()->json([
    //         'start_date' => $startDate,
    //         'end_date' => $endDate,
    //         'dates' => $dates,
    //         'data' => $formatted,
    //     ]);
    // }

    public function referralTrend(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('View Referral Dashboard')) {
            return response(['message' => 'Forbidden', 'statusCode' => 403], 403);
        }

        // Automatically detect the full date range
        $firstReferral = Referral::min('created_at');
        $lastReferral = Referral::max('created_at');

        $startDate = $request->input('start_date', $firstReferral ?? now()->subMonth()->toDateString());
        $endDate = $request->input('end_date', $lastReferral ?? now()->toDateString());

        // 🩺 Group referrals by date and diagnosis
        $trend = DB::table('referrals')
        ->join('diagnosis_referral', 'referrals.referral_id', '=', 'diagnosis_referral.referral_id')
        ->join('diagnoses', 'diagnosis_referral.diagnosis_id', '=', 'diagnoses.diagnosis_id')
        ->join('hospitals', 'referrals.hospital_id', '=', 'hospitals.hospital_id')
        ->join('referral_types', 'hospitals.referral_type_id', '=', 'referral_types.referral_type_id')
        ->select(
            DB::raw('DATE(referrals.created_at) as date'),
            'diagnoses.diagnosis_name',
            DB::raw('COUNT(referrals.referral_id) as total')
        )
        // ->where('referral_types.referral_type_name', 'Medical Board')
        ->whereNotIn('referrals.status', ['Pending', 'Cancelled', 'Requested'])
        ->whereNull('referrals.deleted_at')
        ->whereBetween('referrals.created_at', [$startDate, $endDate])
        ->groupBy('date', 'diagnoses.diagnosis_name')
        ->get();

        $dates = $trend->pluck('date')->unique()->sort()->values();
        $groupedByDiagnosis = $trend->groupBy('diagnosis_name');

        // 2. Identify the Top 10 Diagnosis Names
        $topDiagnosisNames = $trend->groupBy('diagnosis_name')
            ->map(function ($group) {
                return $group->sum('total');
            })
            ->sortDesc()
            ->take(10)
            ->keys();

        $formatted = [];
        $othersData = array_fill_keys($dates->toArray(), 0);

        // 3. Separate Top 10 from the rest
        foreach ($groupedByDiagnosis as $name => $records) {
            if ($topDiagnosisNames->contains($name)) {
                // Map the top 10 normally
                $formatted[$name] = $dates->map(function ($date) use ($records) {
                    $match = $records->firstWhere('date', $date);
                    return ['date' => $date, 'total' => $match ? (int)$match->total : 0];
                });
            } else {
                // Add everything else to the "Others" bucket
                foreach ($records as $record) {
                    $othersData[$record->date] += (int)$record->total;
                }
            }
        }

        // 4. Add the "Others" category to the result if it has data
        if (array_sum($othersData) > 0) {
            $formatted['Others'] = $dates->map(function ($date) use ($othersData) {
                return ['date' => $date, 'total' => $othersData[$date]];
            });
        }

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dates' => $dates,
            'data' => (object)$formatted,
            'statusCode' => 200
        ]);
    }

//     public function referralTrend(Request $request)
// {
//     $user = auth()->user();
//     if (!$user->can('View Referral Dashboard')) {
//         return response(['message' => 'Forbidden', 'statusCode' => 403], 403);
//     }

//     // 1. Get the actual date range from the database using full Carbon path
//     $firstReferral = Referral::min('created_at');
//     $lastReferral = Referral::max('created_at');

//     $startDate = $request->input('start_date')
//         ? \Carbon\Carbon::parse($request->input('start_date'))->startOfDay()
//         : ($firstReferral ? \Carbon\Carbon::parse($firstReferral)->startOfDay() : \Carbon\Carbon::now()->subMonth()->startOfDay());

//     $endDate = $request->input('end_date')
//         ? \Carbon\Carbon::parse($request->input('end_date'))->endOfDay()
//         : ($lastReferral ? \Carbon\Carbon::parse($lastReferral)->endOfDay() : \Carbon\Carbon::now()->endOfDay());

//     // 🩺 Fetch actual data
//     $trend = DB::table('referrals')
//         ->join('diagnosis_referral', 'referrals.referral_id', '=', 'diagnosis_referral.referral_id')
//         ->join('diagnoses', 'diagnosis_referral.diagnosis_id', '=', 'diagnoses.diagnosis_id')
//         ->select(
//             DB::raw('DATE(referrals.created_at) as date'),
//             'diagnoses.diagnosis_name',
//             DB::raw('COUNT(referrals.referral_id) as total')
//         )
//         /** * FIX: Remove 'Pending' and 'Requested' from here
//          * so the most recent data appears on the chart.
//          */
//         ->whereNotIn('referrals.status', ['Cancelled'])
//         ->whereNull('referrals.deleted_at')
//         ->whereBetween('referrals.created_at', [$startDate, $endDate])
//         ->groupBy('date', 'diagnoses.diagnosis_name')
//         ->orderBy('date')
//         ->get();

//     // 2. Extract unique dates and diagnoses
//     $dates = $trend->pluck('date')->unique()->values();
//     $allDiagnoses = $trend->pluck('diagnosis_name')->unique();

//     // 3. Format data
//     $groupedByDiagnosis = $trend->groupBy('diagnosis_name');
//     $formatted = [];

//     foreach ($allDiagnoses as $diagnosis) {
//         $items = $groupedByDiagnosis->get($diagnosis);

//         $formatted[$diagnosis] = $dates->map(function ($date) use ($items) {
//             $record = $items ? $items->firstWhere('date', $date) : null;
//             return [
//                 'date' => $date,
//                 'total' => $record ? (int)$record->total : 0,
//             ];
//         });
//     }

//     // 4. Limit to top 10 diagnoses (Compatible with PHP 7.3+)
//     if ($request->input('limit_top', true) && count($formatted) > 0) {
//         $topDiagnoses = collect($formatted)
//             ->map(function ($items) {
//                 return collect($items)->sum('total');
//             })
//             ->sortDesc()
//             ->take(10)
//             ->keys();

//         $formatted = collect($formatted)->only($topDiagnoses);
//     }

//     return response()->json([
//         'start_date' => $startDate->toDateTimeString(),
//         'end_date' => $endDate->toDateTimeString(),
//         'dates' => $dates,
//         'data' => (object)$formatted, // Ensure empty data returns as {} not []
//         'statusCode' => 200
//     ]);
// }

    // public function referralTrend(Request $request)
    // {
    //     $user = auth()->user();

    //     if (!$user->can('View Referral Dashboard')) {
    //         return response([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     // Get full available date range safely
    //     $firstReferral = Referral::min('created_at');
    //     $lastReferral  = Referral::max('created_at');

    //     $startDate = $request->input(
    //         'start_date',
    //         $firstReferral ? date('Y-m-d', strtotime($firstReferral)) : date('Y-m-d', strtotime('-1 month'))
    //     );

    //     $endDate = $request->input(
    //         'end_date',
    //         $lastReferral ? date('Y-m-d', strtotime($lastReferral)) : date('Y-m-d')
    //     );

    //     // ===============================
    //     //  Fetch Trend Data
    //     // ===============================
    //     $trend = DB::table('referrals')
    //         ->join('diagnosis_referral', 'referrals.referral_id', '=', 'diagnosis_referral.referral_id')
    //         ->join('diagnoses', 'diagnosis_referral.diagnosis_id', '=', 'diagnoses.diagnosis_id')
    //         ->select(
    //             DB::raw('DATE(referrals.created_at) as date'),
    //             'diagnoses.diagnosis_name',
    //             DB::raw('COUNT(referrals.referral_id) as total')
    //         )
    //         ->whereNull('referrals.deleted_at') // Soft delete protection
    //         ->whereNotIn('referrals.status', ['Pending', 'Cancelled', 'Requested'])
    //         ->whereBetween('referrals.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
    //         ->groupBy(DB::raw('DATE(referrals.created_at)'), 'diagnoses.diagnosis_name')
    //         ->orderBy('date', 'ASC')
    //         ->get();

    //     // ===============================
    //     //  Prepare Data For Chart
    //     // ===============================

    //     $dates = $trend->pluck('date')->unique()->values();
    //     $diagnoses = $trend->pluck('diagnosis_name')->unique()->values();

    //     $groupedByDiagnosis = $trend->groupBy('diagnosis_name');

    //     $formatted = [];

    //     foreach ($diagnoses as $diagnosis) {

    //         $items = $groupedByDiagnosis->get($diagnosis);

    //         $formatted[$diagnosis] = $dates->map(function ($date) use ($items) {

    //             $record = $items ? $items->firstWhere('date', $date) : null;

    //             return [
    //                 'date'  => $date,
    //                 'total' => $record ? (int) $record->total : 0,
    //             ];
    //         })->values();
    //     }

    //     // ===============================
    //     //  Limit To Top 10 Diagnoses
    //     // ===============================
    //     if ($request->input('limit_top', true)) {

    //         $topDiagnoses = collect($formatted)
    //             ->map(function ($items) {
    //                 return collect($items)->sum('total');
    //             })
    //             ->sortDesc()
    //             ->take(10)
    //             ->keys();

    //         $formatted = collect($formatted)
    //             ->only($topDiagnoses)
    //             ->values();
    //     }

    //     // ===============================
    //     //  Response
    //     // ===============================
    //     return response()->json([
    //         'start_date' => $startDate,
    //         'end_date'   => $endDate,
    //         'dates'      => $dates,
    //         'data'       => $formatted,
    //         'statusCode' => 200
    //     ]);
    // }
}
