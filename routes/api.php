<?php

use App\Http\Controllers\API\Bills\BillController;
use App\Http\Controllers\API\Hospitals\HospitalController;
use App\Http\Controllers\API\Referrals\ReferralController;
use App\Http\Controllers\API\ReferralType\ReferralTypeController;
use App\Http\Controllers\API\ReferralLetters\ReferralLettersController;
use App\Http\Controllers\API\Insurances\InsuranceController;
use App\Http\Controllers\API\Patients\PatientController;
use App\Http\Controllers\API\Report\ReportController;
use App\Http\Controllers\API\Treatments\TreatmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Reasons\ReasonController;
// use App\Http\Controllers\API\Auth\AuthController;

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
    Route::resource('workStations', App\Http\Controllers\API\Setup\WorkingStationsController::class);
    Route::resource('identifications', App\Http\Controllers\API\Setup\IdentificationsController::class);
    Route::resource('senorities', App\Http\Controllers\API\Setup\SenoritiesController::class);
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
    Route::patch('referralLetters/unBlock/{referralLettersId}', [ReferralLettersController::class, 'unBlockHospital']);

    // PATIENTS APIs
    Route::resource('patients', PatientController::class);
    Route::post('patients/update/{id}', [PatientController::class, 'updatePatient']);
    Route::patch('patients/unBlock/{id}', [PatientController::class, 'unBlockPatient']);
    Route::get('patients-withinsurance/{id}', [PatientController::class, 'getAllPatientsWithInsurance']);


    // INSURANCES APIs
    Route::resource('insurances', InsuranceController::class);
    Route::patch('insurances/unBlock/{hospitalId}', [InsuranceController::class, 'unBlockInsuarance']);

    // REFERRAL APIs
    Route::resource('referrals', ReferralController::class);
    Route::patch('referrals/unBlock/{referralId}', [ReferralController::class, 'unBlockReferral']);
    Route::get('referrals-withbills/{referral_id}', [ReferralController::class, 'getReferralsWithBills']);


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
    Route::patch('bills/unBlock/{billId}', [BillController::class, 'unBlockBill']);


    // REPORT APIs
    Route::get('reports/referrals/{patientId}', [ReportController::class, 'referralReport']);
    Route::get('reports/referralsByType', [ReportController::class, 'referralReportByReferralType']);
    Route::get('reports/referralsByReason', [ReportController::class, 'referralsReportByReason']);
});