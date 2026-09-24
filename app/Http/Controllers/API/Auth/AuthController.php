<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use Auth;
use Illuminate\Http\Request;
use App\Support\AuditService;
use Spatie\Permission\Models\Permission;
use Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required',
        ]);

        if ($data->fails()) {
            return response()->json($data->errors(), 422);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            AuditService::record(
                'login_failed',
                'authentication',
                null,
                [],
                [],
                'Failed login attempt',
                ['email' => $request->input('email')],
            );
            Helper::sendError('Email Or Password is incorrect !!!');
        } else {
            $authenticatedUser = auth()->user();
            if ($authenticatedUser->is_blocked || $authenticatedUser->trashed()) {
                Auth::logout();
                AuditService::record(
                    'login_blocked',
                    'authentication',
                    $authenticatedUser,
                    [],
                    ['blocked' => true],
                    'Blocked user login attempt',
                    ['user_id' => $authenticatedUser->id],
                );

                return response()->json([
                    'message' => 'This account is blocked. Contact an administrator.',
                    'code' => 'ACCOUNT_BLOCKED',
                    'statusCode' => 403,
                ], 403);
            }

            $roles = [];
            $permissions = [];
            $hospitalInfo = null; // default null if not hospital user

            foreach (auth()->user()->roles as $print) {
                $rolePermissions = Permission::join('role_has_permissions', 'role_has_permissions.permission_id', 'permissions.id')
                    ->where('role_has_permissions.role_id', $print->id)->get();

                array_push($roles, [
                    'id' => $print->id,
                    'name' => $print->name,
                ]);

                // all permissions from each role assigned
                foreach ($rolePermissions as $perm) {
                    array_push($permissions, [
                        'id' => $perm->id,
                        'name' => $perm->name,
                    ]);
                }

                // Check if user has HOSPITAL USER role
                if ($print->name === 'ROLE HOSPITAL USER') {
                    $hospitalInfo = auth()->user()->hospitals->map(function ($hospital) {
                        return [
                            'hospital_id' => $hospital->hospital_id,
                            'hospital_name' => $hospital->hospital_name, // use correct column name from hospitals table
                            'hospital_role' => $hospital->pivot->role,
                        ];
                    });
                }
            }

            $token = auth()->user()->createToken('auth_token')->plainTextToken;
            $data = [
                'user_id' => $authenticatedUser->id,
                'email' => $authenticatedUser->email,
                'full_name' => $authenticatedUser->first_name.' '.$authenticatedUser->middle_name.' '.$authenticatedUser->last_name,
                'login_status' => $authenticatedUser->login_status,
                'statusCode' => 200,
                'token' => $token,
                'roles' => $roles,
                'permissions' => $permissions,
                'hospital_info' => $hospitalInfo, // added hospital info here
            ];

            AuditService::record(
                'login',
                'authentication',
                $authenticatedUser,
                [],
                ['login_status' => $authenticatedUser->login_status],
                'User logged in',
                ['user_id' => $authenticatedUser->id],
            );

            return response()->json(['data' => $data]);
        }
    }

    // method for user logout and delete token
    public function logout()
    {
        $user = auth()->user();
        AuditService::record('logout', 'authentication', $user, [], [], 'User logged out', ['user_id' => $user->id]);
        $user->tokens()->delete();

        return response()->json(['status' => 401]);
    }
}
