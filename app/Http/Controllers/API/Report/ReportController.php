<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function referralReport(int $patientId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL'])) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }


        $referrals = DB::table("referrals")
            ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
            ->join("patients", "patients.patient_id", '=', 'referrals.patient_id')
            ->select(
                "referrals.*",
                "hospitals.*",
                "patients.*",
            )
            ->where("patients.patient_id", "=", $patientId)
            ->get();


        if ($referrals->isEmpty()) {
            return response([
                "message" => "No data found",
                "statusCode" => 200
            ], 200);
        } else {
            return response([
                "data" => $referrals,
                "statusCode" => 200,
            ]);
        }





    }


    /**
     * Report by referral type
     */
    public function referralReportByReferralType()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL'])) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        try {
            $totalReferralsByMAINLANDType = DB::table("referrals")
                ->join("referral_types", "referral_types.referral_type_id", '=', 'referrals.referral_type_id')
                ->whereNull("referrals.deleted_at")
                ->where("referral_types.referral_type_name", "=", "MAINLAND")
                ->count();

            $totalReferralsByABROADType = DB::table("referrals")
                ->join("referral_types", "referral_types.referral_type_id", '=', 'referrals.referral_type_id')
                ->whereNull("referrals.deleted_at")
                ->where("referral_types.referral_type_name", "=", "ABROAD")
                ->count();


            return response([
                'totalReferralsByMAINLANDType' => $totalReferralsByMAINLANDType,
                'totalReferralsByABROADType' => $totalReferralsByABROADType,
            ]);
        } catch (\Throwable $e) {
            return response()
                ->json(['message' => $e->getMessage(), 'statusCode' => 401]);
        }
    }

    /**
     * Report by reason
     */
    public function referralsReportByReason()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL'])) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        try {
            $totalReferralsByKufanyiwaUchunguzi = DB::table("referrals")
                ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
                ->whereNull("referrals.deleted_at")
                ->where("reasons.referral_reason_name", "=", "Kufanyiwa uchunguzi")
                ->count();


            $totalReferralsByKupatiwaMatibabu = DB::table("referrals")
                ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
                ->whereNull("referrals.deleted_at")
                ->where("reasons.referral_reason_name", "=", "Kupatiwa matibabu")
                ->count();


            $totalReferralsByUchunguziNaMatibabuZaidi = DB::table("referrals")
                ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
                ->whereNull("referrals.deleted_at")
                ->where("reasons.referral_reason_name", "=", "Uchunguzi na matibabu zaidi")
                ->count();

            $totalReferralsByUchunguziNaMatibabu = DB::table("referrals")
                ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
                ->whereNull("referrals.deleted_at")
                ->where("reasons.referral_reason_name", "=", "Uchunguzi na matibabu")
                ->count();

            $totalReferralsByParsPlanaVitrotomy = DB::table("referrals")
                ->join("reasons", "reasons.reason_id", '=', 'referrals.reason_id')
                ->whereNull("referrals.deleted_at")
                ->where("reasons.referral_reason_name", "=", "Pars Plana Vitrotomy")
                ->count();

            return response([
                'totalReferralsByKufanyiwaUchunguzi' => $totalReferralsByKufanyiwaUchunguzi,
                'totalReferralsByKupatiwaMatibabu' => $totalReferralsByKupatiwaMatibabu,
                'totalReferralsByUchunguziNaMatibabuZaidi' => $totalReferralsByUchunguziNaMatibabuZaidi,
                'totalReferralsByUchunguziNaMatibabu' => $totalReferralsByUchunguziNaMatibabu,
                'totalReferralsByParsPlanaVitrotomy' => $totalReferralsByParsPlanaVitrotomy,
            ]);
        } catch (\Throwable $e) {
            return response()
                ->json(['message' => $e->getMessage(), 'statusCode' => 401]);
        }
    }
}