<?php

namespace App\Http\Controllers\API\User;

use DB;
use Exception;
use Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\API\Setup\GeneralController;

class UsersCotroller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:User Management|Create User|Create User|Update User|Update User|Delete User', ['only' => ['index', 'create', 'store', 'update', 'destroy']]);
    }

    /**
     * @OA\Get(
     *     path="/api/userAccounts",
     *     summary="Get a list of userAccountss",
     *     tags={"userAccounts"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="first_name", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="middle_name", type="string", example="web"),
     *                     @OA\Property(property="last_name", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="email", type="string", example="web"),
     *                     @OA\Property(property="phone_no", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="address", type="string", example="web"),
     *                     @OA\Property(property="gender", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="date_of_birth",type="string",format="date"),
     *                     @OA\Property(property="role_id", type="integer", example=2),
     *                     @OA\Property(property="role_name", type="string", example="web"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function index()
    {
        if (auth()->user()->hasRole('ROLE ADMIN')) {

            try {
                $staffs = DB::table('users')->where('users.id', '!=', 1)->get();

                $response = [
                    'data' => $staffs,
                    'statusCode' => 200
                ];

                return response()->json($response);

            } catch (Exception $e) {
                $errorResponse = [
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage(),
                    'statusCode' => 500
                ];

                return response()->json($errorResponse);
            }

        } else if (auth()->user()->hasRole('ROLE NATIONAL')) {

            try {
                $staffs = DB::table('users')
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.email', 'users.phone_no', 'users.address', 'users.gender', 'users.date_of_birth', 'users.deleted_at', 'roles.name as role_name', 'roles.id as role_id')
                    ->where('model_has_roles.role_id', '!=', 1)
                    ->where('roles.name', '!=', 'ROLE NATIONAL')
                    ->get();

                $response = [
                    'data' => $staffs,
                    'statusCode' => 200
                ];

                return response()->json($response);
            } catch (Exception $e) {
                $errorResponse = [
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage(),
                    'statusCode' => 500
                ];

                return response()->json($errorResponse);
            }

        } else {

            return response()->json([
                'message' => 'Unauthenticated',
                'statusCode' => 401
            ]);

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
     * @OA\Post(
     *     path="/api/userAccounts",
     *     summary="Store a new userAccounts",
     *     tags={"userAccounts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="middle_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="location_id", type="string"),
     *             @OA\Property(property="role_id", type="string"),
     *             @OA\Property(property="phone_no", type="string"),
     *             @OA\Property(property="date_of_birth", type="date"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="gender", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user_id = auth()->user()->id;
        $auto_id = random_int(10000, 99999) . time();
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create User')) {
            $check_value = DB::select("SELECT u.email FROM users u WHERE u.email = '$request->email'");
            if (sizeof($check_value) == 0) {
                try {
                    $users = User::create([
                        'id' => $auto_id,
                        'first_name' => $request->first_name,
                        'middle_name' => $request->middle_name,
                        'last_name' => $request->last_name,
                        'address' => $request->address,
                        'phone_no' => $request->phone_no,
                        'gender' => $request->gender,
                        'date_of_birth' => date('Y-m-d', strtotime($request->date_of_birth)),
                        'email' => $request->email,
                        'password' => Hash::make($auto_id),
                        'login_status' => '0'
                    ]);

                    $users->assignRole($request->role_id);
                    $roleID = $request->role_id;

                    $permissions = DB::table('role_has_permissions')
                        ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                        ->select('permissions.id', 'permissions.name')
                        ->where('role_has_permissions.role_id', '=', $request->roleID)
                        ->get();

                    $users->givePermissionTo($permissions);

                    $successResponse = [
                        'message' => 'User Account Created Successfully',
                        'password' => $auto_id,
                        'email' => $request->email,
                        'statusCode' => 201
                    ];

                    return response()->json($successResponse);
                } catch (Exception $e) {

                    $errorResponse = [
                        'message' => 'Internal Server Error',
                        'error' => $e->getMessage(),
                        'statusCode' => 500
                    ];
                    return response()->json($errorResponse);
                }
            } else {
                $errorResponse = [
                    'message' => 'Email Already Exist',
                    'statusCode' => 400
                ];
                return response()->json($errorResponse);
            }
        } else {
            return response()
                ->json(['message' => 'Unauthorized', 'statusCode' => 401]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/userAccounts/{id}",
     *     summary="Get a specific userAccounts",
     *     tags={"userAccounts"},
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="first_name", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="middle_name", type="string", example="web"),
     *                     @OA\Property(property="last_name", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="email", type="string", example="web"),
     *                     @OA\Property(property="phone_no", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="address", type="string", example="web"),
     *                     @OA\Property(property="gender", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="date_of_birth",type="string",format="date"),
     *                     @OA\Property(property="role_id", type="integer", example=2),
     *                     @OA\Property(property="role_name", type="string", example="web"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete User')) {
            $staffs = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('users.id', 'users.first_name', 'users.middle_name', 'users.last_name', 'users.email', 'users.phone_no', 'users.address', 'users.gender', 'users.date_of_birth', 'users.deleted_at', 'roles.name as role_name', 'roles.id as role_id')
                ->where('model_has_roles.role_id', '!=', 1)
                ->where('users.id', '=', $id)
                ->get();

            $response = [
                'data' => $staffs,
                'statusCode' => 200
            ];
        } else {
            return response()
                ->json(['message' => 'Unauthorized', 'statusCode' => 401]);
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
     * @OA\Put(
     *     path="/api/userAccounts/{id}",
     *     summary="Update a userAccounts",
     *     tags={"userAccounts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="middle_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="location_id", type="string"),
     *             @OA\Property(property="phone_no", type="string"),
     *             @OA\Property(property="date_of_birth", type="date"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="gender", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $user_id = auth()->user()->id;
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update User')) {
            try {

                $users = User::find($id);
                $users->first_name = $request->first_name;
                $users->middle_name = $request->middle_name;
                $users->last_name = $request->last_name;
                $users->address = $request->address;
                $users->gender = $request->gender;
                $users->phone_no = $request->phone_no;
                $users->date_of_birth = $request->date_of_birth;
                $users->update();

                // $users->assignRole($request->roleID);

                $successResponse = [
                    'message' => 'User Account Updated Successfully',
                    'statusCode' => 201
                ];

                return response()->json($successResponse);
            } catch (Exception $e) {
                $errorResponse = [
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage(),
                    'statusCode' => 500
                ];

                return response()->json($errorResponse);
            }
        } else {
            return response()
                ->json(['message' => 'Unauthorized', 'statusCode' => 401]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/userAccounts/{id}",
     *     summary="Delete a userAccounts",
     *     tags={"userAccounts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        //
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete User')) {
            try {
                $delete = User::find($id);
                if ($delete != null) {
                    $delete->delete();

                    $successResponse = [
                        'message' => 'User Account Blocked Successfully',
                        'statusCode' => '200'
                    ];

                    return response()->json($successResponse);
                }
            } catch (Exception $e) {

                $errorResponse = [
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage(),
                    'statusCode' => '500'
                ];

                return response()->json($errorResponse);
            }
        } else {
            return response()
                ->json(['message' => 'Unauthorized', 'statusCode' => 401]);
        }
    }


    // CREATE ACCOUNTANT SUPPORT USER
    /**
     * @OA\Post(
     *     path="/api/users/createAccountantSupportUser",
     *     summary="Create accountant support user",
     *     tags={"users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="middle_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="phone_no", type="string"),
     *             @OA\Property(property="location_id", type="string"),
     *             @OA\Property(property="date_of_birth", type="date"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="gender", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User Account Created Successfully"),
     *             @OA\Property(property="password", type="string", example="KiaSsbbaRCDWzZVo"),
     *             @OA\Property(property="email", type="string", example="emailname@sample.com"),
     *             @OA\Property(property="statusCode", type="integer", example=201),
     *         )
     *     )
     * )
     */
    public function createAccountantSupportUser(Request $request)
    {
        $user_id = auth()->user()->id;
        $user = DB::table('teams')
            ->join('working_teams', 'working_teams.team_id', '=', 'teams.team_id')
            ->join('users', 'working_teams.user_id', '=', 'users.id')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select(
                'teams.team_id',
                'teams.team_name',
            )
            ->where('users.id', '=', $user_id)
            ->first();

        $auto_id = random_int(100000, 999999) . time();
        if (auth()->user()->hasRole('ROLE ACCOUNTANT') || auth()->user()->can('Create Accountant Support User')) {
            $check_value = DB::select("SELECT u.email FROM users u WHERE u.email = '$request->email'");
            $randomPassword = str()->random();
            if (sizeof($check_value) == 0) {
                try {
                    $role = DB::table('roles')->where('name', 'ROLE ACCOUNTANT SUPPORT USER')->select('id', 'name')->first(); // Fetches one result
                    if (!$role) {
                        return response()->json(['message' => 'ROLE ACCOUNTANT SUPPORT USER does not exist', 'statusCode' => 400]);
                    }

                    $check_phone = DB::table('users')->where('phone_no', $request->phone_no)->select('phone_no')->first(); // Fetches one result
                    if ($check_phone) {
                        return response()->json(['message' => 'Phone number already exist', 'statusCode' => 400]);
                    }

                    $users = User::create([
                        'id' => $auto_id,
                        'uuid' => Str::uuid(),
                        'first_name' => $request->first_name,
                        'middle_name' => $request->middle_name,
                        'last_name' => $request->last_name,
                        'phone_no' => $request->phone_no,
                        'gender' => $request->gender,
                        'location_id' => $request->location_id,
                        'date_of_birth' => $request->date_of_birth,
                        'email' => $request->email,
                        'password' => Hash::make($randomPassword),
                        'login_status' => '0',
                        'created_by' => Auth::id(),
                    ]);

                    $roleId = $role->id;
                    $roleName = $role->name;

                    $users->assignRole($roleId);

                    $permissions = DB::table('role_has_permissions')
                        ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                        ->select('permissions.id', 'permissions.name')
                        ->where('role_has_permissions.role_id', '=', $roleId)
                        ->get();

                    $users->givePermissionTo($permissions);

                    // $userCouncils = WorkingTeam::create([
                    //     'uuid' => Str::uuid(),
                    //     'team_id' => $user->team_id,//Team ID of STAFF
                    //     'user_id' =>  $users->id,
                    //     'status' => 1,
                    //     'created_by' => $user_id,
                    // ]);

                    // Mail::to($request->email)->send(new SupportRegisteredMail($request->first_name, $request->email, $randomPassword));

                    return response()->json([
                        'message' => 'User Account Created Successfully',
                        'password' => $randomPassword,
                        'email' => $request->email,
                        'statusCode' => 201,
                    ]);
                } catch (Exception $e) {

                    return response()->json([
                        'message' => 'Internal Server Error',
                        'statusCode' => 500,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Email Already Exist',
                    'statusCode' => 400
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized',
                'statusCode' => 401
            ]);
        }

    }
}