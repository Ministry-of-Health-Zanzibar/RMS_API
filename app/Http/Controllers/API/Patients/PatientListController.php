<?php

namespace App\Http\Controllers\API\Patients;

use App\Models\PatientList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PatientListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Patient List|Create Patient List|Update Patient List|Delete Patient List', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lists = PatientList::withTrashed()->get();

        if ($lists->isEmpty()) {
            return response([
                'message' => 'No patient lists found',
                'statusCode' => 200
            ], 200);
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
        $request->validate([
            'patient_list_title' => ['required', 'string', 'max:255'],
            'patient_list_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // store file
        $filePath = $request->file('patient_list_file')->store('patient_lists', 'public');

        $list = PatientList::create([
            'patient_list_title' => $request->patient_list_title,
            'patient_list_file' => $filePath,
            'created_by' => Auth::id(),
        ]);

        return response([
            'data' => $list,
            'message' => 'Patient list created successfully',
            'statusCode' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $list = PatientList::with('creator')->find($id);

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
        $list = PatientList::findOrFail($id);

        $request->validate([
            'patient_list_title' => ['required', 'string', 'max:255'],
            'patient_list_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // If a new file is uploaded, replace the old one
        if ($request->hasFile('patient_list_file')) {
            // delete old file if exists
            if ($list->patient_list_file && Storage::disk('public')->exists($list->patient_list_file)) {
                Storage::disk('public')->delete($list->patient_list_file);
            }

            $filePath = $request->file('patient_list_file')->store('patient_lists', 'public');
            $list->patient_list_file = $filePath;
        }

        $list->update([
            'patient_list_title' => $request->patient_list_title,
            'patient_list_file'  => $list->patient_list_file, // keep old file if no new one
            'updated_by'         => Auth::id(),
        ]);

        return response([
            'data' => $list,
            'message' => 'Patient list updated successfully',
            'statusCode' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy($id)
    {
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


    // Get patient by patient list id
    public function getAllPatientsByPatientListId(int $patientListId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL', 'ROLE STAFF', 'ROLE DG OFFICER']) || !$user->can('View Patient')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $rows = DB::table('patient_lists')
            ->leftJoin('patients', 'patients.patient_list_id', '=', 'patient_lists.patient_list_id')
            ->select(
                'patient_lists.patient_list_id',
                'patient_lists.patient_list_title',
                'patient_lists.patient_list_file',
                'patients.patient_id',
                'patients.name',
                'patients.date_of_birth',
                'patients.gender',
                'patients.phone',
                'patients.location',
                'patients.job',
                'patients.position'
            )
            ->where('patient_lists.patient_list_id', '=', $patientListId)
            ->get();

        // Transform into nested structure
        $patientList = [
            'patient_list_title' => null,
            'patient_list_file' => null,
            'patients' => []
        ];

        if (!$rows->isEmpty()) {
            $patientList['patient_list_id'] = $rows[0]->patient_list_id;
            $patientList['patient_list_title'] = $rows[0]->patient_list_title;
            $patientList['patient_list_file'] = $rows[0]->patient_list_file;

            $patients = $rows->filter(fn($row) => $row->patient_id !== null)
                            ->map(fn($row) => [
                                'patient_id' => $row->patient_id,
                                'name' => $row->name,
                                'date_of_birth' => $row->date_of_birth,
                                'gender' => $row->gender,
                                'phone' => $row->phone,
                                'location' => $row->location,
                                'job' => $row->job,
                                'position' => $row->position,
                            ])
                            ->values(); // reset keys

            $patientList['patients'] = $patients;
        }

        return response()->json([
            'data' => $patientList,
            'statusCode' => 200,
        ]);
    }
}