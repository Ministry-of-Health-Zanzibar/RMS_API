<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Exception;
use Validator;
use DB;

class PermissionsCotroller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Permission|View Permission', ['only' => ['index','show']]);

        $validate_batch_year = new GeneralController();
        $validate_batch_year->batch_year_configuration();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL'))
        {
            try{
               // $permission =  DB::table('permissions')->select('id','name')->get();

                $permission =  Permission::get(['id','name']);

                $permissions = [];
                foreach($permission as $item){
                    array_push($permissions, array(
                        'id' => $item->id,
                        'name' => $item->name,
                        'isSelected' => false
                    ));
                }


                $respose =[
                    'data' => $permissions,
                    'statusCode'=> 200
                ];

                return response()->json($respose);
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode'=> 500
                ];

                return response()->json($errorResponse);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','status'=> 401]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $user_id = auth()->user()->id;

        $model_has_permissions = DB::table('model_has_roles')
                                    ->join('role_has_permissions','role_has_permissions.role_id','=','model_has_roles.role_id')
                                    ->join('permissions','permissions.id','=','role_has_permissions.permission_id')
                                    ->select('permissions.id','permissions.name')
                                    ->where('model_has_roles.model_id', '=',$user_id)
                                    ->get();
        $respose =[
            'data' => $model_has_permissions,
            'statusCode'=> 200
        ];

        return response()->json($respose);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
