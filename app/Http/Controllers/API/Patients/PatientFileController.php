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
            'patient_id' => 'required|numeric|exists:patients,id',
            'patient_file' => 'required|file|mimes:pdf,jpg,png,doc,docx',
            'description' => 'nullable|string',
        ]);

        // Check if the request contains a file with the field name 'patient_file'
        if ($request->hasFile('patient_file')) {

            // Get the uploaded file object from the request
            $file = $request->file('patient_file');

            // Generate a new file name:
            // - time() ensures uniqueness with a timestamp
            // - preg_replace replaces spaces with underscores in the original file name
            // - getClientOriginalName() gets the original filename from the client
            $newFileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            // Move the uploaded file from temporary storage to the public/uploads/patientFiles/ directory
            // The file will be renamed to the $newFileName we generated above
            $file->move(public_path('uploads/patientFiles/'), $newFileName);

            // Save the relative path (to be stored in DB or used later)
            // Example: 'uploads/patientFiles/1694791234_My_Report.pdf'
            $path = 'uploads/patientFiles/' . $newFileName;
        }

        // Save record in the patient_files table
        $patientFile = PatientFile::create([
            'patient_id'  => $request->patient_id,
            'file_name'   => $file->getClientOriginalName(),        // original file name
            'file_path'   => $path,                                 // saved relative path
            'file_type'   => $file->getClientOriginalExtension(),   // file extension (e.g., pdf, jpg)
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'patient_file uploaded successfully',
            'data' => $patientFile,
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
