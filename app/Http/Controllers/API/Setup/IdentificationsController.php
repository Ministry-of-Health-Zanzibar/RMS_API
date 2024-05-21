<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\IdentificationsImport;
use App\Models\Identifications;
use Exception;
use Validator;
use DB;

class IdentificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Identification|Create Identification|Update Identification|Update Identification|Delete Identification', ['only' => ['index','create','store','update','destroy']]);

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
            $identifications = DB::table('identifications')
                                ->join('users', 'users.id', '=', 'identifications.created_by')
                                ->select('identifications.*','users.first_name','users.middle_name','users.last_name','users.id')
                                ->get();

            $respose =[
                'data' => $identifications,
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
        if((auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL')) && $request->upload_excel){

            // $data = Validator::make($request->all(),[
            //     'upload_excel' => 'mimes:xls,xlsx,csv'
            // ]);

            // if($data->fails()){
            //     return response()->json($data->errors());       
            // }
            
            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new IdentificationsImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Identification Inserted Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->failures(),'statusCode'=> 401]);
            }
            
        }
        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Identification'))
        {
            $user_id = auth()->user()->id;
    
            $check_value = DB::select("SELECT identification_name FROM identifications WHERE LOWER(identification_name) = LOWER('$request->identification_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Identification Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

            try{
                $Identifications = Identifications::create([ 
                    'identification_name' => $request->identification_name,
                    'created_by' => $user_id
                ]);
        
                $respose =[
                    'message' =>'Identification Inserted Successfully',
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
    public function show(string $identification_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $identifications = DB::table('identifications')
                                ->select('identifications.*')
                                ->where('identifications.identification_id', '=',$identification_id)
                                ->get();

            if (sizeof($identifications) > 0) 
            {
                $respose =[
                    'data' => $identifications,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Identification Found','statusCode'=> 400]);
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
    public function update(Request $request, string $identification_id)
    {
        $check_value = DB::select("SELECT identification_name FROM identifications WHERE LOWER(identification_name) = LOWER('$request->identification_name') and identification_id != $identification_id");

        if(sizeof($check_value) != 0)
        {
            $respose =[
                'message' =>'Identification Name Alraedy Exists',
                'statusCode'=> 400
            ];

            return response()->json($respose);       
        }
        
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Identification'))
        {
            $user_id = auth()->user()->id;
            try{
                $Identifications = Identifications::find($identification_id);
                $Identifications->identification_name  = $request->identification_name;
                $Identifications->created_by = $user_id;
                $Identifications->update();

                $respose =[
                    'message' =>'Identification Updated Successfully',
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
    public function destroy(string $identification_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Upload Types'))
        {
            $delete = Identifications::find($identification_id);
            if ($delete != null) {
                $delete->delete();
                
                $respose =[
                    'message'=> 'Identification Blocked Successfuly',
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
