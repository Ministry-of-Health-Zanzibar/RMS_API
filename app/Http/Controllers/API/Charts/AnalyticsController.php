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
        ->whereNotIn('referrals.status', ['Pending', 'Cancelled', 'Requested'])
        ->whereNull('referrals.deleted_at')
        ->whereBetween('referrals.created_at', [$startDate, $endDate])
        ->groupBy('date', 'diagnoses.diagnosis_name')
        ->get();

        $dates = $trend->pluck('date')->unique()->sort()->values();
        $groupedByDiagnosis = $trend->groupBy('diagnosis_name');

        // 2. Identify the Top 10 Diagnosis Names
        $showAll = $request->boolean('show_all', false);

        if ($showAll) {
            // If show_all is true, every diagnosis is a "Top" diagnosis
            $topDiagnosisNames = $trend->pluck('diagnosis_name')->unique();
        } else {
            // Otherwise, use your existing Top 10 logic
            $topDiagnosisNames = $trend->groupBy('diagnosis_name')
                ->map(function ($group) { return $group->sum('total'); })
                ->sortDesc()
                ->take(10)
                ->keys();
        }

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

        // 4. Add the "Others" category with a detailed breakdown for tooltips
        if (array_sum($othersData) > 0) {
            $formatted['Others'] = $dates->map(function ($date) use ($groupedByDiagnosis, $topDiagnosisNames) {
                $dayBreakdown = [];
                $dayTotal = 0;

                foreach ($groupedByDiagnosis as $name => $records) {
                    // Only look at diagnoses NOT in the top 10
                    if (!$topDiagnosisNames->contains($name)) {
                        $match = $records->firstWhere('date', $date);
                        if ($match && $match->total > 0) {
                            $dayTotal += (int)$match->total;
                            $dayBreakdown[] = [
                                'name' => $name,
                                'count' => (int)$match->total
                            ];
                        }
                    }
                }

                return [
                    'date' => $date,
                    'total' => $dayTotal,
                    'breakdown' => $dayBreakdown // This is what the frontend uses for the hover tooltip
                ];
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

    public function otherDiagnosesList(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('View Referral Dashboard')) {
            return response(['message' => 'Forbidden', 'statusCode' => 403], 403);
        }

        // 1. Re-detect the date range logic
        $firstReferral = Referral::min('created_at');
        $lastReferral = Referral::max('created_at');

        $startDate = $request->input('start_date', $firstReferral ?? now()->subMonth()->toDateString());
        $endDate = $request->input('end_date', $lastReferral ?? now()->toDateString());

        // 2. Query all diagnosis counts for this range
        $allData = DB::table('referrals')
            ->join('diagnosis_referral', 'referrals.referral_id', '=', 'diagnosis_referral.referral_id')
            ->join('diagnoses', 'diagnosis_referral.diagnosis_id', '=', 'diagnoses.diagnosis_id')
            ->join('hospitals', 'referrals.hospital_id', '=', 'hospitals.hospital_id')
            ->join('referral_types', 'hospitals.referral_type_id', '=', 'referral_types.referral_type_id')
            ->select('diagnoses.diagnosis_name', DB::raw('COUNT(referrals.referral_id) as total'))
            ->whereNotIn('referrals.status', ['Pending', 'Cancelled', 'Requested'])
            ->whereNull('referrals.deleted_at')
            ->whereBetween('referrals.created_at', [$startDate, $endDate])
            ->groupBy('diagnoses.diagnosis_name')
            ->orderBy('total', 'desc')
            ->get();

        // 3. Skip the top 10 (the ones visible on the chart) and take the rest
        $others = $allData->slice(10);

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_others_count' => $others->count(),
            'total_referrals_in_others' => $others->sum('total'),
            'data' => $others->values(), // values() resets the array keys from 10,11,12... to 0,1,2...
            'statusCode' => 200
        ]);
    }

}
