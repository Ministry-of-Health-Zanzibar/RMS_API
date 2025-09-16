<?php

use App\Http\Controllers\API\Accountants\AccountantReportController;
use App\Http\Controllers\API\Accountants\CategoryController;
use App\Http\Controllers\API\Accountants\DocumentTypeController;
use App\Http\Controllers\API\Accountants\DocumentTypeFormController;
use App\Http\Controllers\API\Accountants\SouceController;
use App\Http\Controllers\API\Accountants\SourceTypeController;
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
use App\Http\Controllers\API\HospitalLetters\HospitalLetterController;
use App\Http\Controllers\API\Followups\FollowupController;
use App\Http\Controllers\API\BillFiles\BillFileController;
use App\Http\Controllers\API\BillItems\BillItemController;
use App\Http\Controllers\API\BillPayments\BillPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Reasons\ReasonController;
use App\Http\Controllers\API\Payments\PaymentController;

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

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('checkPassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'index'])->name('checkPassword');
    Route::post('changePassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'change_password'])->name('changePassword');
    Route::post('resetPassword', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'reset_password'])->name('resetPassword');
    Route::get('logsFunction', [App\Http\Controllers\API\User\UserProfileCotroller::class, 'logs_function'])->name('logsFunction');

    Route::resource('uploadTypes', App\Http\Controllers\API\Setup\UploadTypesController::class);
    Route::resource('locations', App\Http\Controllers\API\Setup\GeographicalLocationsController::class);
    // Route::resource('workStations', App\Http\Controllers\API\Setup\WorkingStationsController::class);
    Route::resource('identifications', App\Http\Controllers\API\Setup\IdentificationsController::class);
    // Route::resource('senorities', App\Http\Controllers\API\Setup\SenoritiesController::class);
    Route::resource('countries', App\Http\Controllers\API\Setup\CountriesController::class);

    Route::resource('userAccounts', App\Http\Controllers\API\User\UsersCotroller::class);
    Route::resource('roles', App\Http\Controllers\API\User\RolesCotroller::class);
    Route::resource('permissions', App\Http\Controllers\API\User\PermissionsCotroller::class);

    Route::get('getDefaultHeadCount', [App\Http\Controllers\API\Management\DashboardController::class, 'get_default_year'])->name('getDefaultHeadCount');
    Route::get('getSelectedHeadCount/{year}', [App\Http\Controllers\API\Management\DashboardController::class, 'get_selected_year'])->name('getSelectedHeadCount');

    Route::get('unBlockWorkingStations/{stations_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_working_stations'])->name('unBlockWorkingStations');
    Route::get('unBlockSenorities/{senorities_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_senorities'])->name('unBlockSenorities');
    Route::get('unBlockIdentifications/{identifications_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_identifications'])->name('unBlockIdentifications');
    Route::get('unBlockGeographicalLocations/{geographical_locations_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_geographical_locations'])->name('unBlockGeographicalLocations');
    Route::get('unBlockUploadTypes/{upload_types_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_upload_types'])->name('unBlockUploadTypes');
    Route::get('unBlockUser/{user_id}', [App\Http\Controllers\API\Setup\UnBlockCotroller::class, 'unblock_user'])->name('unBlockUser');

    // RMS RELATED APIs
    //HOSPITALS
    Route::get('hospitals/reffered-hospitals', [HospitalController::class, 'getReferredHospitals']);
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
    Route::patch('patients/unBlock/{id}', [PatientController::class, 'unBlockPatient']);
    Route::get('patients-withinsurance/{id}', [PatientController::class, 'getAllPatientsWithInsurance']);
    Route::get('patients/for-referral/allowed', [PatientController::class, 'getAllPatients']);

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
    // Route::get('bill-payment/{bill_id}', [ReferralController::class, 'getReferralsWithBillPayment']);
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

    //  MONTHLY BILL APIs
    Route::resource('monthly-bills', MonthlyBillController::class);
    Route::post('monthly-bills/update/{id}', [MonthlyBillController::class, 'updateMonthlyBill']);
    Route::patch('monthly-bills/unBlock/{monthlyId}', [MonthlyBillController::class, 'unBlockMonthlyBill']);
    Route::get('monthly-bills/by-hospital/{hospitalId}', [MonthlyBillController::class, 'viewBillsByHospitalId']);

    // REPORT APIs
    Route::get('reports/referrals/{patientId}', [ReportController::class, 'referralReport']);
    Route::get('reports/referralsByType', [ReportController::class, 'referralReportByReferralType']);
    Route::get('reports/referralsByReason', [ReportController::class, 'referralsReportByReason']);
    Route::get('reports/referralByHospital', [ReportController::class, 'referralReportByHospital']);
    Route::post('reports/getBillsBetweenDates', [ReportController::class, 'getBillsBetweenDates']);
    Route::post('reports/searchReferralReport', [ReportController::class, 'searchReferralReport']);

    // PAYMENT  API
    Route::resource('payments', PaymentController::class);

    // SOURCE
    Route::resource('sources', SouceController::class);
    Route::patch('sources/unBlock/{sourceId}', [SouceController::class, 'unBlockSource']);


    // SOURCE TYPE
    Route::resource('sourceTypes', SourceTypeController::class);
    Route::get('sourceTypes/source/{sourceName}', [SourceTypeController::class, 'getAllSourceTypesBySourceName']);
    Route::patch('sourceTypes/unBlock/{sourceId}', [SourceTypeController::class, 'unBlockSourceType']);

    // CATEGORY
    Route::resource('categories', CategoryController::class);
    Route::patch('categories/unBlock/{categoryId}', [CategoryController::class, 'unBlockCategory']);

    // DOCUMENT TYPE
    Route::resource('documentTypes', DocumentTypeController::class);
    Route::patch('documentTypes/unBlock/{documentTypeId}', [DocumentTypeController::class, 'unBlockDocumentType']);

    // DOCUMENT FORM
    Route::resource('documentForms', DocumentTypeFormController::class);
    Route::post('documentForms/update/{id}', [DocumentTypeFormController::class, 'updateDocumentForm']);
    Route::patch('documentForms/unBlock/{documentTypeId}', [DocumentTypeFormController::class, 'unBlockDocumentForm']);
    Route::get('accountant/reports/reportPerMonthly', [AccountantReportController::class, 'reportPerMonthly']);
    Route::get('accountant/reports/reportPerWeekly', [AccountantReportController::class, 'reportPerWeekly']);
    Route::get('accountant/reports/reportPerDocumentType', [AccountantReportController::class, 'reportPerDocumentType']);
    Route::get('accountant/reports/reportBySourceType', [AccountantReportController::class, 'reportBySourceType']);
    Route::post('accountant/reports/getDocumentFormReportByDate', [AccountantReportController::class, 'getDocumentFormsReport']);
    Route::post('accountant/reports/searchReportByParameter', [AccountantReportController::class, 'searchReportByParameter']);

    // Patient Lis
    Route::resource('patient-lists', PatientListController::class);
    Route::post('patient-lists/update/{id}', [PatientListController::class, 'updatePatientList']);
    Route::patch('patient-lists/unblock/{id}', [PatientListController::class, 'unBlockParentList']);
    Route::get('patient-lists/body-form/{id}', [PatientListController::class, 'getAllPatientsByPatientListId']);

    // Hospital Letters
    Route::resource('hospital-letters', HospitalLetterController::class);

    // Followups
    Route::resource('followups', FollowupController::class);

    // Bill Files
    Route::resource('bill-files', BillFileController::class);
    Route::get('bill-files/bill-files-for-payment/payment', [BillFileController::class,'getBillFilesForPayment']);
    Route::get('bill-files/hospitals', [BillFileController::class,'getBillFilesGoupByHospitals']);

    // Bill Items
    Route::resource('bill-items', BillItemController::class);
    Route::get('bill-items/by-bill-id/{bill_id}', [BillItemController::class, 'getBillItemsByBillId']);

    // Bill Payments
    Route::resource('bill-payments', BillPaymentController::class);

    // New Report
    Route::post('reports/range', [ReportController::class, 'rangeReport']);
    Route::get('reports/referrals', [ReportController::class, 'referralStatusReport']);
    Route::post('reports/timely', [ReportController::class, 'timelyReport']);
    Route::get('reports/patients', [ReportController::class, 'patientsReport']);

});