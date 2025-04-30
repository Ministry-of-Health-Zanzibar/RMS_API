<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Exception;
use Validator;
use DB;

class RolesCotroller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:User Management|Create Role|Create Role|Update Role|Update Role|Delete Role', ['only' => ['index', 'create', 'store', 'update', 'destroy']]);
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get a list of roles",
     *     tags={"roles"},
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
     *                     @OA\Property(property="name", type="string", example="ROLE NATIONAL"),
     *                     @OA\Property(property="guard_name", type="string", example="web"),
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
                $roles = DB::table('roles')
                    ->select('roles.*')
                    ->whereNotIn('name', ['ROLE ADMIN'])
                    ->orderBy('id', 'asc')
                    ->get();

                $respose = [
                    'data' => $roles,
                    'statusCode' => 200
                ];

                return response()->json($respose);
            } catch (Exception $e) {

                $errorResponse = [
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage(),
                    'statusCode' => 500
                ];

                return response()->json($errorResponse);
            }
        } else if (auth()->user()->hasRole('ROLE NATIONAL') || (auth()->user()->can('User Management') && !auth()->user()->hasRole('ROLE ADMIN'))) {
            try {
                $roles = DB::table('roles')
                    ->select('roles.*')
                    ->whereNotIn('name', ['ROLE ADMIN', 'ROLE NATIONAL'])
                    ->orderBy('id', 'asc')
                    ->get();

                $respose = [
                    'data' => $roles,
                    'statusCode' => 200
                ];

                return response()->json($respose);
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
                ->json(['message' => 'Unauthorized', 'status' => 401]);
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
     *     path="/api/roles",
     *     summary="Store a new role",
     *     tags={"roles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="permission_id", type="array", @OA\Items(type="integer"))
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
        $permission = $request->permission_id;
        $rolename = $request->name;

        $check_value = DB::select("SELECT r.name FROM roles r WHERE LOWER(r.name) = LOWER('$rolename')");

        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->hasRole('ROLE ACCOUNTANT') || auth()->user()->can('Create Role')) {
            if (sizeof($check_value) == 0) {
                try {
                    $role = Role::create([
                        'name' => $rolename,
                        'guard_name' => 'web'
                    ]);

                    $role->syncPermissions($permission);

                    $successResponse = [
                        'message' => 'Role With Permission Saved Successfuly',
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
                    'message' => 'Role Name Already Exist',
                    'statusCode' => 400
                ];

                return response()->json($errorResponse);
            }
        } else {
            return response()
                ->json(['message' => 'unAuthenticated', 'statusCode' => 401]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     summary="Get a specific role",
     *     tags={"roles"},
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
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="ROLE ADMIN")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="permission",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="View Dashboard"),
     *                     @OA\Property(property="isSelected", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->hasRole('ROLE ACCOUNTANT') || auth()->user()->can('Update Role')) {
            try {
                $rolepermission = DB::table('role_has_permissions')
                    ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                    ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
                    ->select('permissions.id', 'permissions.name')
                    ->where('role_has_permissions.role_id', $id)
                    ->get();

                $role = DB::table('roles')
                    ->select('roles.id', 'roles.name')
                    ->where('roles.id', $id)
                    ->get();

                $permissions = [];
                foreach ($rolepermission as $item) {
                    array_push($permissions, array(
                        'id' => $item->id,
                        'name' => $item->name,
                        'isSelected' => true,
                    ));
                }

                $successResponse = [
                    'roles' => $role,
                    'permission' => $permissions,
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
                ->json(['message' => 'unAuthenticated', 'statusCode' => 401]);
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
     *     path="/api/roles/{id}",
     *     summary="Update a role",
     *     tags={"roles"},
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
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="permission_id", type="array", @OA\Items(type="integer"))
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
        $data = Validator::make($request->all(), [
            'permission_id' => 'required',
            'name' => 'required'
        ]);

        if ($data->fails()) {
            return response()->json($data->errors());
        }

        $permission = $request->permission_id;
        $rolename = $request->name;

        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->hasRole('ROLE ACCOUNTANT') || auth()->user()->can('Update Role')) {
            try {
                $role = Role::find($id);
                $role->name = $rolename;
                $role->update();

                $role->syncPermissions($permission);

                $successResponse = [
                    'message' => 'Role With Permission Update Successfuly',
                    'statusCode' => 200
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
                ->json(['message' => 'unAuthenticated', 'status' => 401]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Delete a role",
     *     tags={"roles"},
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
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->hasRole('ROLE ACCOUNTANT') || auth()->user()->can('Delete Role')) {
            try {
                $delete = Role::find($id);
                if ($delete != null) {
                    $delete->delete();

                    $successResponse = [
                        'message' => 'Role Deleted Successfuly',
                        'statusCode' => 200
                    ];

                    return response()->json($successResponse);
                }
            } catch (Exception $e) {
                $errorResponse = [
                    'message' => 'Internal Server Error',
                    'statusCode' => 500
                ];

                return response()->json($errorResponse);
            }
        } else {
            return response()
                ->json(['message' => 'unAuthenticated', 'statusCode' => 401]);
        }
    }
}