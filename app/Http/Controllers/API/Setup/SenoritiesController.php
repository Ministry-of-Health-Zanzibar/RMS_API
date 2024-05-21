<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SenoritiesImport;
use App\Models\Senorities;
use Exception;
use Validator;
use DB;

class SenoritiesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Senority|Create Senority|Update Senority|Update Senority|Delete Senority', ['only' => ['index','create','store','update','destroy']]);

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
            $senorities = DB::table('senorities')
                                ->join('users', 'users.id', '=', 'senorities.created_by')
                                ->select('senorities.*','users.first_name','users.middle_name','users.last_name','users.id')
                                ->get();

            $respose =[
                'data' => $senorities,
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
                $data = Excel::import(new SenoritiesImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Senority Inserted Successfully',
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
        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Senority'))
        {
            $user_id = auth()->user()->id;

            $check_value = DB::select("SELECT senority_name FROM senorities WHERE LOWER(senority_name) = LOWER('$request->senority_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Senority Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

            try{
                $Senorities = Senorities::create([
                    'senority_name' => $request->senority_name,
                    'created_by' => $user_id
                ]);

                $respose =[
                    'message' =>'Senority Inserted Successfully',
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
    public function show(string $senority_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $senorities = DB::table('senorities')
                                ->select('senorities.*')
                                ->where('senorities.senority_id', '=',$senority_id)
                                ->get();

            if (sizeof($senorities) > 0)
            {
                $respose =[
                    'data' => $senorities,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Senority Found','statusCode'=> 400]);
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
    public function update(Request $request, string $senority_id)
    {
        $check_value = DB::select("SELECT senority_name FROM senorities WHERE LOWER(senority_name) = LOWER('$request->senority_name') and senority_id != $senority_id");

        if(sizeof($check_value) != 0)
        {
            $respose =[
                'message' =>'Senority Alraedy Exists',
                'statusCode'=> 400
            ];

            return response()->json($respose);       
        }

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Senority'))
        {
            $user_id = auth()->user()->id;
            try{
                $Senorities = Senorities::find($senority_id);
                $Senorities->senority_name  = $request->senority_name;
                $Senorities->created_by = $user_id;
                $Senorities->update();

                $respose =[
                    'message' =>'Senority Updated Successfully',
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
    public function destroy(string $senority_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Senority'))
        {
            $delete = Senorities::find($senority_id);
            if ($delete != null) {
                $delete->delete();

                $respose =[
                    'message'=> 'Senority Blocked Successfuly',
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
