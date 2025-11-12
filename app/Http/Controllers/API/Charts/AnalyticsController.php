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
    public function referralTrend(Request $request)
    {
        // 🧭 Automatically detect the full date range
        $firstReferral = Referral::min('created_at');
        $lastReferral = Referral::max('created_at');

        $startDate = $request->input('start_date', $firstReferral ?? now()->toDateString());
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
            ->whereBetween('referrals.created_at', [$startDate, $endDate])
            ->groupBy('date', 'diagnoses.diagnosis_name')
            ->orderBy('date')
            ->get();

        // 🗓️ Extract all unique dates and diagnosis names
        $dates = $trend->pluck('date')->unique()->values();
        $diagnoses = $trend->pluck('diagnosis_name')->unique()->values();

        // 🧮 Format data for the chart
        $formatted = [];
        foreach ($diagnoses as $diagnosis) {
            $formatted[$diagnosis] = $dates->map(function ($date) use ($trend, $diagnosis) {
                return [
                    'date' => $date,
                    'total' => $trend
                        ->where('diagnosis_name', $diagnosis)
                        ->where('date', $date)
                        ->sum('total'),
                ];
            });
        }

        // 🎯 Limit to top 10 diagnoses (optional)
        if ($request->input('limit_top', true)) {
            $topDiagnoses = collect($formatted)
                ->map(fn($items) => collect($items)->sum('total'))
                ->sortDesc()
                ->take(10)
                ->keys();

            $formatted = collect($formatted)
                ->filter(fn($v, $k) => $topDiagnoses->contains($k));
        }

        // ✅ Return clean JSON
        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'dates' => $dates,
            'data' => $formatted,
        ]);
    }
}
