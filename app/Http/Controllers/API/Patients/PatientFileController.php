<?php

namespace App\Http\Controllers\API\Patients;

use App\Http\Controllers\Controller;
use App\Models\PatientFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientFileController extends Controller
{
    // Store a new file
    public function store(Request $request)
    {
        $request->validate([
            'patient_id'   => 'required|numeric|exists:patients,id',
            'patient_file' => 'required|file|mimes:pdf,jpg,png,doc,docx',
            'description'  => 'nullable|string',
        ]);

        $filePath = null; // to store the relative path

        if ($request->hasFile('patient_file')) {

            // Get the uploaded file object
            $file = $request->file('patient_file');

            // Extract the file extension
            $extension = $file->getClientOriginalExtension();

            // Generate a custom file name
            $newFileName = 'patient_file_' .  date('h-i-s_a_d-m-Y') . '.' . $extension;

            // Move the uploaded file to public/uploads/patientFiles/
            $file->move(public_path('uploads/patientFiles/'), $newFileName);

            // Save the relative file path
            $filePath = 'uploads/patientFiles/'.$newFileName; // or 'uploads/patientFiles/' . $newFileName for full path
        }

        // Save record in the patient_files table
        $patientFile = PatientFile::create([
            'patient_id'  => $request->patient_id,
            'file_name'   => $file->getClientOriginalName(),  // original filename
            'file_path'   => $filePath,                       // saved relative path
            'file_type'   => $extension,                      // file extension
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Patient file uploaded successfully',
            'data'    => $patientFile,
        ], 201);
    }

    // List files for a patient
    public function index($patientId)
    {
        $files = PatientFile::where('patient_id', $patientId)->get();

        return response()->json([
            'data' => $files,
        ]);
    }

    // Download a file
    public function download($id)
    {
        $file = PatientFile::findOrFail($id);
        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    // Delete a file
    public function destroy($id)
    {
        $file = PatientFile::findOrFail($id);

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return response()->json([
            'message' => 'File deleted successfully'
        ]);
    }
}
