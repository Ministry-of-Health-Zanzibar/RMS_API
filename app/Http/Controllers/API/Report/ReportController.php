<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ReportController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

// BASHBOARD ================================================================================

    /**
     * Report by referral type
     */
    // public function referralReportByReferralType()
    // {
    //     $user = auth()->user();
    //     if (!$user->can('View Referral Dashboard')) {
    //         return response([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     try {
    //         $totalReferralsByMAINLANDType = DB::table("referrals")
    //             ->join("referral_types", "referral_types.referral_type_id", '=', 'referrals.referral_type_id')
    //             ->whereNull("referrals.deleted_at")
    //             ->where("referral_types.referral_type_name", "=", "MAINLAND")
    //             ->count();

    //         $totalReferralsByABROADType = DB::table("referrals")
    //             ->join("referral_types", "referral_types.referral_type_id", '=', 'referrals.referral_type_id')
    //             ->whereNull("referrals.deleted_at")
    //             ->where("referral_types.referral_type_name", "=", "ABROAD")
    //             ->count();


    //         return response([
    //             'totalReferralsByMAINLANDType' => $totalReferralsByMAINLANDType,
    //             'totalReferralsByABROADType' => $totalReferralsByABROADType,
    //         ]);
    //     } catch (\Throwable $e) {
    //         return response()
    //             ->json(['message' => $e->getMessage(), 'statusCode' => 401]);
    //     }
    // }
// DASHBOARD ========================================================================//


// DASHBOARD ========================================================================//

    /**
     * Get total counts for Medical Boards, Patients, and Referrals.
     */
    public function getOverallCounts()
    {
        $user = auth()->user();

        // Optional: Permission check
        if (!$user->can('View Referral Dashboard')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Count totals
        $totalMedicalBoards = \App\Models\PatientList::count();
        $totalPatients = \App\Models\Patient::count();
        $totalReferrals = \App\Models\Referral::count();
        $totalHospitals = \App\Models\Hospital::count();

        return response([
            'data' => [
                'total_medical_boards' => $totalMedicalBoards,
                'total_patients'       => $totalPatients,
                'total_referrals'      => $totalReferrals,
                'total_hospitals'      => $totalHospitals,
            ],
            'statusCode' => 200
        ], 200);
    }

// DASHBOARD ========================================================================//


    /**
     * Report by reason
     */
    public function referralsReportByReason()
    {
        $user = auth()->user();
        if (!$user->can('View Referral Dashboard')) {
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
// DASHBOARD ========================================================================//



// DASHBOARD ========================================================================//

    public function referralsReportByGendr()
    {
        $user = auth()->user();

        if (!$user->can('View Referral Dashboard')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        try {
            // Group by lowercase gender (case-insensitive)
            $referralsByGender = DB::table("referrals")
                ->join("patients", "patients.patient_id", '=', "referrals.patient_id")
                ->whereNull("referrals.deleted_at")
                ->select(
                    DB::raw("LOWER(patients.gender) as gender"),
                    DB::raw("COUNT(referrals.referral_id) as total")
                )
                ->groupBy(DB::raw("LOWER(patients.gender)"))
                ->get();

            // Convert to associative array and normalize keys
            $genderStats = $referralsByGender->pluck('total', 'gender')->toArray();

            // Normalize to Title Case keys (Female, Male)
            $femaleCount = $genderStats['female'] ?? 0;
            $maleCount   = $genderStats['male'] ?? 0;

            return response([
                'Male'   => $maleCount,
                'Female' => $femaleCount,
                'statusCode' => 200,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'statusCode' => 401
            ]);
        }
    }
// DASHBOARD ========================================================================//


// DASHBOARD ========================================================================//

    /**
     * Report by hospital
     */
    public function referralReportByHospital()
    {
        $user = auth()->user();
        if (!$user->can('View Referral Dashboard')) {
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

// DASHBOARD ========================================================================//


// PRINTABLE REPORT ========================================================================//

public function referralReport(int $patientId)
    {
        $user = auth()->user();
        if (!$user->can('View Report')) {
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

    // getBillsBetweenDates
    public function getBillsBetweenDates(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('View Report')) {
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

    public function searchReferralReport(Request $request)
    {
        $user = auth()->user();

        // Permission check
        if (!$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $query = DB::table('referrals')
            ->join('patients', 'patients.patient_id', '=', 'referrals.patient_id')
            ->join('hospitals', 'hospitals.hospital_id', '=', 'referrals.hospital_id')
            ->join('reasons', 'reasons.reason_id', '=', 'referrals.reason_id')
            ->leftJoin('insurances', 'insurances.patient_id', '=', 'patients.patient_id')
            ->select(
                'referrals.referral_id',
                'referrals.created_at',
                'referrals.status as referral_status',
                'patients.patient_id',
                'patients.name as patient_name',
                'hospitals.hospital_name',
                'hospitals.hospital_address',
                'reasons.referral_reason_name',
                'insurances.insurance_provider_name'
            );

        // Filters (Postgres uses ILIKE)
        if ($request->filled('patient_name')) {
            $query->where('patients.name', 'ILIKE', '%' . $request->patient_name . '%');
        }

        if ($request->filled('hospital_name')) {
            $query->where('hospitals.hospital_name', 'ILIKE', '%' . $request->hospital_name . '%');
        }

        if ($request->filled('hospital_address')) {
            $query->where('hospitals.hospital_address', 'ILIKE', '%' . $request->hospital_address . '%');
        }

        if ($request->filled('referral_reason_name')) {
            $query->where('reasons.referral_reason_name', 'ILIKE', '%' . $request->referral_reason_name . '%');
        }

        // Disease filter (joins only when needed)
        if ($request->filled('disease_name')) {
            $query->join('patient_diseases', 'patient_diseases.patient_id', '=', 'patients.patient_id')
                ->join('diseases', 'diseases.disease_id', '=', 'patient_diseases.disease_id')
                ->where('diseases.disease_name', 'ILIKE', '%' . $request->disease_name . '%');
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('referrals.created_at', [$request->start_date, $request->end_date]);
        }

        $results = $query->get();

        return response([
            'data' => $results,
            'statusCode' => 200,
        ], 200);
    }

    public function rangeReport(Request $request)
    {
        // Permission check
        if (!$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Validate request input
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // Query hospital billing/payment report
        $report = DB::table('hospitals')
        ->join('bill_files', 'hospitals.hospital_id', '=', 'bill_files.hospital_id')
        ->join('bills', 'bill_files.bill_file_id', '=', 'bills.bill_file_id')
        ->leftJoin('bill_payments', 'bills.bill_id', '=', 'bill_payments.bill_id')
        ->leftJoin('payments', 'bill_payments.payment_id', '=', 'payments.payment_id')
        ->select(
            'hospitals.hospital_name',
            DB::raw('COUNT(DISTINCT bills.bill_id) as total_bills'),
            DB::raw("SUM(CASE WHEN bills.bill_status = 'Paid' THEN 1 ELSE 0 END) as paid_bills"),
            DB::raw("SUM(CASE WHEN bills.bill_status = 'Pending' THEN 1 ELSE 0 END) as pending_bills"),
            DB::raw('SUM(bills.total_amount) as total_amount'),
            DB::raw("SUM(CASE WHEN bills.bill_status = 'Paid' THEN bills.total_amount ELSE 0 END) as paid_amount"),
            DB::raw("SUM(CASE WHEN bills.bill_status = 'Pending' THEN bills.total_amount ELSE 0 END) as pending_amount")
        )
        ->whereBetween('bills.bill_period_start', [$startDate, $endDate])
        ->groupBy('hospitals.hospital_name')
        ->orderBy('hospitals.hospital_name')
        ->get();

        return response()->json([
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'data'       => $report,
        ]);
    }

    public function referralStatusReport()
    {
        // Permission check
        if (!$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $report = DB::table('referrals')
            ->select(
                DB::raw('COUNT(CASE WHEN status = "Confirmed" THEN 1 END) as confirmed'),
                DB::raw('COUNT(CASE WHEN status = "Cancelled" THEN 1 END) as cancelled'),
                DB::raw('COUNT(CASE WHEN status = "Expired" THEN 1 END) as expired'),
                DB::raw('COUNT(CASE WHEN status = "Closed" THEN 1 END) as closed')
            )
            ->first();

        return response()->json($report);
    }

    public function timelyReport(Request $request)
    {
        // Permission check
        if (!$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $period = $request->input('period'); // daily, weekly, monthly, yearly

        $query = DB::table('bills');

        switch ($period) {
            case 'daily':
                $query->select(
                    DB::raw('DATE(bill_period_start) as period'),
                    DB::raw('COUNT(bill_id) as total_bills'),
                    DB::raw('SUM(total_amount) as total_amount')
                )->groupBy(DB::raw('DATE(bill_period_start)'));
                break;

            case 'weekly':
                $query->select(
                    DB::raw('YEARWEEK(bill_period_start, 1) as period'),
                    DB::raw('COUNT(bill_id) as total_bills'),
                    DB::raw('SUM(total_amount) as total_amount')
                )->groupBy(DB::raw('YEARWEEK(bill_period_start, 1)'));
                break;

            case 'monthly':
                $query->select(
                    DB::raw('DATE_FORMAT(bill_period_start, "%Y-%m") as period'),
                    DB::raw('COUNT(bill_id) as total_bills'),
                    DB::raw('SUM(total_amount) as total_amount')
                )->groupBy(DB::raw('DATE_FORMAT(bill_period_start, "%Y-%m")'));
                break;

            case 'yearly':
                $query->select(
                    DB::raw('YEAR(bill_period_start) as period'),
                    DB::raw('COUNT(bill_id) as total_bills'),
                    DB::raw('SUM(total_amount) as total_amount')
                )->groupBy(DB::raw('YEAR(bill_period_start)'));
                break;
        }

        $report = $query->get();
        return response()->json($report);
    }

    public function patientsReport()
    {
        // Permission check
        if (!$user->can('View Report')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }
        
        $report = DB::table('patients')
            ->select(
                DB::raw('COUNT(CASE WHEN gender = "Male" THEN 1 END) as male'),
                DB::raw('COUNT(CASE WHEN gender = "Female" THEN 1 END) as female')
            )
            ->first();

        return response()->json($report);
    }

// PRINTABLE REPORT ========================================================================//

}
