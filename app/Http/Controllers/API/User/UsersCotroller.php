<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\UserHierarchies;
use Exception;
use Validator;
use DB;

class UsersCotroller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:User Management|Create User|Create User|Update User|Update User|Delete User', ['only' => ['index','create','store','update','destroy']]);

        $validate_batch_year = new GeneralController();
        $validate_batch_year->batch_year_configuration();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN'))
        {
            try{
                $staffs = DB::table('users')
                            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->join('user_hierarchies', 'user_hierarchies.user_id', '=', 'users.id')
                            ->join('working_stations', 'working_stations.working_station_id', '=', 'user_hierarchies.working_station_id')
                            ->select('users.id','users.first_name','users.middle_name','users.last_name','users.email','users.phone_no','users.address','users.gender','users.date_of_birth','users.deleted_at','roles.name as role_name','roles.id as role_id','working_stations.working_station_id', 'working_stations.working_station_name','user_hierarchies.user_hierarche_id')
                            ->where('model_has_roles.role_id','!=',1)
                            ->get();

                $respose =[
                    'data' => $staffs,
                    'statusCode'=> 200
                ];

                return response()->json($respose);
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' =>500
                ];

                return response()->json($errorResponse);
            }
        }else if(auth()->user()->hasRole('ROLE NATIONAL'))
        {
            try{
                $staffs = DB::table('users')
                            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->join('user_hierarchies', 'user_hierarchies.user_id', '=', 'users.id')
                            ->join('working_stations', 'working_stations.working_station_id', '=', 'user_hierarchies.working_station_id')
                            ->select('users.id','users.first_name','users.middle_name','users.last_name','users.email','users.phone_no','users.address','users.gender','users.date_of_birth','users.deleted_at','roles.name as role_name','roles.id as role_id','working_stations.working_station_id', 'working_stations.working_station_name','user_hierarchies.user_hierarche_id')
                            ->where('model_has_roles.role_id','!=',1)
                            ->where('roles.name','!=','ROLE NATIONAL')
                            ->get();

                $respose =[
                    'data' => $staffs,
                    'statusCode'=> 200
                ];

                return response()->json($respose);
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
        $user_id = auth()->user()->id;
        $auto_id = random_int(10000, 99999).time();
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create User'))
        {
            $check_value = DB::select("SELECT u.email FROM users u WHERE u.email = '$request->email'");

            if(sizeof($check_value) == 0)
            {
                try{
                    $users = User::create([
                        'id' => $auto_id,
                        'first_name' => $request->first_name,
                        'middle_name' => $request->middle_name,
                        'last_name' => $request->last_name,
                        'address' => $request->address,
                        'phone_no' => $request->phone_no,
                        'gender' => $request->gender,
                        'date_of_birth' =>$request->date_of_birth,
                        'email' => $request->email,
                        'password' => Hash::make($auto_id),
                        'login_status'=> '0'
                    ]);
    
                    $users->assignRole($request->roleID);
                    $roleID = $request->roleID;

                    $permissions = DB::table('role_has_permissions')
                                        ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                                        ->select('permissions.id','permissions.name')
                                        ->where('role_has_permissions.role_id','=',$request->roleID)
                                        ->get();

                    $users->givePermissionTo($permissions);

                    $UserHierarchies = UserHierarchies::create([
                        'user_hierarche_id' => $auto_id,
                        'working_station_id' => $request->working_station_id,
                        'user_id' =>  $users->id,
                        'status' => 1,
                        'created_by' => $user_id,
                    ]);
    
                    $successResponse = [
                        'message'=>'User Account Created Successfuly',
                        'password'=>$auto_id,
                        'email'=>$request->email,
                        'statusCode' => 201
                    ];
        
                    return response()->json($successResponse);
                }
                catch (Exception $e){

                    $errorResponse = [
                        'message'=>'Internal Server Error',
                        'error' =>$e->getMessage(),
                        'statusCode' => 500
                    ];
                    return response()->json($errorResponse);
                }
            }else
            {
                $errorResponse = [
                    'message'=>'Email Alread Exist',
                    'statusCode' => 400
                ];
                return response()->json($errorResponse);
            }
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete User'))
        {
            $staffs = DB::table('users')
                            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->join('user_hierarchies', 'user_hierarchies.user_id', '=', 'users.id')
                            ->join('working_stations', 'working_stations.working_station_id', '=', 'user_hierarchies.working_station_id')
                            ->select('users.id','users.first_name','users.middle_name','users.last_name','users.email','users.phone_no','users.address','users.gender','users.date_of_birth','users.deleted_at','roles.name as role_name','roles.id as role_id','working_stations.working_station_id', 'working_stations.working_station_name','user_hierarchies.user_hierarche_id')
                            ->where('model_has_roles.role_id','!=',1)
                            ->where('users.id','=',$id)
                            ->get();

            $respose =[
                'data' => $staffs,
                'statusCode'=> 200
            ];
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
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
    public function update(Request $request, string $id)
    {
        $user_id = auth()->user()->id;
        $user_hierarche_id = $request->user_hierarche_id;
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update User'))
        {
            try{

                $users = User::find($id);
                $users->first_name  = $request->first_name;
                $users->middle_name = $request->middle_name;
                $users->last_name  = $request->last_name;
                $users->address = $request->address;
                $users->gender = $request->gender;
                $users->phone_no  = $request->phone_no;
                $users->date_of_birth  = $request->date_of_birth;
                $users->update();

                // $users->assignRole($request->roleID);

                $UserHierarchies = UserHierarchies::find($user_hierarche_id);
                $UserHierarchies->working_station_id  = $request->working_station_id;
                $UserHierarchies->user_id = $id;
                $UserHierarchies->created_by  = $user_id;
                $UserHierarchies->update();

                $successResponse = [
                    'message'=>'User Account Updated Successfuly',
                    'statusCode' => 201
                ];

                return response()->json($successResponse);
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode' => 500
                ];

                return response()->json($errorResponse);
            }
        }else
        {
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete User'))
        {
            try{
                $delete = User::find($id);
                if ($delete != null) {
                    $delete->delete();

                    $successResponse = [
                        'message'=>'User Account Blocked Successfuly',
                        'statusCode'=>'200'
                    ];

                    return response()->json($successResponse);
                }
            }
            catch (Exception $e){

                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode'=>'500'
                ];

                return response()->json($errorResponse);
            }
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }
}
