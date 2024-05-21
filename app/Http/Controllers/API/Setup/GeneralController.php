<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use App\Models\AdminHierarchies;
use App\Models\BatchYears;
use DB;

class GeneralController extends Controller
{
    public function get_standard_level_by_facility_level(string $facility_level_id)
    {

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            $standard_levels = DB::table('standard_levels')
                                    ->select('standard_levels.*')
                                    ->where('standard_levels.facility_level_id',$facility_level_id)
                                    ->get();

            $respose =[
                'data' => $standard_levels,
                'statusCode'=> 200
            ];

            return response()->json($respose);

        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    public function get_admin_tempaltes()
    {

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $data = AdminHierarchies::orderBy('admin_hierarchy_id')->get(['admin_hierarchy_id', 'admin_hierarchy_name', 'parent_id','created_by']);

            // Convert data to CSV format
            $csvData = $this->convertToCsv($data);

            // Generate a unique file name
            $fileName = 'adminHierarchies' . '.csv';

            // Save CSV data to a file
            $filePath = storage_path('app/' . $fileName);
            file_put_contents($filePath, $csvData);

            // Send the file through API response
            return response()->download($filePath)->deleteFileAfterSend(true);
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    private function convertToCsv($data)
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_keys($data->first()->toArray())); // Write CSV header

        foreach ($data as $row) {
            fputcsv($handle, $row->toArray()); // Write CSV rows
        }

        rewind($handle);
        $csvData = stream_get_contents($handle);
        fclose($handle);

        return $csvData;
    }


    public function get_working_stations_by_facility_level(string $facility_level_id){

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            $working_station = DB::table('working_stations')
                                    ->join("standard_levels","standard_levels.standard_level_id","=","working_stations.standard_level_id",)
                                    ->select('working_stations.working_station_id', 'working_stations.working_station_name')
                                    ->where('standard_levels.facility_level_id', $facility_level_id)
                                    ->get();

            $respose =[
                'data' => $working_station,
                'statusCode'=> 200
            ];

            return response()->json($respose);

        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }

    }

    public function get_departments_by_working_stationl(string $working_station_id){

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            $working_station = DB::table('working_positions')
                                    ->join("departments","departments.department_id","=","working_positions.department_id",)
                                    ->select('working_positions.working_position_id', 'departments.department_name')
                                    ->where('working_positions.working_station_id', $working_station_id)
                                    ->get();

            $respose =[
                'data' => $working_station,
                'statusCode'=> 200
            ];

            return response()->json($respose);

        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }

    }

    public function get_batch_year(){

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            $batch_year = DB::table('batch_years')->get();

            $respose =[
                'data' => $batch_year,
                'statusCode'=> 200
            ];

            return response()->json($respose);

        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }

    }

    public function delete_position_post($position_post_id){

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            try{
                $check_value = DB::table('employment_positions')
                                    ->where('position_post_id', $position_post_id)
                                    ->get();

                if(sizeof($check_value) == 0)
                {
                    DB::table('position_posts')->where('position_post_id', $position_post_id)->delete();

                    $respose =[
                        'message'=> 'Position Post Deleted Successfuly',
                        'statusCode'=> 201
                    ];

                }else{
                    $respose =[
                        'message'=> 'You Can Not Delete This Position Post Because It Is Already Used',
                        'statusCode'=> 401
                    ];
                }
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
                ];
                return response()->json($errorResponse);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    //this method contain the logic to check if batch year is still valid or not
    public function batch_year_configuration(){

        $current_year = date('Y');

        $batch_year =DB::table('batch_years')->whereNull('deleted_at')->get();

        if(sizeof($batch_year) > 0){

            $current_butch_year = substr($batch_year[0]->batch_year,-4);

            $going_date = date('Y-m-d');

            $pilot_date = $current_year.'-07-01';
            
            if($going_date >= $pilot_date){

                if($pilot_date > $batch_year[0]->updated_at){
                    $new_batch_year = $current_butch_year.'/'.$current_butch_year+1;
                    $this->insert_batch_year($batch_year[0]->batch_year_id, $new_batch_year);
                }
            }
        }

    }

    //function that will be called when new batch year reached
    //it will be called on 'batch_year_configuration' method
    public function insert_batch_year(string $id, string $new_year){

        $BatchYears = BatchYears::find($id);
        $BatchYears->delete();

        $BatchYears =BatchYears::create([ 
            'batch_year' => $new_year
        ]);
    }


    private function getPostCategories($childId)
    {
        $position_posts = DB::table('position_posts')
                        ->join('post_categories', 'post_categories.post_category_id', '=', 'position_posts.post_category_id')
                        ->join('batch_years', 'batch_years.batch_year_id', '=', 'position_posts.batch_year_id')
                        ->select('post_categories.post_category_name', 'position_posts.*')
                        ->where('position_posts.working_position_id', $childId)
                        ->where('position_posts.status', 1)
                        ->whereNull('batch_years.deleted_at')
                        ->whereNull('position_posts.deleted_at')
                        ->get();
    
        $fgetUpdatedPositionPost = [];
        foreach ($position_posts as $parent) {

            $employment_position = DB::table('employment_positions')->where('position_post_id', $parent->position_post_id)->get();
            
            if(sizeof($employment_position) == 0){
                $new_post = $parent->position_number;
            }else if(sizeof($employment_position) > 0 && sizeof($employment_position) <= $parent->position_number){
                $new_post = $parent->position_number - sizeof($employment_position);
            }else{
                $old_position_numbers = DB::table('position_posts')
                                ->select(DB::raw('sum(position_posts.position_number) as total_position_number'))
                                ->where('position_posts.working_position_id', $childId)
                                ->where('position_posts.status', 0)
                                ->get();

                $old_position_number = 0;
                if($old_position_numbers == null){
                    $old_position_number = 0;
                }else{
                    $old_position_number = $old_position_numbers[0]->total_position_number;
                }

                $new_post = $parent->position_number + $old_position_number - sizeof($employment_position);
            }

            array_push($fgetUpdatedPositionPost, array(
                'position_post_id' => $parent->position_post_id,
                'post_category_name' => $parent->post_category_name,
                'position_number' => $new_post
            ));
        }
        

        return $fgetUpdatedPositionPost;
    }

    private function getDepartment($parentId)
    {

        $working_positions = DB::table('working_positions')
                            ->join("departments","departments.department_id","=","working_positions.department_id",)
                            ->select('departments.department_name','working_positions.working_position_id')
                            ->where('working_positions.working_station_id', $parentId)
                            ->whereNull('working_positions.deleted_at')
                            ->whereNull('departments.deleted_at')
                            ->get();
        
        $formattedworkingPositions = [];
        foreach ($working_positions as $parent) {
            $parent->position_posts = $this->getPostCategories($parent->working_position_id);
            $formattedworkingPositions[] = $parent;
        }

        return $formattedworkingPositions;
    }


    public function get_working_stations()
    {
        //
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            $working_positions = DB::table('working_positions')
                                ->join("position_posts","position_posts.working_position_id","=","working_positions.working_position_id",)
                                ->get();

            $workingPositionIds = $working_positions->pluck('working_station_id')->toArray();

            $workingStations = DB::table('working_stations')
                                ->select('working_stations.working_station_id','working_stations.working_station_name')
                                ->whereIn('working_stations.working_station_id', $workingPositionIds)
                                ->whereNull('working_stations.deleted_at')
                                ->get();

            $formattedworkingStations = [];
            foreach ($workingStations as $parent) {
                $parent->working_positions = $this->getDepartment($parent->working_station_id);
                $formattedworkingStations[] = $parent;
            }

            $respose =[
                'data' => $formattedworkingStations,
                'statusCode'=> 200
            ];

            return response()->json($respose);

        }
        else{
                return response()
                    ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    private function getEmployer($parentId)
    {

        $employers = DB::table('employer_term_of_employments')
                    ->join('employer_informations', 'employer_informations.employer_information_id', '=', 'employer_term_of_employments.employer_information_id')
                    ->select('employer_informations.*')
                    ->where('employer_term_of_employments.term_of_employment_id', $parentId)
                    ->get();

        return $employers;
    }


    public function get_term_of_employment()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            $termOfEmployments = DB::table('term_of_employments')
                                ->select('term_of_employments.*')
                                ->get();

            $formattedtermOfEmployments = [];
            foreach ($termOfEmployments as $parent) {
                $parent->employer = $this->getEmployer($parent->term_of_employment_id);
                $formattedtermOfEmployments[] = $parent;
            }

            $respose =[
                'data' => $formattedtermOfEmployments,
                'statusCode'=> 200
            ];

            return response()->json($respose);

        }
        else{
                return response()
                    ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    private function getWorkingStation($parentId)
    {
        $workingStations = DB::table('working_stations')
                    ->join('admin_hierarchies','admin_hierarchies.admin_hierarchy_id','=','working_stations.admin_hierarchy_id')
                    ->select('working_stations.working_station_id', 'working_stations.working_station_name')
                    ->where('working_stations.admin_hierarchy_id', $parentId)
                    ->whereNull('working_stations.deleted_at')
                    ->get();

        return $workingStations;
    }

    private function getChildren($parentId)
    {
        $children = DB::table('admin_hierarchies')
                    ->select('admin_hierarchies.admin_hierarchy_id','admin_hierarchies.admin_hierarchy_name')
                    ->where('parent_id', $parentId)
                    ->whereNull('deleted_at')
                    ->get();

        $childrenHierarchy = [];
        foreach ($children as $parent) {
            $parent->children = $this->getWorkingStation($parent->admin_hierarchy_id);
            $childrenHierarchy[] = $parent;
        }

        return $childrenHierarchy;
    }

    public function ger_working_hirarchy()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            $adminHierarchies = DB::table('admin_hierarchies')
                                ->select('admin_hierarchies.admin_hierarchy_id','admin_hierarchies.admin_hierarchy_name')
                                ->where("parent_id","100001")
                                ->whereNull('deleted_at')
                                ->get();

        // Format parent and children recursively
        $formattedAdminHierarchies = [];
        foreach ($adminHierarchies as $parent) {
            $parent->children = $this->getChildren($parent->admin_hierarchy_id);
            $formattedAdminHierarchies[] = $parent;
        }

            

            $respose =[
                'data' => $formattedAdminHierarchies,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }
}
