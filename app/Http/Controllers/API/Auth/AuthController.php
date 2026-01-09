<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Helper;
use App\Http\Resources\UserResource;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Auth;
use Validator;
use DB;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $data = Validator::make($request->all(),[
            'email' => 'required|string|email|max:255',
            'password' => 'required'
        ]);

        if($data->fails()){
            return response()->json($data->errors());
        }

        if(!Auth::attempt($request->only('email','password'))){
            Helper::sendError('Email Or Password is incorrect !!!');
        } else {
            $roles = [];
            $permissions = [];
            $hospitalInfo = null; // default null if not hospital user

            foreach(auth()->user()->roles as $print){
                $rolePermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', 'permissions.id')
                                    ->where('role_has_permissions.role_id',$print->id)->get();

                array_push($roles, [
                    "id" => $print->id,
                    "name" => $print->name,
                ]);

                // all permissions from each role assigned
                foreach($rolePermissions as $perm){
                    array_push($permissions, [
                        "id" => $perm->id,
                        "name" => $perm->name
                    ]);
                }

                // Check if user has HOSPITAL USER role
                if($print->name === 'ROLE HOSPITAL USER'){
                    $hospitalInfo = auth()->user()->hospitals->map(function($hospital){
                        return [
                            'hospital_id'   => $hospital->hospital_id,
                            'hospital_name' => $hospital->hospital_name, // use correct column name from hospitals table
                            'hospital_role' => $hospital->pivot->role,
                        ];
                    });
                }
            }

            $token = auth()->user()->createToken('auth_token')->plainTextToken;
            $data = [
                'user_id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'full_name' => auth()->user()->first_name." ". auth()->user()->middle_name." ". auth()->user()->last_name,
                'login_status' => auth()->user()->login_status,
                'statusCode' => 200,
                'token' => $token,
                'roles' => $roles,
                'permissions' => $permissions,
                'hospital_info' => $hospitalInfo // added hospital info here
            ];

            return response()->json(['data'=>$data]);
        }
    }

    // method for user logout and delete token
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['status'=> 401]);
    }

}
