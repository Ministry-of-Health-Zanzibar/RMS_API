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
    }

    /**
     * @OA\Get(
     *     path="/api/permissions",
     *     summary="Get a list of permissions",
     *     tags={"permissions"},
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
    *                     @OA\Property(property="name", type="string", example="Create Permissioin"),
    *                     @OA\Property(property="isSelected", type="boolean", example="true")
    *                 )
    *             ),
    *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL'))
        {
            try{
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
