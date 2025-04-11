<?php

use App\Http\Controllers\API\Hospitals\HospitalController;
use App\Http\Controllers\API\Insuarances\InsuaranceController;
use App\Http\Controllers\API\Patients\PatientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
    Route::resource('hospitals', HospitalController::class);
    Route::patch('hospitals/unBlock/{hospitalId}', [HospitalController::class, 'unBlockHospital']);

    Route::resource('patients', PatientController::class);
    Route::post('patients/update/{id}', [PatientController::class, 'updatePatient']);
    Route::patch('patients/unBlock/{id}', [PatientController::class, 'unBlockPatient']);

    Route::resource('insuarances', InsuaranceController::class);
    Route::patch('insuarances/unBlock/{hospitalId}', [InsuaranceController::class, 'unBlockHospital']);


});