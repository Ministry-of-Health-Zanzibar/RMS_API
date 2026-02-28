<?php

namespace App\Http\Controllers\API\Charts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Referral;
use Illuminate\Support\Facades\DB;

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
            ->select(
                DB::raw('DATE(referrals.created_at) as date'),
                'diagnoses.diagnosis_name',
                DB::raw('COUNT(referrals.referral_id) as total')
            )
            // 1. Consistency: Exclude Pending and Cancelled
            ->whereNotIn('referrals.status', ['Pending', 'Cancelled', 'Requested'])
            // 2. Security: Manually handle soft deletes for DB::table
            ->whereNull('referrals.deleted_at')
            ->whereBetween('referrals.created_at', [$startDate, $endDate])
            ->groupBy('date', 'diagnoses.diagnosis_name')
            ->orderBy('date')
            ->get();

        // Extract all unique dates and diagnosis names
        $dates = $trend->pluck('date')->unique()->values();
        $diagnoses = $trend->pluck('diagnosis_name')->unique()->values();

        // Format data for the chart (Optimized approach)
        $groupedByDiagnosis = $trend->groupBy('diagnosis_name');

        $formatted = [];
        foreach ($diagnoses as $diagnosis) {
            $items = $groupedByDiagnosis->get($diagnosis);

            $formatted[$diagnosis] = $dates->map(function ($date) use ($items) {
                $record = $items ? $items->firstWhere('date', $date) : null;
                return [
                    'date' => $date,
                    'total' => $record ? (int)$record->total : 0,
                ];
            });
        }

        // Limit to top 10 diagnoses
        if ($request->input('limit_top', true)) {
            $topDiagnoses = collect($formatted)
                ->map(function ($items) {
                    return collect($items)->sum('total');
                })
                ->sortDesc()
                ->take(10)
                ->keys();

            $formatted = collect($formatted)
                ->filter(function ($v, $k) use ($topDiagnoses) {
                    return $topDiagnoses->contains($k);
                });
        }

        // Return clean JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dates' => $dates,
            'data' => $formatted,
            'statusCode' => 200
        ]);
    }

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
