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
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        $patientHistoryId = $request->query('patient_history_id');

        // Fetching threaded conversations
        $conversations = PatientHistoryConversation::with([
                'sender:id,first_name,middle_name,last_name', 
                'receiver:id,first_name,middle_name,last_name',
                'children.sender:id,first_name,middle_name,last_name'
            ])
            ->where('patient_history_id', $patientHistoryId)
            ->whereNull('parent_id') // Start with the "Root" messages
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $conversations,
            'message' => 'Conversations retrieved successfully'
        ], 200);
    }

    /**
     * Store a new conversation message.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'patient_history_id' => 'required|exists:patient_histories,patient_histories_id',
            'message'            => 'required|string',
            'receiver'           => 'required|in:mkurugenzi,board,hospital,dg',
            'parent_id'          => 'nullable|exists:patient_history_conversations,conversation_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $patientHistory = PatientHistory::findOrFail($request->patient_history_id);
            $patient = $patientHistory->patient; 
            
            $patientListRelation = DB::table('patient_list_patient')
                ->where('patient_id', $patient->patient_id)
                ->first();
                
            $patientList = $patientListRelation 
                ? DB::table('patient_lists')->where('patient_list_id', $patientListRelation->patient_list_id)->first()
                : null;

            $resolvedReceiverId = null;

            // Resolve who gets the message
            switch ($request->receiver) {
                case 'dg':
                case 'mkurugenzi':
                    $resolvedReceiverId = $patientHistory->dg_id ?? $patientHistory->mkurugenzi_tiba_id;
                    break;
                case 'board':
                    $resolvedReceiverId = $patientList ? $patientList->created_by : null;
                    break;
                case 'hospital':
                    $resolvedReceiverId = $patient->created_by;
                    break;
            }

            if (!$resolvedReceiverId) {
                throw new \Exception("Could not resolve a User ID for: " . $request->receiver);
            }

            $data = [
                'patient_history_id' => $request->patient_history_id,
                'sender_id'          => $user->id,
                'receiver_id'        => $resolvedReceiverId,
                'parent_id'          => $request->parent_id,
                'message'            => $request->message,
            ];

            // Create the record
            $conversation = PatientHistoryConversation::create($data);

            DB::commit();

            // Return the clean, minimal response matching your 'show' method
            return response()->json([
                "patient_history_id" => (int) $conversation->patient_history_id,
                "conversation_id"    => $conversation->conversation_id,
                "user_id"            => $user->id,
                "sender_full_name"   => $user->full_name, // Using your User model accessor
                "message"            => $conversation->message,
                "status"             => true,
                "message_text"       => "Message sent to " . ucfirst($request->receiver)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display specific conversation message.
     */
    // The order MUST match the Route: {patientHistoryConversation} then {conversation_id}
    public function show(Request $request, $patientHistoryId, $conversation_id)
    {
        $user = auth()->user();

        // 1. Authorization check
        if (!$user->can('View Patient History')) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        // 2. Fetch the conversation with sender and children (replies)
        $conversation = PatientHistoryConversation::where('conversation_id', $conversation_id)
            ->where('patient_history_id', $patientHistoryId)
            ->with([
                'sender:id,first_name,middle_name,last_name',
                'children' => function($query) {
                    $query->with('sender:id,first_name,middle_name,last_name')->oldest();
                }
            ])
            ->first();

        if (!$conversation) {
            return response()->json(['status' => false, 'message' => "Not found"], 404);
        }

        // 3. Construct the payload with User IDs (id) included
        return response()->json([
            "patient_history_id" => (int) $patientHistoryId,
            "conversation_id"    => $conversation->conversation_id,
            "user_id"            => $conversation->sender->id, // ID from users table
            "sender_full_name"   => $conversation->sender->full_name,
            "message"            => $conversation->message,
            "replies"            => $conversation->children->map(function ($reply) {
                return [
                    "conversation_id"    => $reply->conversation_id,
                    "user_id"            => $reply->sender->id, // ID from users table for the reply
                    "receiver_full_name" => $reply->sender->full_name,
                    "message"            => $reply->message,
                ];
            })
        ], 200);
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'statusCode' => 422
            ], 422);
        }

        try {

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

