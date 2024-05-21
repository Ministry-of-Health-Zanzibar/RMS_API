<?php

namespace App\Http\Controllers\API\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class DashboardController extends Controller
{
    public function head_count($batch_year_id){

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL'))
        {
            try{
                $today = date('Y-m-d');

                $all_employee = DB::table('employment_informations')
                                ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                ->where('personal_employment_statuses.employement_status_id','!=', 7)
                                ->where('employment_informations.status','=', 1)
                                ->count();

                $active_employee = DB::table('employment_informations')
                                ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                ->where('personal_employment_statuses.employement_status_id','=', 1)
                                ->where('employment_informations.status','=', 1)
                                ->count();

                $inactive_employee = DB::table('employment_informations')
                                ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                ->where('personal_employment_statuses.employement_status_id','!=', 1)
                                ->where('employee_term_of_employments.term_of_employment_id','=', 4)
                                ->where('employment_informations.status','=', 1)
                                ->count();
                
                $parmanet_employee = DB::table('employment_informations')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','=', 4)
                                    ->where('employment_informations.status','=', 1)
                                    ->count();

                $contract_employee = DB::table('employment_informations')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','!=', 4)
                                    ->where('employment_informations.status','=', 1)
                                    ->count();

                $retired_employee = DB::table('employment_informations')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','=', 4)
                                    ->where('employment_informations.retirement_date','<', $today)
                                    ->where('employment_informations.status','=', 1)
                                    ->count();

                $response = [
                    'all_employee' => $all_employee,
                    'active_employee' => $active_employee,
                    'inactive_employee' => $inactive_employee,
                    'parmanet_employee' => $parmanet_employee,
                    'contract_employee' => $contract_employee,
                    'retired_employee' => $retired_employee
                ];

                return response()->json($response);

            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 401]);
            }
        }else if(auth()->user()->can('View Dashboard'))
        {
            try{
                $today = date('Y-m-d');

                $get_user_hierarchy = DB::table('user_hierarchies')
                                        ->where('user_id', auth()->user()->id)
                                        ->where('user_hierarchies.status',1)
                                        ->get();

                $working_station_id = $get_user_hierarchy[0]->working_station_id;

                $all_employee = DB::table('employment_informations')
                                ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                ->where('working_positions.working_station_id','=', $working_station_id)
                                ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                ->where('personal_employment_statuses.employement_status_id','!=', 7)
                                ->where('employment_informations.status','=', 1)
                                ->count();

                $active_employee = DB::table('employment_informations')
                                ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                ->where('working_positions.working_station_id','=', $working_station_id)
                                ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                ->where('personal_employment_statuses.employement_status_id','=', 1)
                                ->where('employment_informations.status','=', 1)
                                ->count();

                $inactive_employee = DB::table('employment_informations')
                                ->join('personal_employment_statuses', 'personal_employment_statuses.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                ->where('working_positions.working_station_id','=', $working_station_id)
                                ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                ->where('personal_employment_statuses.employement_status_id','!=', 1)
                                ->where('employee_term_of_employments.term_of_employment_id','=', 4)
                                ->where('employment_informations.status','=', 1)
                                ->count();
                
                $parmanet_employee = DB::table('employment_informations')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->where('working_positions.working_station_id','=', $working_station_id)
                                    ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','=', 4)
                                    ->where('employment_informations.status','=', 1)
                                    ->count();

                $contract_employee = DB::table('employment_informations')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->where('working_positions.working_station_id','=', $working_station_id)
                                    ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','!=', 4)
                                    ->where('employment_informations.status','=', 1)
                                    ->count();

                $retired_employee = DB::table('employment_informations')
                                    ->join('employment_positions', 'employment_positions.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('employee_term_of_employments', 'employee_term_of_employments.employment_information_id', '=', 'employment_informations.employment_information_id')
                                    ->join('position_posts', 'position_posts.position_post_id', '=', 'employment_positions.position_post_id')
                                    ->join('working_positions', 'working_positions.working_position_id', '=', 'position_posts.working_position_id')
                                    ->where('working_positions.working_station_id','=', $working_station_id)
                                    ->where('employment_positions.batch_year_id','=', $batch_year_id)
                                    ->where('employee_term_of_employments.term_of_employment_id','=', 4)
                                    ->where('employment_informations.retirement_date','<', $today)
                                    ->where('employment_informations.status','=', 1)
                                    ->count();

                $response = [
                    'all_employee' => $all_employee,
                    'active_employee' => $active_employee,
                    'inactive_employee' => $inactive_employee,
                    'parmanet_employee' => $parmanet_employee,
                    'contract_employee' => $contract_employee,
                    'retired_employee' => $retired_employee
                ];

                return response()->json($response);

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

    public function get_default_year(){
        $batch_year = DB::table('batch_years')->whereNull('deleted_at')->get();

        return $this->head_count($batch_year[0]->batch_year_id);
    }

    public function get_selected_year($year_id){

        return $this->head_count($year_id);
    }
            
}