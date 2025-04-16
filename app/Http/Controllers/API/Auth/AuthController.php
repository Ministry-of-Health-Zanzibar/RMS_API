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

        // $user = Auth::attempt($request->only('email','password'));
        if(!Auth::attempt($request->only('email','password'))){

            Helper::sendError('Email Or Password is incorrect !!!');
            // return response()
            //     ->json(['message' => 'Unauthorized','status'=> 401]);
        }
        else{

            $roles = [];
            $permissions = [];


            foreach(auth()->user()->roles as $print){
                $rolePermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', 'permissions.id')->where('role_has_permissions.role_id',$print->id)->get();
                array_push($roles, array(
                    "id" => $print->id,
                    "name" => $print->name,
                ));

                //all permission from each role assigned
                foreach($rolePermissions as $print){
                    array_push($permissions, array(
                        "id" => $print->id,
                        "name" => $print->name
                    ));
                }
            }

             //return new UserResource(auth()->user());
            $token = auth()->user()->createToken('auth_token')->plainTextToken;
            $data = array(
                'user_id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'full_name' => auth()->user()->first_name." ". auth()->user()->middle_name." ". auth()->user()->last_name,
                'login_status' => auth()->user()->login_status,
                'statusCode' => 200,
                'token' => $token,
                'roles' => $roles,
                'permissions' => $permissions
            );

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
