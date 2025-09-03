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
            'file' => 'required|file|mimes:pdf,jpg,png,doc,docx',
            'description' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('patient_files', 'public');

        $patientFile = PatientFile::create([
            'patient_id' => $request->patient_id,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientOriginalExtension(),
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
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
