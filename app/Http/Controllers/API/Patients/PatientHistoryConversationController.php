<?php

namespace App\Http\Controllers\API\Patients;

use App\Http\Controllers\Controller;
use App\Models\PatientHistoryConversation;
use App\Models\PatientHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Patient History Conversations",
 *     description="API Endpoints for managing patient history conversations (chat/messaging)"
 * )
 */
class PatientHistoryConversationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display conversations for a specific patient history.
     * Query param: patient_history_id (required)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('View Patient History')) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'patient_history_id' => 'required|exists:patient_histories,patient_histories_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        $patientHistoryId = $request->input('patient_history_id');

        $conversations = PatientHistoryConversation::with(['sender', 'receiver', 'parent', 'children'])
            ->where('patient_history_id', $patientHistoryId)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $conversations,
            'message' => 'Conversations retrieved successfully',
            'statusCode' => 200
        ]);
    }

    /**
     * Store a new conversation message.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Patient History')) {  // Reuse existing permission
            return response()->json([
                'status' => false,
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'patient_history_id' => 'required|exists:patient_histories,patient_histories_id',
            'receiver_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:patient_history_conversations,conversation_id',
            'message' => 'required|string|max:65535',
            'case_status_at_time' => 'required|string|max:50',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->only([
                'patient_history_id',
                'receiver_id',
                'parent_id',
                'message',
                'case_status_at_time'
            ]);
            $data['sender_id'] = $user->id;

            // Handle file upload
            if ($request->hasFile('attachment_file')) {
                $file = $request->file('attachment_file');
                $fileName = 'conv_' . time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/conversations/'), $fileName);
                $data['attachment_file'] = 'uploads/conversations/' . $fileName;
            }

            $conversation = PatientHistoryConversation::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'data' => $conversation->load(['sender', 'receiver', 'patientHistory']),
                'message' => 'Message sent successfully',
                'statusCode' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Conversation creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Creation failed',
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Display specific conversation message.
     */
    public function show(PatientHistoryConversation $patientHistoryConversation)
    {
        $user = auth()->user();
        if (!$user->can('View Patient History')) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $patientHistoryConversation->load(['sender', 'receiver', 'parent', 'children', 'patientHistory']);

        return response()->json([
            'status' => true,
            'data' => $patientHistoryConversation,
            'message' => 'Conversation retrieved successfully',
            'statusCode' => 200
        ]);
    }

    /**
     * Update conversation message.
     */
    public function update(Request $request, PatientHistoryConversation $patientHistoryConversation)
    {
        $user = auth()->user();
        if (!$user->can('Update Patient History')) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Only allow owner to edit
        if ($patientHistoryConversation->sender_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Can only edit own messages',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'sometimes|required|string|max:65535',
            'case_status_at_time' => 'sometimes|required|string|max:50',
            'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        try {
            $data = $request->only(['message', 'case_status_at_time']);

            // Handle new file upload (replace old)
            if ($request->hasFile('attachment_file')) {
                // Delete old file
                if ($patientHistoryConversation->attachment_file && file_exists(public_path($patientHistoryConversation->attachment_file))) {
                    unlink(public_path($patientHistoryConversation->attachment_file));
                }
                $file = $request->file('attachment_file');
                $fileName = 'conv_' . time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/conversations/'), $fileName);
                $data['attachment_file'] = 'uploads/conversations/' . $fileName;
            }

            $patientHistoryConversation->update($data);

            return response()->json([
                'status' => true,
                'data' => $patientHistoryConversation->fresh()->load(['sender', 'receiver']),
                'message' => 'Message updated successfully',
                'statusCode' => 200
            ]);

        } catch (\Exception $e) {
            Log::error('Conversation update failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Update failed',
                'statusCode' => 500
            ], 500);
        }
    }

    /**
     * Remove conversation message.
     */
    public function destroy(PatientHistoryConversation $patientHistoryConversation)
    {
        $user = auth()->user();
        if (!$user->can('Delete Patient History')) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Soft delete
        $patientHistoryConversation->delete();

        return response()->json([
            'status' => true,
            'message' => 'Message deleted successfully',
            'statusCode' => 200
        ]);
    }
}

