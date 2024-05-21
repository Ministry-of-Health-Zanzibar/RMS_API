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
use App\Models\EmployementStatuses;
use App\Models\GeographicalLocations;
use App\Models\Identifications;
use App\Models\Institutes;
use App\Models\MeritalStatuses;
use App\Models\Relations;
use App\Models\Senorities;
use App\Models\Specializations;
use App\Models\TermOfEmployments;
use App\Models\WorkingStations;
use App\Models\FacilityLevels;
use App\Models\PostCategories;
use App\Models\Skills;
use App\Models\Languages;
use App\Models\Hobbies;
use App\Models\StandardLevels;
use App\Models\HealthBodies;
use App\Models\EmployerInformations;
use App\Models\SalaryScales;
use App\Models\SalarySources;
use App\Models\Departments;
use App\Models\WorkingPositions;
use App\Models\PositionPosts;
use App\Models\ParentUploads;
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
     * Display the specified resource.
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
     * Display the specified resource.
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
     * Display the specified resource.
     */
    public function unblock_caders(string $cader_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Caders = Caders::withTrashed()->find($cader_id);
                $Caders->deleted_at  = null;
                $Caders->created_by  = $user_id;
                $Caders->update();

                $respose =[
                    'message'=> 'Cader UnBlocked Successfuly',
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
    public function unblock_designations(string $designation_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Designations = Designations::withTrashed()->find($designation_id);
                $Designations->deleted_at  = null;
                $Designations->created_by  = $user_id;
                $Designations->update();

                $respose =[
                    'message'=> 'Designation UnBlocked Successfuly',
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
    public function unblock_disabilities(string $disability_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Disabilities = Disabilities::withTrashed()->find($disability_id);
                $Disabilities->deleted_at  = null;
                $Disabilities->created_by  = $user_id;
                $Disabilities->update();

                $respose =[
                    'message'=> 'Disability UnBlocked Successfuly',
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
    public function unblock_education_levels(string $education_level_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $EducationLevels = EducationLevels::withTrashed()->find($education_level_id);
                $EducationLevels->deleted_at  = null;
                $EducationLevels->created_by  = $user_id;
                $EducationLevels->update();

                $respose =[
                    'message'=> 'Education Level UnBlocked Successfuly',
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
    public function unblock_employement_statuses(string $employement_status_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $EmployementStatuses = EmployementStatuses::withTrashed()->find($employement_status_id);
                $EmployementStatuses->deleted_at  = null;
                $EmployementStatuses->created_by  = $user_id;
                $EmployementStatuses->update();

                $respose =[
                    'message'=> 'Employement Status UnBlocked Successfuly',
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
     * Display the specified resource.
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
    public function unblock_institutes(string $institute_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Institutes = Institutes::withTrashed()->find($institute_id);
                $Institutes->deleted_at  = null;
                $Institutes->created_by  = $user_id;
                $Institutes->update();

                $respose =[
                    'message'=> 'Institute UnBlocked Successfuly',
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
    public function unblock_merital_statuses(string $merital_status_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $MeritalStatuses = MeritalStatuses::withTrashed()->find($merital_status_id);
                $MeritalStatuses->deleted_at  = null;
                $MeritalStatuses->created_by  = $user_id;
                $MeritalStatuses->update();

                $respose =[
                    'message'=> 'Merital Status UnBlocked Successfuly',
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
    public function unblock_relations(string $relation_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Relations = Relations::withTrashed()->find($relation_id);
                $Relations->deleted_at  = null;
                $Relations->created_by  = $user_id;
                $Relations->update();

                $respose =[
                    'message'=> 'Relation UnBlocked Successfuly',
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
    public function unblock_senorities(string $senority_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Senorities = Senorities::withTrashed()->find($senority_id);
                $Senorities->deleted_at  = null;
                $Senorities->created_by  = $user_id;
                $Senorities->update();

                $respose =[
                    'message'=> 'Senority UnBlocked Successfuly',
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
    public function unblock_specializations(string $specialization_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Specializations = Specializations::withTrashed()->find($specialization_id);
                $Specializations->deleted_at  = null;
                $Specializations->created_by  = $user_id;
                $Specializations->update();

                $respose =[
                    'message'=> 'Specialization UnBlocked Successfuly',
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
    public function unblock_term_of_employments(string $term_of_employment_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $TermOfEmployments = TermOfEmployments::withTrashed()->find($term_of_employment_id);
                $TermOfEmployments->deleted_at  = null;
                $TermOfEmployments->created_by  = $user_id;
                $TermOfEmployments->update();

                $respose =[
                    'message'=> 'Term Of Employment UnBlocked Successfuly',
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
    public function unblock_working_stations(string $WorkingStations)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $WorkingStations = WorkingStations::withTrashed()->find($WorkingStations);
                $WorkingStations->deleted_at  = null;
                $WorkingStations->created_by  = $user_id;
                $WorkingStations->update();

                $respose =[
                    'message'=> 'Working Station UnBlocked Successfuly',
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

    /**
     * Display the specified resource.
     */
    public function unblock_post_categories(string $post_category_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $PostCategories = PostCategories::withTrashed()->find($post_category_id);
                $PostCategories->deleted_at  = null;
                $PostCategories->created_by  = $user_id;
                $PostCategories->update();

                $respose =[
                    'message'=> 'Post Category UnBlocked Successfuly',
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

    public function unblock_skills(string $skill_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Skills = Skills::withTrashed()->find($skill_id);
                $Skills->deleted_at  = null;
                $Skills->created_by  = $user_id;
                $Skills->update();

                $respose =[
                    'message'=> 'Skill UnBlocked Successfuly',
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

    public function unblock_languages(string $language_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Languages = Languages::withTrashed()->find($language_id);
                $Languages->deleted_at  = null;
                $Languages->created_by  = $user_id;
                $Languages->update();

                $respose =[
                    'message'=> 'Language UnBlocked Successfuly',
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

    public function unblock_hobbies(string $hobby_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $Hobbies = Hobbies::withTrashed()->find($hobby_id);
                $Hobbies->deleted_at  = null;
                $Hobbies->created_by  = $user_id;
                $Hobbies->update();

                $respose =[
                    'message'=> 'Hobby UnBlocked Successfuly',
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

    public function unblock_standard_levels(string $level_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $StandardLevels = StandardLevels::withTrashed()->find($level_id);
                $StandardLevels->deleted_at  = null;
                $StandardLevels->created_by  = $user_id;
                $StandardLevels->update();

                $respose =[
                    'message'=> 'Standard Level UnBlocked Successfuly',
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

    public function unblock_health_body(string $body_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $HealthBodies = HealthBodies::withTrashed()->find($body_id);
                $HealthBodies->deleted_at  = null;
                $HealthBodies->created_by  = $user_id;
                $HealthBodies->update();

                $respose =[
                    'message'=> 'Health Body UnBlocked Successfuly',
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

    public function unblock_employer(string $employer_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $EmployerInformations = EmployerInformations::withTrashed()->find($employer_id);
                $EmployerInformations->deleted_at  = null;
                $EmployerInformations->created_by  = $user_id;
                $EmployerInformations->update();

                $respose =[
                    'message'=> 'Employer UnBlocked Successfuly',
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

    public function unblock_salary_scale(string $scale_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $SalaryScales = SalaryScales::withTrashed()->find($scale_id);
                $SalaryScales->deleted_at  = null;
                $SalaryScales->created_by  = $user_id;
                $SalaryScales->update();

                $respose =[
                    'message'=> 'Salary Scale UnBlocked Successfuly',
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

    public function unblock_salary_source(string $source_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $SalarySources = SalarySources::withTrashed()->find($source_id);
                $SalarySources->deleted_at  = null;
                $SalarySources->created_by  = $user_id;
                $SalarySources->update();

                $respose =[
                    'message'=> 'Salary Source UnBlocked Successfuly',
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

    public function unblock_working_position($working_position_id){

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $WorkingPositions = WorkingPositions::withTrashed()->find($working_position_id);
                $WorkingPositions->deleted_at  = null;
                $WorkingPositions->created_by  = $user_id;
                $WorkingPositions->update();

                $respose =[
                    'message'=> 'Working Station Department UnBlocked Successfuly',
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

    public function unblock_position_post($position_post_id){

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $user_id = auth()->user()->id;

            try{
                $PositionPosts = PositionPosts::withTrashed()->find($position_post_id);
                $PositionPosts->deleted_at  = null;
                $PositionPosts->created_by  = $user_id;
                $PositionPosts->update();

                $respose =[
                    'message'=> 'Position Post UnBlocked Successfuly',
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
