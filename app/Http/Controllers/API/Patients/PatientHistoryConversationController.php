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
        $patientHistoryId = $request->input('patient_history_id');

        // Fetch all threads where the logged-in user is either the sender OR the receiver
        $conversations = PatientHistoryConversation::with([
                'sender:id,first_name,last_name', 
                'children.sender:id,first_name,last_name' 
            ])
            ->where('patient_history_id', $patientHistoryId)
            ->whereNull('parent_id') // Get root messages only
            ->where(function($query) use ($user) {
                // Automatically detect involvement based on Auth ID
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->get();

        // Map the collection to your required structure
        $data = $conversations->map(function ($convo) use ($patientHistoryId) {
            return [
                "patient_history_id" => (int) $patientHistoryId,
                "conversation_id"    => $convo->conversation_id,
                "user_id"            => $convo->sender->id,
                "sender_full_name"   => $convo->sender->full_name,
                "message"            => $convo->message,
                "date"               => $convo->created_at->diffForHumans(),
                "replies"            => $convo->children->map(function ($reply) {
                    return [
                        "conversation_id"    => $reply->conversation_id,
                        "user_id"            => $reply->sender_id,
                        "sender_full_name"   => $reply->sender->full_name,
                        "message"            => $reply->message,
                        "date"               => $reply->created_at->diffForHumans(),
                    ];
                })
            ];
        });

        return response()->json([
            "statusCode" => 200,
            "data"       => $data
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
            // Receiver is ONLY required if we are NOT replying (no parent_id)
            'receiver'           => 'required_without:parent_id|nullable|in:mkurugenzi,board,hospital,dg',
            'parent_id'          => 'nullable|exists:patient_history_conversations,conversation_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $resolvedReceiverId = null;

            // CASE 1: This is a REPLY
            if ($request->filled('parent_id')) {
                $parent = PatientHistoryConversation::findOrFail($request->parent_id);
                
                // Logic: If I am the original sender, send to the original receiver.
                // If I am the original receiver, send back to the original sender.
                $resolvedReceiverId = ($parent->sender_id === $user->id)
                    ? $parent->receiver_id
                    : $parent->sender_id;
                    
                $receiverName = "Reply";
            }
            // CASE 2: This is a NEW MESSAGE (Initiating)
            else {
                $patientHistory = PatientHistory::findOrFail($request->patient_history_id);
                $patient = $patientHistory->patient; 
                
                $patientListRelation = DB::table('patient_list_patient')
                    ->where('patient_id', $patient->patient_id)
                    ->first();
                    
                $patientList = $patientListRelation 
                    ? DB::table('patient_lists')->where('patient_list_id', $patientListRelation->patient_list_id)->first()
                    : null;

                switch ($request->receiver) {
                    case 'dg':
                        $resolvedReceiverId = $patientHistory->dg_id;
                        break;
                    case 'mkurugenzi':
                        $resolvedReceiverId = $patientHistory->mkurugenzi_tiba_id;
                        break;
                    case 'board':
                        $resolvedReceiverId = $patientList ? $patientList->created_by : null;
                        break;
                    case 'hospital':
                        $resolvedReceiverId = $patient->created_by;
                        break;
                }
                
                $receiverName = ucfirst($request->receiver);
            }

            if (!$resolvedReceiverId) {
                throw new \Exception("Could not resolve a User ID for the intended receiver.");
            }

            $conversation = PatientHistoryConversation::create([
                'patient_history_id' => $request->patient_history_id,
                'sender_id'          => $user->id,
                'receiver_id'        => $resolvedReceiverId,
                'parent_id'          => $request->parent_id,
                'message'            => $request->message,
            ]);

            DB::commit();

            return response()->json([
                "statusCode" => 201,
                "message_text" => $request->parent_id ? "Reply sent successfully" : "Message sent to " . $receiverName,
                "data" => [
                    "patient_history_id" => (int) $conversation->patient_history_id,
                    "conversation_id"    => $conversation->conversation_id,
                    "user_id"            => $user->id,
                    "sender_full_name"   => $user->full_name,
                    "message"            => $conversation->message,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['statusCode' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display specific conversation message.
     */
    public function show(Request $request, $patientHistoryId)
    {
        $user = auth()->user();

        // 1. Get the collection of conversations
        $conversations = PatientHistoryConversation::where('patient_history_id', $patientHistoryId)
            ->where('receiver_id', $user->id) 
            ->whereNull('parent_id')         
            ->with([
                'sender:id,first_name,last_name',
                'children' => function($query) {
                    $query->with('sender:id,first_name,last_name')->oldest();
                }
            ])
            ->latest() 
            ->get(); // This returns a Collection

        // 2. Check if the collection is empty using the isEmpty() method
        if ($conversations->isEmpty()) {
            return response()->json([
                'statusCode' => 404, 
                'message' => "No conversations found."
            ], 404);
        }

        // 3. Map the collection. 
        // Notice we don't call $conversations->conversation_id anymore.
        $data = $conversations->map(function ($convo) use ($patientHistoryId) {
            return [
                "patient_history_id" => (int) $patientHistoryId,
                "conversation_id"    => $convo->conversation_id, // Accessing the individual item
                "user_id"            => $convo->sender->id,
                "sender_full_name"   => $convo->sender->full_name,
                "message"            => $convo->message,
                "date"               => $convo->created_at->diffForHumans(),
                "replies"            => $convo->children->map(function ($reply) {
                    return [
                        "conversation_id"    => $reply->conversation_id,
                        "user_id"            => $reply->sender_id,
                        "sender_full_name"   => $reply->sender->full_name,
                        "message"            => $reply->message,
                        "date"               => $reply->created_at->diffForHumans(),
                    ];
                })
            ];
        });

        return response()->json([
            "statusCode" => 200,
            "data"       => $data
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

