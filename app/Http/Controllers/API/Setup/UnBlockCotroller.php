<?php

namespace App\Http\Controllers\API\Setup;
use App\Http\Controllers\API\Setup\GeneralController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminHierarchies;
use App\Models\UploadTypes;
use App\Models\User;
use App\Models\Caders;
use App\Models\Designations;
use App\Models\Disabilities;
use App\Models\EducationLevels;
use App\Models\GeographicalLocations;
use App\Models\Identifications;
use App\Models\FacilityLevels;
use Exception;
use Validator;
use DB;

class UnBlockCotroller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        //$this->middleware('permission:Setup Management|Create Upload Types|Create Upload Types|Update Upload Types|Update Upload Types|Delete Upload Types', ['only' => ['index','create','store','update','destroy']]);

        $validate_batch_year = new GeneralController();
        $validate_batch_year->batch_year_configuration();
    }
    
          /**
     * Display the specified resource.
     */
    public function unblock_adminHierache(string $admin_hierarchy_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $AdminHierarchies = AdminHierarchies::withTrashed()->find($admin_hierarchy_id);
                $AdminHierarchies->deleted_at  = null;
                $AdminHierarchies->created_by  = $user_id;
                $AdminHierarchies->update();

                $respose =[
                    'message'=> 'Admin Hierarchy UnBlocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
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
     * @OA\Get(
     *     path="/api/unBlockUser/{id}",
     *     summary="UnBlocked User",
     *     tags={"unBlock"},
     *     @OA\Parameter(
     *         name="id",
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
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="statusCode", type="integer")
    *         )
    *     )
    * )
    */
    public function unblock_user(string $id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $User = User::withTrashed()->find($id);
                $User->deleted_at  = null;
                $User->update();

                $successResponse = [
                    'message'=>'User Account UnBlocked Successfuly',
                    'statusCode'=> '201'
                ];
                return response()->json($successResponse); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
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
     * @OA\Get(
     *     path="/api/unBlockUploadType/{upload_type_id}",
     *     summary="UnBlocked Education Upload Types",
     *     tags={"unBlock"},
     *     @OA\Parameter(
     *         name="upload_type_id",
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
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="statusCode", type="integer")
    *         )
    *     )
    * )
    */
    public function unblock_upload_types(string $upload_type_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $UploadTypes = UploadTypes::withTrashed()->find($upload_type_id);
                $UploadTypes->deleted_at  = null;
                $UploadTypes->created_by  = $user_id;
                $UploadTypes->update();

                $respose =[
                    'message'=> 'Upload Type UnBlocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
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
     * @OA\Get(
     *     path="/api/unBlockLocation/{location_id}",
     *     summary="UnBlocked Locations",
     *     tags={"unBlock"},
     *     @OA\Parameter(
     *         name="location_id",
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
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="statusCode", type="integer")
    *         )
    *     )
    * )
    */
    public function unblock_geographical_locations(string $location_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $GeographicalLocations = GeographicalLocations::withTrashed()->find($location_id);
                $GeographicalLocations->deleted_at  = null;
                $GeographicalLocations->created_by  = $user_id;
                $GeographicalLocations->update();

                $respose =[
                    'message'=> 'Geographical Location UnBlocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
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
     * @OA\Get(
     *     path="/api/unBlockIdentification/{identification_id}",
     *     summary="UnBlocked Identifications",
     *     tags={"unBlock"},
     *     @OA\Parameter(
     *         name="identification_id",
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
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="statusCode", type="integer")
    *         )
    *     )
    * )
    */
    public function unblock_identifications(string $identification_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Identifications = Identifications::withTrashed()->find($identification_id);
                $Identifications->deleted_at  = null;
                $Identifications->created_by  = $user_id;
                $Identifications->update();

                $respose =[
                    'message'=> 'Identification UnBlocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
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
     * Display the specified resource.
     */
    public function unblock_facility_levels(string $facility_level_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $FacilityLevels = FacilityLevels::withTrashed()->find($facility_level_id);
                $FacilityLevels->deleted_at  = null;
                $FacilityLevels->created_by  = $user_id;
                $FacilityLevels->update();

                $respose =[
                    'message'=> 'Facility Level UnBlocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
                ];
                return response()->json($errorResponse);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function unblock_departments(string $depatment_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Departments = Departments::withTrashed()->find($depatment_id);
                $Departments->deleted_at  = null;
                $Departments->created_by  = $user_id;
                $Departments->update();

                $respose =[
                    'message'=> 'Department UnBlocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
                ];
                return response()->json($errorResponse);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    public function unblock_parent_upload_type($parent_id){

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $ParentUploads = ParentUploads::withTrashed()->find($parent_id);
                $ParentUploads->deleted_at  = null;
                $ParentUploads->created_by  = $user_id;
                $ParentUploads->update();

                $respose =[
                    'message'=> 'Parent Upload Type UnBlocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=> $e->getMessage(),
                    'statusCode'=> '500'
                ];
                return response()->json($errorResponse);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }
}
