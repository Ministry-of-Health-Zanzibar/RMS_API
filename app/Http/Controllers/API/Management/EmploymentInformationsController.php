<?php

namespace App\Http\Controllers\API\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PersonalInformations;
use App\Models\CountryOfOrigins;
use App\Models\PersonalLocations;
use App\Models\EmploymentInformations;
use App\Models\EmploymentPositions;
use App\Models\EmployeeTermOfEmployments;
use App\Models\PersonalEmploymentStatuses;
use App\Models\Experiences;
use App\Models\PositionPosts;
use App\Models\MeritalPersonalInformations;
use App\Models\EmploymentSenorities;
use Exception;
use Validator;
use DB;

class EmploymentInformationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Data Management|Create Employee|Create Employee|Update Employee|Update Employee|Delete Employee', ['only' => ['index','create','store','update','destroy']]);

    }

    public function get_active_employees(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Staff Management'))
        {
            try{
                $active_employee = DB::table('personal_informations')
                                    ->join('employment_informations', 'employment_informations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                    ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('post_categories', 'post_categories.post_category_id', '=', 'position_posts.post_category_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->select('personal_informations.*','employment_informations.payroll', 'employment_informations.employment_date', 'post_categories.post_category_name')
                                    ->where('working_positions.working_station_id','=',$working_station_id)
                                    ->where('personal_employment_statuses.employement_status_id','=', 1)
                                    ->where('employment_informations.status','=', 1)
                                    ->get();

                if(sizeof($active_employee) == 0){
                    $respose =[
                        'data' => 'There Is No Any Empoyee For Now',
                        'statusCode'=> 401
                    ];
                    return response()->json($respose);

                }else
                {
                    $respose =[
                        'data' => $active_employee,
                        'statusCode'=> 200
                    ];
                    return response()->json($respose);

                } 
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' =>500
                ];

                return response()->json($errorResponse);
            }
        }else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function get_all_employees(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Staff Management'))
        {
            try{
                $all_employee = DB::table('personal_informations')
                                    ->join('employment_informations', 'employment_informations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                    ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employement_statuses', 'employement_statuses.employement_status_id', '=', 'personal_employment_statuses.employement_status_id')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('post_categories', 'post_categories.post_category_id', '=', 'position_posts.post_category_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->select('personal_informations.*','employment_informations.payroll', 'employment_informations.employment_date', 'post_categories.post_category_name','employement_statuses.employement_status_name')
                                    ->where('working_positions.working_station_id','=',$working_station_id)
                                    ->where('employment_informations.status','=', 1)
                                    ->get();

                if(sizeof($all_employee) == 0){
                    $respose =[
                        'data' => 'There Is No Any Empoyee For Now',
                        'statusCode'=> 401
                    ];
                    return response()->json($respose);

                }else
                {
                    $respose =[
                        'data' => $all_employee,
                        'statusCode'=> 200
                    ];
                    return response()->json($respose);

                } 
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' =>500
                ];

                return response()->json($errorResponse);
            }
        }else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function get_parmanent_employees(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Staff Management'))
        {
            try{
                $parmanet_employee = DB::table('personal_informations')
                                    ->join('employment_informations', 'employment_informations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('post_categories', 'post_categories.post_category_id', '=', 'position_posts.post_category_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->select('personal_informations.*','employment_informations.payroll', 'employment_informations.employment_date', 'post_categories.post_category_name')
                                    ->where('working_positions.working_station_id','=',$working_station_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','=', 4)
                                    ->where('employment_informations.status','=', 1)
                                    ->get();

                if(sizeof($parmanet_employee) == 0){
                    $respose =[
                        'data' => 'There Is No Any Empoyee For Now',
                        'statusCode'=> 401
                    ];
                    return response()->json($respose);

                }else
                {
                    $respose =[
                        'data' => $parmanet_employee,
                        'statusCode'=> 200
                    ];
                    return response()->json($respose);

                } 
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' =>500
                ];

                return response()->json($errorResponse);
            }
        }else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function get_contract_employees(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Staff Management'))
        {
            try{
                $contract_employee = DB::table('personal_informations')
                                    ->join('employment_informations', 'employment_informations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('post_categories', 'post_categories.post_category_id', '=', 'position_posts.post_category_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->select('personal_informations.*','employment_informations.payroll', 'employment_informations.employment_date', 'post_categories.post_category_name')
                                    ->where('working_positions.working_station_id','=',$working_station_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','!=', 4)
                                    ->where('employment_informations.status','=', 1)
                                    ->get();

                if(sizeof($contract_employee) == 0){
                    $respose =[
                        'data' => 'There Is No Any Empoyee For Now',
                        'statusCode'=> 401
                    ];
                    return response()->json($respose);

                }else
                {
                    $respose =[
                        'data' => $contract_employee,
                        'statusCode'=> 200
                    ];
                    return response()->json($respose);

                } 
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' =>500
                ];

                return response()->json($errorResponse);
            }
        }else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function get_inactive_employees(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Staff Management'))
        {
            try{
                $inactive_employee = DB::table('personal_informations')
                                    ->join('employment_informations', 'employment_informations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                    ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('post_categories', 'post_categories.post_category_id', '=', 'position_posts.post_category_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->select('personal_informations.*','employment_informations.payroll', 'employment_informations.employment_date', 'post_categories.post_category_name')
                                    ->where('working_positions.working_station_id','=',$working_station_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','=', 4)
                                    ->where('personal_employment_statuses.employement_status_id','!=', 1)
                                    ->where('employment_informations.status','=', 1)
                                    ->get();

                if(sizeof($inactive_employee) == 0){
                    $respose =[
                        'data' => 'There Is No Any Empoyee For Now',
                        'statusCode'=> 401
                    ];
                    return response()->json($respose);

                }else
                {
                    $respose =[
                        'data' => $inactive_employee,
                        'statusCode'=> 200
                    ];
                    return response()->json($respose);

                } 
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' =>500
                ];

                return response()->json($errorResponse);
            }
        }else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function get_died_employees(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Staff Management'))
        {
            try{
                $active_employee = DB::table('personal_informations')
                                    ->join('employment_informations', 'employment_informations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                    ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('post_categories', 'post_categories.post_category_id', '=', 'position_posts.post_category_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->select('personal_informations.*','employment_informations.payroll', 'employment_informations.employment_date', 'post_categories.post_category_name')
                                    ->where('working_positions.working_station_id','=',$working_station_id)
                                    ->where('personal_employment_statuses.employement_status_id','=', 7)
                                    ->where('employment_informations.status','=', 1)
                                    ->get();

                if(sizeof($active_employee) == 0){
                    $respose =[
                        'data' => 'There Is No Any Empoyee For Now',
                        'statusCode'=> 401
                    ];
                    return response()->json($respose);

                }else
                {
                    $respose =[
                        'data' => $active_employee,
                        'statusCode'=> 200
                    ];
                    return response()->json($respose);

                } 
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' =>500
                ];

                return response()->json($errorResponse);
            }
        }else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function get_employee_details(string $employee_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Staff Management'))
        {
            try{
                $employee_informations = DB::table('personal_informations')
                                        ->join('merital_personal_informations', 'merital_personal_informations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                        ->join('merital_statuses', 'merital_statuses.merital_status_id', '=', 'merital_personal_informations.merital_status_id')
                                        ->join('country_of_origins', 'country_of_origins.personal_information_id', '=', 'personal_informations.personal_information_id')
                                        ->join('countries', 'countries.country_id', '=', 'country_of_origins.country_id')
                                        ->join('personal_locations', 'personal_locations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                        ->join('geographical_locations', 'geographical_locations.location_id', '=', 'personal_locations.location_id')
                                        ->join('employment_informations', 'employment_informations.personal_information_id', '=', 'personal_informations.personal_information_id')
                                        ->join('employer_informations', 'employer_informations.employer_information_id', '=', 'employment_informations.employer_information_id')
                                        ->join('employment_senorities', 'employment_senorities.employment_information_id', '=', 'employment_informations.employment_information_id')
                                        ->join('senorities', 'senorities.senority_id', '=', 'employment_senorities.senority_id')
                                        ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                        ->join('term_of_employments', 'term_of_employments.term_of_employment_id', '=', 'employee_term_of_employments.term_of_employment_id')
                                        ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                        ->join('employement_statuses', 'employement_statuses.employement_status_id', '=', 'personal_employment_statuses.employement_status_id')
                                        ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                        ->join('caders', 'caders.cader_id', '=', 'employment_positions.cader_id')
                                        ->join('education_levels', 'education_levels.education_level_id', '=', 'employment_positions.education_level_id')
                                        ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                        ->join('post_categories', 'post_categories.post_category_id', '=', 'position_posts.post_category_id')
                                        ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                        ->join('departments', 'departments.department_id', '=', 'working_positions.department_id')
                                        ->join('working_stations', 'working_stations.working_station_id', '=', 'working_positions.working_station_id')
                                        ->select('personal_informations.*','employement_statuses.employement_status_name','working_stations.working_station_name','merital_statuses.merital_name',
                                                'employment_informations.payroll', 'employment_informations.employment_date', 'employment_informations.retirement_date', 'employer_informations.employer_information_name',
                                                'post_categories.post_category_name', 'departments.department_name', 'country_of_origins.country_of_origin_name', 'countries.country_name',
                                                'senorities.senority_name', 'term_of_employments.term_of_employment_name',  'geographical_locations.label', 'education_levels.education_level_name', 'caders.cader_name')
                                        ->where('merital_personal_informations.merital_personal_status','=', 1)
                                        ->where('personal_locations.personal_location_status','=', 1)
                                        ->where('employment_informations.status','=', 1)
                                        ->where('personal_employment_statuses.status','=', 1)
                                        ->where('employment_positions.status','=', 1)
                                        ->where('employment_senorities.employment_senority_status','=', 1)
                                        ->where('personal_informations.personal_information_id','=',$employee_id)
                                        ->get();

                if(sizeof($employee_informations) == 0){
                    $respose =[
                        'data' => 'No Employee Registered',
                        'statusCode'=> 401
                    ];
                    return response()->json($respose);

                }else
                {

                    $educations = DB::table('education_details')
                                ->join('education_levels', 'education_levels.education_level_id', '=', 'education_details.education_level_id')
                                ->join('institutes', 'institutes.institute_id', '=', 'education_details.institute_id')
                                ->join('countries', 'countries.country_id', '=', 'education_details.country_id')
                                ->select('education_details.*', 'education_levels.education_level_name', 'institutes.institute_name', 'countries.country_name')
                                ->where('personal_information_id', '=', $employee_id)
                                ->whereNull('education_details.deleted_at')
                                ->get();

                    $experiences = DB::table('experiences')
                                ->select('experiences.*')
                                ->where('personal_information_id', '=', $employee_id)
                                ->whereNull('experiences.deleted_at')
                                ->get();

                    $skills = DB::table('skills')
                                ->join('personal_skills', 'personal_skills.skill_id', '=', 'skills.skill_id')
                                ->select('personal_skills.*', 'skills.skill_name')
                                ->where('personal_information_id', '=', $employee_id)
                                ->whereNull('personal_skills.deleted_at')
                                ->get();

                    $hobbies = DB::table('hobbies')
                                ->join('personal_hobbies', 'personal_hobbies.hobby_id', '=', 'hobbies.hobby_id')
                                ->select('personal_hobbies.*', 'hobbies.hobby_name')
                                ->where('personal_information_id', '=', $employee_id)
                                ->whereNull('personal_hobbies.deleted_at')
                                ->get();

                    $languages = DB::table('languages')
                                ->join('personal_languages', 'personal_languages.language_id', '=', 'languages.language_id')
                                ->select('personal_languages.*', 'languages.language_name')
                                ->where('personal_information_id', '=', $employee_id)
                                ->whereNull('personal_languages.deleted_at')
                                ->get();


                    $respose =[
                        'data' => $employee_informations,
                        'educations' => $educations,
                        'experiences' => $experiences,
                        'skills' => $skills,
                        'hobbies' => $hobbies,
                        'languages' => $languages,
                        'statusCode'=> 200
                    ];
                    return response()->json($respose);

                } 
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' =>500
                ];

                return response()->json($errorResponse);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function save_employee(Request $request)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Employee'))
        {

            try{

                $isEmployeeExist = DB::select("SELECT * FROM personal_informations WHERE LOWER(email) = LOWER('$request->email') or phone_no = '$request->phone_no'");

                $isPayrollExist = DB::select("SELECT * FROM employment_informations WHERE payroll = '$request->payroll'");

                if(sizeof($isEmployeeExist) > 0 || sizeof($isPayrollExist) > 0){
                    $respose =[
                        'message' =>'Either Payroll, Email or Phone Number Is Taken By Another Employee',
                        'statusCode'=> 401
                    ];
                }else{

                    $auto_id = random_int(10000, 99999).time();
                    $user_id = auth()->user()->id;

                    if ($request->hasFile('image_name')) {
                        $image = $request->file('image_name');
                        $imageName = $auto_id . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path('uploads/profiles'), $imageName);
                    }else{
                        $imageName = null;
                    }

                    $batch_year =DB::table('batch_years')->whereNull('deleted_at')->get();

                    $employer_name = DB::table('employer_informations')->where('employer_information_id', $request->employer_information_id)->get();

                    $experience = DB::table('position_posts')
                                  ->join('post_categories','post_categories.post_category_id','=', 'position_posts.post_category_id')
                                  ->select('post_category_name')
                                  ->where('position_posts.position_post_id', $request->position_post_id)
                                  ->get();

                    if($request->term_of_employment_id == 4){
                        $employment_status = 1;
                        $dateTime = new \DateTime($request->date_of_birth);
                        $dateTime->modify('+60 years');
                        $retired_date = $dateTime->format('Y-m-d');
                    }else{
                        $employment_status = 8;
                        $retired_date = $request->retired_date;
                    }

                    $PersonalInformations = PersonalInformations::create([ 
                        'personal_information_id' =>$auto_id,
                        'first_name' => $request->first_name,
                        'middle_name' => $request->middle_name,
                        'last_name' => $request->last_name,
                        'sur_name' => $request->sur_name,
                        'gender' => $request->gender,
                        'phone_no' => $request->phone_no,
                        'date_of_birth' => $request->date_of_birth,
                        'physical_address' => $request->physical_address,
                        'email' => $request->email,
                        'photo' => $imageName,
                        'created_by' => $user_id
                    ]);

                    $CountryOfOrigins = CountryOfOrigins::create([ 
                        'country_of_origin_name' => $request->country_of_origin_name,
                        'personal_information_id' =>$auto_id,
                        'country_id' => $request->country_id,
                        'created_by' => $user_id
                    ]);

                    $PersonalLocations = PersonalLocations::create([ 
                        'personal_information_id' =>$auto_id,
                        'location_id' => $request->location_id,
                        'personal_location_status' => 1,
                        'created_by' => $user_id
                    ]);

                    $MeritalPersonalInformations = MeritalPersonalInformations::create([ 
                        'personal_information_id' =>$auto_id,
                        'merital_status_id' => $request->merital_status_id,
                        'file_name' => null,
                        'merital_personal_status' => 1,
                        'created_by' => $user_id
                    ]);

                    $EmploymentInformations = EmploymentInformations::create([ 
                        'personal_information_id' =>$auto_id,
                        'payroll' => $request->payroll,
                        'employment_date' => $request->employment_date,
                        'retirement_date' => $retired_date,
                        'employer_information_id' => $request->employer_information_id,
                        'status' => 1,
                        'confirmed_date' => null,
                        'created_by' => $user_id
                    ]);

                    $EmploymentPositions = EmploymentPositions::create([ 
                        'employment_information_id' => $EmploymentInformations->employment_information_id,
                        'cader_id' => $request->cader_id,
                        'position_post_id' => $request->position_post_id,
                        'education_level_id' => $request->education_level_id,
                        'batch_year_id' => $batch_year[0]->batch_year_id,
                        'status' => 1,
                        'created_by' => $user_id
                    ]);

                    $EmploymentSenorities = EmploymentSenorities::create([ 
                        'employment_information_id' => $EmploymentInformations->employment_information_id,
                        'senority_id' => $request->senority_id,
                        'employment_senority_status' => 1,
                        'created_by' => $user_id
                    ]);

                    $EmployeeTermOfEmployments = EmployeeTermOfEmployments::create([ 
                        'employment_information_id' => $EmploymentInformations->employment_information_id,
                        'term_of_employment_id' => $request->term_of_employment_id,
                        'created_by' => $user_id
                    ]);

                    $PersonalEmploymentStatuses = PersonalEmploymentStatuses::create([ 
                        'employment_information_id' => $EmploymentInformations->employment_information_id,
                        'start_date' => $request->employment_date,
                        'end_date' => $retired_date,
                        'status' => 1,
                        'employement_status_id' => $employment_status,
                        'created_by' => $user_id
                    ]);

                    $Experiences = Experiences::create([ 
                        'employer_name' => $employer_name[0]->employer_information_name,
                        'experience_position' => $experience[0]->post_category_name,
                        'start_date' => $request->employment_date,
                        'end_date' => null,
                        'upload_type_id' => null,
                        'upload_file_name' => null,
                        'personal_information_id' => $auto_id,
                        'status' => 1,
                        'created_by' => $user_id
                    ]);

                    //close position post
                    $used_position = EmploymentPositions::where('position_post_id', $request->position_post_id)->get();
                    $position_number = PositionPosts::find($request->position_post_id);
                    if(sizeof($used_position) == $position_number->position_number){
                        $position_number->status = 0;
                        $position_number->update();
                    }

                    $respose =[
                        'message' =>'Employee Saved Successfully',
                        'employee_id' => $auto_id,
                        'statusCode'=> 201
                    ];
                }
        
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 401]);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

}
