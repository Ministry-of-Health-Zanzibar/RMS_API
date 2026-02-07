<?php

use App\Http\Controllers\API\Bills\BillController;
use App\Http\Controllers\API\Bills\MonthlyBillController;
use App\Http\Controllers\API\Hospitals\HospitalController;
use App\Http\Controllers\API\Referrals\ReferralController;
use App\Http\Controllers\API\ReferralType\ReferralTypeController;
use App\Http\Controllers\API\ReferralLetters\ReferralLettersController;
use App\Http\Controllers\API\Insurances\InsuranceController;
use App\Http\Controllers\API\Patients\PatientController;
use App\Http\Controllers\API\Report\ReportController;
use App\Http\Controllers\API\Treatments\TreatmentController;
use App\Http\Controllers\API\Patients\PatientListController;
use App\Http\Controllers\API\Patients\MedicalBoadController;
use App\Http\Controllers\API\Patients\PatientHistoryController;
use App\Http\Controllers\API\HospitalLetters\HospitalLetterController;
use App\Http\Controllers\API\Followups\FollowupController;
use App\Http\Controllers\API\BillFiles\BillFileController;
use App\Http\Controllers\API\BillItems\BillItemController;
use App\Http\Controllers\API\BillPayments\BillPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Reasons\ReasonController;
use App\Http\Controllers\API\Payments\PaymentController;
use App\Http\Controllers\API\Setup\DiagnosisController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [App\Http\Controllers\API\Auth\AuthController::class, 'login']);

Route::post('forgot-password', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('reset-forgot-password', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'forgotPasswordReset'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('checkPassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'index'])->name('checkPassword');
    Route::post('changePassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'change_password'])->name('changePassword');
    Route::post('resetPassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'resetPassword'])->name('resetPassword');
    Route::get('logsFunction', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'logs_function'])->name('logsFunction');

    Route::resource('uploadTypes', App\Http\Controllers\API\Setup\UploadTypesController::class);
    Route::resource('locations', App\Http\Controllers\API\Setup\GeographicalLocationsController::class);
    Route::resource('identifications', App\Http\Controllers\API\Setup\IdentificationsController::class);
    Route::resource('countries', App\Http\Controllers\API\Setup\CountriesController::class);

    Route::get('userAccounts/board-members', [App\Http\Controllers\API\User\UsersCotroller::class,'getBoardMembers']);
    Route::resource('userAccounts', App\Http\Controllers\API\User\UsersCotroller::class);
    Route::resource('roles', App\Http\Controllers\API\User\RolesCotroller::class);
    Route::resource('permissions', App\Http\Controllers\API\User\PermissionsCotroller::class);

    // ================================================== RMS RELATED APIs ========================================================= //
    //HOSPITALS
    Route::get('hospitals/reffered-hospitals', [HospitalController::class, 'getReferredHospitals']);
    Route::get('hospitals/internal-referral-hospitals', [HospitalController::class, 'getInternalReferralHospitals']);
    Route::resource('hospitals', HospitalController::class);
    Route::patch('hospitals/unBlock/{hospitalId}', [HospitalController::class, 'unBlockHospital']);

    //REFERRAL TYPE
    Route::resource('referralTypes', ReferralTypeController::class);
    Route::patch('referralTypes/unblock/{referralTypeId}', [ReferralTypeController::class, 'unBlockReferralType']);

    //REFERRAL LETTERS
    Route::resource('referralLetters', ReferralLettersController::class);
    Route::patch('referralLetters/unBlock/{referralLetters_id}', [ReferralLettersController::class, 'unBlockReferralLetter']);

    //REFERRAL LETTERS
    Route::resource('referralLetters', ReferralLettersController::class);
    Route::get('referralLetters/comment/referral/{referralId}', [ReferralLettersController::class, 'getReferralCommentByReferralId']);
    Route::patch('referralLetters/unBlock/{referralLettersId}', [ReferralLettersController::class, 'unBlockHospital']);

    // PATIENTS APIs
    Route::resource('patients', PatientController::class);
    Route::post('patients/update/{id}', [PatientController::class, 'updatePatient']);
    Route::post('patients/storePatientAndHistory', [PatientController::class, 'storePatientAndHistory']);
    Route::get('patientsHistories', [PatientController::class, 'patientsHistories']);
    Route::delete('patients/delete/{id}', [PatientController::class, 'delete']);
    Route::patch('patients/unBlock/{id}', [PatientController::class, 'unBlockPatient']);
    Route::get('patients-withinsurance/{id}', [PatientController::class, 'getAllPatientsWithInsurance']);
    Route::get('patients/for-referral/allowed', [PatientController::class, 'getAllPatients']);
    Route::get('/patients/histories/{id}', [PatientController::class, 'getMedicalHistory']);

    // INSURANCES APIs
    Route::resource('insurances', InsuranceController::class);
    Route::patch('insurances/unBlock/{hospitalId}', [InsuranceController::class, 'unBlockInsuarance']);

    // REFERRAL APIs
    Route::resource('referrals', ReferralController::class);
    Route::get('referralwithbills', [ReferralController::class, 'getReferralwithBills']);
    Route::get('referral/{referral_id}', [ReferralController::class, 'getReferralById']);
    Route::post('referral/action', [ReferralController::class, 'handleAction']);
    Route::post('referrals/confirm-referral-by-id/{referral_id}', [ReferralController::class, 'chooseHospitalAndConfirmReferral']);
    Route::patch('referrals/unBlock/{referralId}', [ReferralController::class, 'unBlockReferral']);
    Route::get('referrals-withbills/{referral_id}', [ReferralController::class, 'getReferralsWithBills']);
    Route::get('referrals-by-hospital/{hospital_id}/{bill_file_id}', [ReferralController::class, 'getReferralsByHospitalId']);
    Route::get('hospital-letters/followup-by-referral-id/{referral_id}', [ReferralController::class, 'getHospitalLettersByReferralId']);

    // RMS REASON APIs
    Route::resource('reasons', ReasonController::class);
    Route::patch('reasons/unBlock/{reasonsId}', [ReasonController::class, 'unBlockReason']);

    // RMS Treatments APIs
    Route::resource('treatments', TreatmentController::class);
    Route::post('treatments/update/{treatmentId}', [TreatmentController::class, 'update']);
    Route::patch('treatments/unBlock/{treatmentId}', [TreatmentController::class, 'unBlockTreatment']);

    // BILLS APIs
    Route::resource('bills', BillController::class);
    Route::post('bills/update/{id}', [BillController::class, 'updateBill']);
    Route::get('bills-by-bill-file/{billFileId}', [BillController::class, 'getBillsByBillFile']);
    Route::get('bills/getPatientBillAndPaymentByBillId/{billId}', [BillController::class, 'getPatientBillAndPaymentByBillId']);
    Route::patch('bills/unBlock/{billId}', [BillController::class, 'unBlockBill']);

    // REPORT APIs
    Route::get('reports/referrals/{patientId}', [ReportController::class, 'referralReport']);
    Route::get('reports/referralsByType', [ReportController::class, 'referralReportByReferralType']);
    Route::get('reports/referralsByReason', [ReportController::class, 'referralsReportByReason']);
    Route::get('reports/referralByHospital', [ReportController::class, 'referralReportByHospital']);
    Route::post('reports/getBillsBetweenDates', [ReportController::class, 'getBillsBetweenDates']);
    Route::post('reports/searchReferralReport', [ReportController::class, 'searchReferralReport']);
    Route::post('reports/searchReferralReport', [ReportController::class, 'searchReferralReport']);
    Route::get('reports/getMonthlyMaleAndFemaleReferralReport', [ReportController::class, 'getMonthlyMaleAndFemaleReferralReport']);
    // Dasboard Counts
    Route::get('/dashboard/totals', [ReportController::class, 'getOverallCounts']);

    // PAYMENT  API
    Route::resource('payments', PaymentController::class);

    // Patient Lis
    Route::resource('patient-lists', MedicalBoadController::class);
    Route::post('patient-lists/update/{id}', [MedicalBoadController::class, 'updatePatientList']);
    Route::patch('patient-lists/unblock/{id}', [PatientListController::class, 'unBlockParentList']);
    Route::get('patient-lists/body-form/{id}', [PatientListController::class, 'getAllPatientsByPatientListId']);
    Route::post('patient-lists/assign-patients/{id}', [MedicalBoadController::class, 'assignPatientsToList']);

    // Hospital Letters
    Route::resource('hospital-letters', HospitalLetterController::class);
    Route::post('hospital-letters/update/{followup_id}', [HospitalLetterController::class,'updateHospitalLetter']);

    // Followups
    Route::resource('followups', FollowupController::class);

    // Bill Files
    Route::resource('bill-files', BillFileController::class);
    Route::post('bill-files/update/{bill_file_id}', [BillFileController::class,'updateBillFile']);
    Route::get('bill-files/bill-files-for-payment/payment', [BillFileController::class,'getBillFilesForPayment']);
    Route::get('bill-files/hospital-bills/hospitals', [BillFileController::class,'getBillFilesGroupByHospitals']);
    Route::get('bill-files/hospitals/{hospital_id}', [BillFileController::class,'getBillFilesByHospitalId']);

    // Bill Items
    Route::resource('bill-items', BillItemController::class);
    Route::get('bill-items/by-bill-id/{bill_id}', [BillItemController::class, 'getBillItemsByBillId']);

    // Bill Payments
    Route::resource('bill-payments', BillPaymentController::class);

    // New Report
    Route::post('reports/range', [ReportController::class, 'rangeReport']);
    Route::get('reports/referrals', [ReportController::class, 'referralStatusReport']);
    Route::get('reports/timely', [ReportController::class, 'timelyReport']);
    Route::get('reports/patients', [ReportController::class, 'patientsReport']);

    // referrals by Gender
    Route::get('reports/referralsByGender', [ReportController::class, 'referralsReportByGendr']);
    Route::get('reports/showEverythingByReferralId/{referral_id}', [ReportController::class, 'showEverythingByReferralId']);

    // Diagnoses
    Route::prefix('diagnoses')->group(function () {
        Route::get('/', [DiagnosisController::class, 'index']);
        Route::post('/', [DiagnosisController::class, 'store']);
        Route::get('{uuid}', [DiagnosisController::class, 'show']);
        Route::put('{uuid}', [DiagnosisController::class, 'update']);
        Route::delete('{uuid}', [DiagnosisController::class, 'destroy']);
        Route::post('/restore/{uuid}', [DiagnosisController::class, 'restore']);
        Route::post('/import', [DiagnosisController::class, 'importExcel']);
    });

    // Patient Histories
    Route::prefix('patient-histories')->group(function () {
        Route::get('/', [PatientHistoryController::class, 'index']);
        Route::get('/{id}', [PatientHistoryController::class, 'show']);
        Route::post('/', [PatientHistoryController::class, 'store']);
        Route::post('/update/{id}', [PatientHistoryController::class, 'update']);
        Route::delete('/{id}', [PatientHistoryController::class, 'destroy']);
        Route::post('/{id}/unblock', [PatientHistoryController::class, 'unblock']);
        Route::post('/update-status/{id}', [PatientHistoryController::class, 'updateStatus']);
        Route::put('/{id}/medical-board', [PatientHistoryController::class, 'updateByMedicalBoard']);
        Route::put('/{id}/mkurugenzi-tiba', [PatientHistoryController::class, 'updateByMkurugenzi']);
        Route::get('/allowed-to-assign/patients', [PatientHistoryController::class, 'getPatientToBeAssignedToMedicalBoard']);
    });

    Route::get('/analytics/referral-trend', [App\Http\Controllers\API\Charts\AnalyticsController::class, 'referralTrend']);
    Route::post('/users/{userId}/assign-hospital', [App\Http\Controllers\API\User\UsersCotroller::class, 'assignHospital']);

    Route::get('patients/autocomplete-matibabu-card', [PatientController::class, 'autocompleteMatibabuCards']);
    // The specific endpoint for Matibabu Card eligibility search
    Route::post('patients/search-eligibility', [PatientController::class, 'searchByMatibabu']);

});
