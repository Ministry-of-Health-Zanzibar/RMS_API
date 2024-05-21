<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WorkingStationsImport;
use App\Models\WorkingStations;
use Exception;
use Validator;
use DB;

class WorkingStationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Work Station|Create Work Station|Update Work Station|Update Work Station|Delete Work Station', ['only' => ['index','create','store','update','destroy']]);

        $validate_batch_year = new GeneralController();
        $validate_batch_year->batch_year_configuration();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $workingStation = DB::table('working_stations')
                                    ->join('users', 'users.id', '=', 'working_stations.created_by')
                                    ->join('geographical_locations','geographical_locations.location_id','=','working_stations.location_id')
                                    ->join("standard_levels","standard_levels.standard_level_id","=","working_stations.standard_level_id",)
                                    ->join("facility_levels","facility_levels.facility_level_id","=","standard_levels.facility_level_id",)
                                    ->join('admin_hierarchies','admin_hierarchies.admin_hierarchy_id','=','working_stations.admin_hierarchy_id')
                                    ->select('working_stations.*',
                                        'geographical_locations.location_id','geographical_locations.location_name',
                                        'admin_hierarchies.admin_hierarchy_id','admin_hierarchies.admin_hierarchy_name',
                                        'standard_levels.standard_level_id','standard_levels.standard_level_name',
                                        'facility_levels.facility_level_id','facility_levels.facility_level_name')
                                    ->get();

            $workingStations = [];
            foreach($workingStation as $item){
                array_push($workingStations, array(
                    'working_station_id' => $item->working_station_id,
                    'working_station_name' => $item->working_station_name,
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'admin_hierarchy_id' => $item->admin_hierarchy_id,
                    'admin_hierarchy_name' => $item->admin_hierarchy_name,
                    'standard_level_id' => $item->standard_level_id,
                    'standard_level_name' => $item->standard_level_name,
                    'facility_level_id' => $item->facility_level_id,
                    'facility_level_name' => $item->facility_level_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $workingStations,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auto_id = random_int(10000, 99999).time();
        if(auth()->user()->hasRole('ROLE ADMIN') && $request->upload_excel){

            // $data = Validator::make($request->all(),[
            //     'upload_excel' => 'mimes:xls,xlsx,csv'
            // ]);

            // if($data->fails()){
            //     return response()->json($data->errors());       
            // }
            
            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new WorkingStationsImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Working Station Inserted Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 401]);
            }
            
        }
        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Work Station'))
        {
            $user_id = auth()->user()->id;
    
            $check_value = DB::select("SELECT working_station_name FROM working_stations WHERE LOWER(working_station_name) = LOWER('$request->working_station_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Working Station Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

            try{
                $WorkingStations = WorkingStations::create([
                    'working_station_name' => $request->working_station_name,
                    'location_id' => $request->location_id,
                    'admin_hierarchy_id' => $request->admin_hierarchy_id,
                    'standard_level_id' => $request->standard_level_id,
                    'created_by' => $user_id
                ]);
        
                $respose =[
                    'message' =>'Working Station Inserted Successfully',
                    'statusCode'=> 201
                ];
        
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

    /**
     * Display the specified resource.
     */
    public function show(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $workingStations = DB::table('working_stations')
                                ->join('users', 'users.id', '=', 'working_stations.created_by')
                                ->join('geographical_locations','geographical_locations.location_id','=','working_stations.location_id')
                                ->join("standard_levels","standard_levels.standard_level_id","=","working_stations.standard_level_id",)
                                ->join("facility_levels","facility_levels.facility_level_id","=","standard_levels.facility_level_id",)
                                ->join('admin_hierarchies','admin_hierarchies.admin_hierarchy_id','=','working_stations.admin_hierarchy_id')
                                ->select('working_stations.*','geographical_locations.location_name','admin_hierarchies.admin_hierarch_name','standard_levels.standard_level_name','facility_levels.facility_level_name')
                                ->where('working_stations.working_station_id', '=',$working_station_id)
                                ->get();

            if (sizeof($workingStations) > 0) 
            {
                $respose =[
                    'data' => $workingStations,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Working Station Found','statusCode'=> 400]);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $working_station_id)
    {
            $check_value = DB::select("SELECT working_station_name FROM working_stations WHERE LOWER(working_station_name) = LOWER('$request->working_station_name') and working_station_id != $working_station_id");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Working Station Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Upload Types'))
        {
            $user_id = auth()->user()->id;
            try{
                $WorkingStations = WorkingStations::find($working_station_id);
                $WorkingStations->working_station_name  = $request->working_station_name;
                $WorkingStations->location_id  = $request->location_id;
                $WorkingStations->admin_hierarchy_id  = $request->admin_hierarchy_id;
                $WorkingStations->standard_level_id  = $request->standard_level_id;
                $WorkingStations->created_by = $user_id;
                $WorkingStations->update();

                $respose =[
                    'message' =>'Working Station Updated Successfully',
                    'statusCode'=> 201
                ];
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Upload Types'))
        {
            $delete = WorkingStations::find($working_station_id);
            if ($delete != null) {
                $delete->delete();
                
                $respose =[
                    'message'=> 'Working Station Blocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }
}
