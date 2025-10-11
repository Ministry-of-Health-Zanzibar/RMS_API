<?php

namespace App\Http\Controllers\API\Patients;

use App\Models\PatientList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MedicalBoadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Patient List|Create Patient List|Update Patient List|Delete Patient List', 
            ['only' => ['index', 'store', 'show', 'updatePatientList', 'destroy', 'unBlockParentList', 'getAllPatientsByPatientListId']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('View Patient List')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        if ($user->hasAnyRole(['ROLE ADMIN'])) {
        $lists = PatientList::with([
            'creator', 
            'patients' => function ($q) {
                $q->with('geographicalLocation'); 
            }
        ])
        ->withTrashed()
        ->get();
        } else {
            $lists = PatientList::with([
            'creator', 
            'patients' => function ($q) {
                $q->with('geographicalLocation'); 
            }
        ])
        ->get();
        }

        return response([
            'data' => $lists,
            'statusCode' => 200
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Patient List')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }
        // Validate request
        $validator = Validator::make($request->all(), [
            'patient_list_title' => ['required', 'string', 'max:255'],
            'patient_list_file'  => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);
        
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        $filePath = null;

        if ($request->hasFile('patient_list_file')) {

            // Get the uploaded file
            $file = $request->file('patient_list_file');

            // Extract the file extension (pdf, jpg, jpeg, png)
            $extension = $file->getClientOriginalExtension();

            // Generate a custom file name
            $newFileName = 'patient_list_' .  date('h-i-s_a_d-m-Y') . '.' . $extension;

            // Move the file to public/uploads/patientLists/
            $file->move(public_path('uploads/patientLists/'), $newFileName);

            // Save the relative path
            $filePath = 'uploads/patientLists/'.$newFileName;
        }

        // Save to database
        $list = PatientList::create([
            'patient_list_title' => $request->patient_list_title,
            'patient_list_file'  => $filePath,
            'created_by'         => Auth::id(),
        ]);

        return response([
            'data'       => $list,
            'message'    => 'Patient list created successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->can('View Patient List')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $list = PatientList::with([
            'creator',
            'patients'
        ])->find($id);

        if (!$list) {
            return response([
                'message' => 'Patient list not found',
                'statusCode' => 404
            ], 404);
        }

        return response([
            'data' => $list,
            'statusCode' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatePatientList(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->can('Update Patient List')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $list = PatientList::findOrFail($id);

        // Validate input
        $data = $request->validate([
            'patient_list_title' => ['required', 'string', 'max:255'],
            'patient_list_file'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // Handle file upload
        if ($request->hasFile('patient_list_file')) {
            if ($list->patient_list_file && Storage::disk('public')->exists($list->patient_list_file)) {
                Storage::disk('public')->delete($list->patient_list_file);
            }

            $filePath = $request->file('patient_list_file')->store('patient_lists', 'public');
            $data['patient_list_file'] = $filePath;
        } else {
            $data['patient_list_file'] = $list->patient_list_file;
        }

        $list->update([
            'patient_list_title' => $data['patient_list_title'],
            'patient_list_file'  => $data['patient_list_file'],
            'updated_by'         => Auth::id(),
        ]);

        return response([
            'data' => $list->load(['creator', 'patients']),
            'message' => 'Patient list updated successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->can('Delete Patient List')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $list = PatientList::find($id);

        if (!$list) {
            return response([
                'message' => 'Patient list not found',
                'statusCode' => 404
            ], 404);
        }

        $list->delete();

        return response([
            'message' => 'Patient list deleted successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Restore a soft-deleted patient list.
     */
    public function unBlockParentList($id)
    {
        $list = PatientList::withTrashed()->find($id);

        if (!$list) {
            return response([
                'message' => 'Patient list not found',
                'statusCode' => 404
            ], 404);
        }

        $list->restore();

        return response([
            'message' => 'Patient list restored successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Get patients by patient list id using relationship.
     */
    public function getAllPatientsByPatientListId(int $patientListId)
    {
        $user = auth()->user();
        if (!$user->can('View Patient List')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $list = PatientList::with([
            'patients.files',
            'patients.geographicalLocation'
        ])->find($patientListId);
        

        if (!$list) {
            return response([
                'message' => 'Patient list not found',
                'statusCode' => 404
            ], 404);
        }

        return response([
            'data' => $list,
            'statusCode' => 200,
        ], 200);
    }
}
