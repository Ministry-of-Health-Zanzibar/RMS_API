<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

class TopDiagnosesReport
{
    public function generate(string $start, string $end): array
    {
        $patients = DB::table('diagnoses as d')
            ->join('history_diagnosis as hd', 'hd.diagnosis_id', '=', 'd.diagnosis_id')
            ->join('patient_histories as ph', 'ph.patient_histories_id', '=', 'hd.patient_histories_id')
            ->join('patients as p', 'p.patient_id', '=', 'ph.patient_id')
            ->whereNull('d.deleted_at')->whereNull('ph.deleted_at')->whereNull('p.deleted_at')
            ->where('hd.added_by', 'medical_board')
            ->where('ph.created_at', '>=', $start)->where('ph.created_at', '<', $end)
            ->select('d.diagnosis_name', 'p.patient_id', 'p.name', 'p.gender')
            ->selectRaw('COUNT(DISTINCT ph.patient_histories_id) AS history_count')
            ->groupBy('d.diagnosis_name', 'p.patient_id', 'p.name', 'p.gender');

        $top = DB::query()->fromSub(clone $patients, 'dp')
            ->select('diagnosis_name')->selectRaw('COUNT(*) AS patient_count, SUM(history_count) AS history_count')
            ->groupBy('diagnosis_name')->orderByDesc('patient_count')->orderBy('diagnosis_name')->limit(10)->get();

        if ($top->isEmpty()) {
            return [];
        }

        $details = (clone $patients)->whereIn('d.diagnosis_name', $top->pluck('diagnosis_name'))
            ->orderBy('p.name')->orderBy('p.patient_id')->get();

        $hospitals = DB::table('referrals as r')
            ->join('diagnosis_referral as dr', 'dr.referral_id', '=', 'r.referral_id')
            ->join('diagnoses as d', 'd.diagnosis_id', '=', 'dr.diagnosis_id')
            ->join('hospitals as h', 'h.hospital_id', '=', 'r.hospital_id')
            ->whereNull('r.deleted_at')->whereNull('d.deleted_at')->whereNull('h.deleted_at')
            ->where('r.created_at', '>=', $start)->where('r.created_at', '<', $end)
            ->whereIn('d.diagnosis_name', $top->pluck('diagnosis_name'))
            ->whereIn('r.patient_id', $details->pluck('patient_id')->unique())
            ->select('r.patient_id', 'd.diagnosis_name', 'h.hospital_id', 'h.hospital_name')
            ->distinct()->orderBy('h.hospital_name')->get()
            ->groupBy('diagnosis_name');

        $details = $details->groupBy('diagnosis_name');

        return $top->map(function ($diagnosis) use ($details, $hospitals) {
            $byPatient = $hospitals->get($diagnosis->diagnosis_name, collect())->groupBy('patient_id');

            return [
                'diagnosis_name' => $diagnosis->diagnosis_name,
                'patient_count' => (int) $diagnosis->patient_count,
                'history_count' => (int) $diagnosis->history_count,
                'patients' => $details->get($diagnosis->diagnosis_name)->map(fn ($patient) => [
                    'patient_id' => $patient->patient_id,
                    'name' => $patient->name,
                    'gender' => $patient->gender,
                    'history_count' => (int) $patient->history_count,
                    'referred_hospitals' => $byPatient->get($patient->patient_id, collect())
                        ->map(fn ($hospital) => ['hospital_id' => $hospital->hospital_id, 'hospital_name' => $hospital->hospital_name])->values()->all(),
                ])->values()->all(),
            ];
        })->all();
    }
}
