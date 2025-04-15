<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function referralReport(int $patientId) {
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
            ->where("patients.patient_id","=", $patientId)
        ->get();


        if ($referrals->isEmpty()) {
            return response([
                "message" => "No data found",
                "statusCode" => 200
                ],200);
        } else {
            return response([
                "data" => $referrals,
                "statusCode" => 200,
            ]);
        }

    }
}
