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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF'])) {
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF'])) {
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF'])) {
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


    /**
     * Report by hospital
     */
    public function referralReportByHospital()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF'])) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        try {
            $totalReferralsByLumumba = DB::table("referrals")
                ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
                ->whereNull("referrals.deleted_at")
                ->where("hospitals.hospital_name", "=", "LUMUMBA")
                ->count();

            $totalReferralsByMuhimbiliOrthopaedicInstitute = DB::table("referrals")
                ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
                ->whereNull("referrals.deleted_at")
                ->where("hospitals.hospital_name", "=", "Muhimbili Orthopaedic Institute (MOI)")
                ->count();


            $totalReferralsByJakayaKikweteCardiacInstitute = DB::table("referrals")
                ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
                ->whereNull("referrals.deleted_at")
                ->where("hospitals.hospital_name", "=", "Jakaya Kikwete Cardiac Institute (JKCI)")
                ->count();

            $totalReferralsBySIMS = DB::table("referrals")
                ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
                ->whereNull("referrals.deleted_at")
                ->where("hospitals.hospital_name", "=", "SIMS")
                ->count();

            $totalReferralsByMuhimbiliNationalHospital = DB::table("referrals")
                ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
                ->whereNull("referrals.deleted_at")
                ->where("hospitals.hospital_name", "=", "Muhimbili National Hospital (MNH)")
                ->count();

            $totalReferralsByOceanRoadCancerInstitute = DB::table("referrals")
                ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
                ->whereNull("referrals.deleted_at")
                ->where("hospitals.hospital_name", "=", "Ocean Road Cancer Institute (ORCI)")
                ->count();

            $totalReferralsByKilimanjaroChristianMedicalCentre = DB::table("referrals")
                ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
                ->whereNull("referrals.deleted_at")
                ->where("hospitals.hospital_name", "=", "Kilimanjaro Christian Medical Centre (KCMC)")
                ->count();


            $totalReferralsByMadrasInstituteOfOrthopaedicsAndTraumatology = DB::table("referrals")
                ->join("hospitals", "hospitals.hospital_id", '=', 'referrals.hospital_id')
                ->whereNull("referrals.deleted_at")
                ->where("hospitals.hospital_name", "=", "Madras Institute of Orthopaedics and Traumatology (MIOT)	")
                ->count();


            return response([
                'totalReferralsByLumumba' => $totalReferralsByLumumba,
                'totalReferralsByMuhimbiliOrthopaedicInstitute' => $totalReferralsByMuhimbiliOrthopaedicInstitute,
                'totalReferralsByJakayaKikweteCardiacInstitute' => $totalReferralsByJakayaKikweteCardiacInstitute,
                'totalReferralsBySIMS' => $totalReferralsBySIMS,
                'totalReferralsByMuhimbiliNationalHospital' => $totalReferralsByMuhimbiliNationalHospital,
                'totalReferralsByOceanRoadCancerInstitute' => $totalReferralsByOceanRoadCancerInstitute,
                'totalReferralsByKilimanjaroChristianMedicalCentre' => $totalReferralsByKilimanjaroChristianMedicalCentre,
                'totalReferralsByMadrasInstituteOfOrthopaedicsAndTraumatology' => $totalReferralsByMadrasInstituteOfOrthopaedicsAndTraumatology,
            ]);
        } catch (\Throwable $e) {
            return response()
                ->json(['message' => $e->getMessage(), 'statusCode' => 401]);
        }
    }


    // getBillsBetweenDates
    public function getBillsBetweenDates(Request $request)
    {
        $user = auth()->user();
        if (
            !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) ||
            !$user->can('View Patient')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // Fetch bills with joined data
        $bills = DB::table('bills')
            ->join('referrals', 'referrals.referral_id', '=', 'bills.referral_id')
            ->join('patients', 'patients.patient_id', '=', 'referrals.patient_id')
            ->join('hospitals', 'hospitals.hospital_id', '=', 'referrals.hospital_id')
            ->whereBetween('bills.sent_date', [$startDate, $endDate])
            ->select(
                'bills.bill_id',
                'bills.amount',
                'bills.sent_date',
                'bills.bill_status',
                'bills.bill_file',
                'patients.patient_id',
                'patients.name as patient_name',
                'patients.date_of_birth',
                'patients.gender',
                'patients.phone',
                'patients.location',
                'patients.job',
                'patients.position',
                'hospitals.hospital_id',
                'hospitals.hospital_name',
                'hospitals.hospital_code',
                'hospitals.hospital_address',
                'hospitals.contact_number',
                'hospitals.hospital_email'
            )
            ->get();

        // Attach payments to each bill
        $result = $bills->map(function ($bill) {
            $payments = DB::table('payments')
                ->where('bill_id', $bill->bill_id)
                ->select('payment_id', 'amount_paid', 'payment_method', 'created_at as payment_date')
                ->get();

            return [
                'bill_id' => $bill->bill_id,
                'amount' => $bill->amount,
                'sent_date' => $bill->sent_date,
                'bill_status' => $bill->bill_status,
                'bill_file' => $bill->bill_file,
                'patient' => [
                    'patient_id' => $bill->patient_id,
                    'name' => $bill->patient_name,
                    'date_of_birth' => $bill->date_of_birth,
                    'gender' => $bill->gender,
                    'phone' => $bill->phone,
                    'location' => $bill->location,
                    'job' => $bill->job,
                    'position' => $bill->position,
                ],
                'hospital' => [
                    'hospital_id' => $bill->hospital_id,
                    'hospital_name' => $bill->hospital_name,
                    'hospital_code' => $bill->hospital_code,
                    'hospital_address' => $bill->hospital_address,
                    'contact_number' => $bill->contact_number,
                    'hospital_email' => $bill->hospital_email,
                ],
                'payments' => $payments,
            ];
        });

        return response([
            'data' => $result,
            'statusCode' => 200,
        ]);
    }


    // searchReferralReport
    public function searchReferralReport(Request $request)
    {
        $user = auth()->user();

        if (
            !$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) ||
            !$user->can('View Patient')
        ) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $query = DB::table('referrals')
            ->join('patients', 'patients.patient_id', '=', 'referrals.patient_id')
            ->join('hospitals', 'hospitals.hospital_id', '=', 'referrals.hospital_id')
            ->join('referral_types', 'referral_types.referral_type_id', '=', 'referrals.referral_type_id')
            ->join('reasons', 'reasons.reason_id', '=', 'referrals.reason_id')
            ->leftJoin('insurances', 'insurances.patient_id', '=', 'patients.patient_id')
            ->select(
                'referrals.referral_id',
                'referrals.start_date',
                'referrals.end_date',
                'referrals.status as referral_status',
                'patients.patient_id',
                'patients.name as patient_name',
                'hospitals.hospital_name',
                'hospitals.hospital_address',
                'referral_types.referral_type_name',
                'reasons.referral_reason_name',
                'insurances.insurance_provider_name'
            );

        // Optional filters
        if ($request->filled('patient_name')) {
            $query->where('patients.name', 'ILIKE', '%' . $request->patient_name . '%');
        }

        if ($request->filled('hospital_name')) {
            $query->where('hospitals.hospital_name', 'ILIKE', '%' . $request->hospital_name . '%');
        }

        if ($request->filled('hospital_address')) {
            $query->where('hospitals.hospital_address', 'ILIKE', '%' . $request->hospital_address . '%');
        }

        if ($request->filled('referral_type_name')) {
            $query->where('referral_types.referral_type_name', 'ILIKE', '%' . $request->referral_type_name . '%');
        }

        if ($request->filled('referral_reason_name')) {
            $query->where('reasons.referral_reason_name', 'ILIKE', '%' . $request->referral_reason_name . '%');
        }

        if ($request->filled('insurance_provider_name')) {
            $query->where('insurances.insurance_provider_name', 'ILIKE', '%' . $request->insurance_provider_name . '%');
        }

        if ($request->filled('disease_name')) {
            $query->join('patient_diseases', 'patient_diseases.patient_id', '=', 'patients.patient_id')
                ->join('diseases', 'diseases.disease_id', '=', 'patient_diseases.disease_id')
                ->where('diseases.disease_name', 'ILIKE', '%' . $request->disease_name . '%');
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            return response([
                'message' => 'No results found',
                'statusCode' => 404,
            ], 404);
        }

        return response([
            'data' => $results,
            'statusCode' => 200,
        ], 200);
    }

}