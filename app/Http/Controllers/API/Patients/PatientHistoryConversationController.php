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
    // public function index(Request $request)
    // {

    //     $patientHistoryId = $request->query('patient_history_id');

    //     // Fetching threaded conversations
    //     $conversations = PatientHistoryConversation::with([
    //             'sender:id,first_name,middle_name,last_name', 
    //             'receiver:id,first_name,middle_name,last_name',
    //             'children.sender:id,first_name,middle_name,last_name'
    //         ])
    //         ->where('patient_history_id', $patientHistoryId)
    //         ->whereNull('parent_id') // Start with the "Root" messages
    //         ->latest()
    //         ->get();

    //     return response()->json([
    //         'data' => $conversations,
    //         'statusCode' => 200
    //     ], 200);
    // }
    // public function index(Request $request)
    // {
    //     $user = auth()->user();
    //     // $patientHistoryId = $request->query('patient_history_id');
    //     $patientHistoryId = $request->input('patient_history_id');

    //     // Fetch conversations where the user is involved
    //     $conversations = PatientHistoryConversation::with([
    //             'sender:id,first_name,last_name', 
    //             'receiver:id,first_name,last_name'
    //         ])
    //         ->where('patient_history_id', $patientHistoryId)
    //         ->whereNull('parent_id') // Only top-level messages
    //         ->where(function($query) use ($user) {
    //             // A person sees a root message if:
    //             // 1. They sent it.
    //             // 2. It was sent specifically to them.
    //             $query->where('sender_id', $user->id)
    //                 ->orWhere('receiver_id', $user->id);
    //         })
    //         ->latest()
    //         ->get();

    //     return response()->json([
    //         'data' => $conversations->map(function($convo) {
    //             return [
    //                 "conversation_id" => $convo->conversation_id,
    //                 "sender_name"     => $convo->sender->full_name,
    //                 "receiver_name"   => $convo->receiver->full_name ?? 'N/A',
    //                 "last_message"    => $convo->message,
    //                 "date"            => $convo->created_at->diffForHumans(),
    //                 // Add this line to see the replies in the index!
    //                 "replies"         => $convo->children->map(function($reply) {
    //                     return [
    //                         "sender" => $reply->sender->full_name,
    //                         "message" => $reply->message
    //                     ];
    //                 })
    //             ];
    //         }),
    //         'statusCode' => 200
    //     ], 200);
    // }

    public function index(Request $request)
    {
        $user = auth()->user();
        $patientHistoryId = $request->input('patient_history_id');

        // Fetch conversations where the user is involved
        $conversations = PatientHistoryConversation::with([
                'sender:id,first_name,last_name', 
                'receiver:id,first_name,last_name',
                'children.sender:id,first_name,last_name' 
            ])
            ->where('patient_history_id', $patientHistoryId)
            ->whereNull('parent_id') 
            ->where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->get();

        // Map each conversation to your specific structure
        $mappedConversations = $conversations->map(function ($convo) use ($patientHistoryId) {
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
                        "user_id"            => $reply->sender->id,
                        "receiver_full_name" => $reply->sender->full_name, 
                        "message"            => $reply->message,
                        "date"               => $reply->created_at->diffForHumans(),
                    ];
                })
            ];
        });

        // Return the response wrapped in 'data'
        return response()->json([
            "statusCode" => 200,
            "data"       => $mappedConversations
        ], 200);
    }

    /**
     * Store a new conversation message.
     */
    // public function store(Request $request)
    // {
    //     $user = auth()->user();

    //     $validator = Validator::make($request->all(), [
    //         'patient_history_id' => 'required|exists:patient_histories,patient_histories_id',
    //         'message'            => 'required|string',
    //         'receiver'           => 'required|in:mkurugenzi,board,hospital,dg',
    //         'parent_id'          => 'nullable|exists:patient_history_conversations,conversation_id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         $patientHistory = PatientHistory::findOrFail($request->patient_history_id);
    //         $patient = $patientHistory->patient; 
            
    //         $patientListRelation = DB::table('patient_list_patient')
    //             ->where('patient_id', $patient->patient_id)
    //             ->first();
                
    //         $patientList = $patientListRelation 
    //             ? DB::table('patient_lists')->where('patient_list_id', $patientListRelation->patient_list_id)->first()
    //             : null;

    //         $resolvedReceiverId = null;

    //         switch ($request->receiver) {
    //             case 'dg':
    //             case 'mkurugenzi':
    //                 $resolvedReceiverId = $patientHistory->dg_id ?? $patientHistory->mkurugenzi_tiba_id;
    //                 break;
    //             case 'board':
    //                 $resolvedReceiverId = $patientList ? $patientList->created_by : null;
    //                 break;
    //             case 'hospital':
    //                 $resolvedReceiverId = $patient->created_by;
    //                 break;
    //         }

    //         if (!$resolvedReceiverId) {
    //             throw new \Exception("Could not resolve a User ID for: " . $request->receiver);
    //         }

    //         $conversation = PatientHistoryConversation::create([
    //             'patient_history_id' => $request->patient_history_id,
    //             'sender_id'          => $user->id,
    //             'receiver_id'        => $resolvedReceiverId,
    //             'parent_id'          => $request->parent_id,
    //             'message'            => $request->message,
    //         ]);

    //         DB::commit();

    //         // All message details now wrapped in the 'data' key
    //         return response()->json([
    //             "statusCode" => 201,
    //             "message_text" => "Message sent to " . ucfirst($request->receiver),
    //             "data" => [
    //                 "patient_history_id" => (int) $conversation->patient_history_id,
    //                 "conversation_id"    => $conversation->conversation_id,
    //                 "user_id"            => $user->id,
    //                 "sender_full_name"   => $user->full_name,
    //                 "message"            => $conversation->message,
    //             ]
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['statusCode' => 500, 'message' => $e->getMessage()], 500);
    //     }
    // }
    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'patient_history_id' => 'required|exists:patient_histories,patient_histories_id',
            'message'            => 'required|string',
            'receiver'           => 'required_without:parent_id|in:mkurugenzi,board,hospital,dg',
            'parent_id'          => 'nullable|exists:patient_history_conversations,conversation_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $resolvedReceiverId = null;

            // NEW LOGIC: If it's a reply, send it back to the original sender
            if ($request->filled('parent_id')) {
                $parent = PatientHistoryConversation::findOrFail($request->parent_id);
                // If I am replying, the receiver is the person who sent the parent message
                // OR if I sent the parent, the receiver is the original receiver.
                $resolvedReceiverId = ($parent->sender_id === $user->id) 
                    ? $parent->receiver_id 
                    : $parent->sender_id;
            } else {
                // ORIGINAL LOGIC: For new conversations, resolve by Role
                $patientHistory = PatientHistory::findOrFail($request->patient_history_id);
                $patient = $patientHistory->patient; 
                
                // ... (Your existing switch logic to find $resolvedReceiverId) ...
                switch ($request->receiver) {
                    case 'dg': $resolvedReceiverId = $patientHistory->dg_id; break;
                    case 'mkurugenzi': $resolvedReceiverId = $patientHistory->mkurugenzi_tiba_id; break;
                    // etc...
                }
            }

            if (!$resolvedReceiverId) {
                throw new \Exception("Could not resolve a User ID for this message.");
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
                "message_text" => $request->parent_id ? "Reply sent" : "New message started",
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
    // The order MUST match the Route: {patientHistoryConversation} then {conversation_id}
    // public function show(Request $request, $patientHistoryId, $conversation_id)
    // {
    //     $user = auth()->user();

    //     // 2. Fetch the conversation with sender and children (replies)
    //     $conversation = PatientHistoryConversation::where('conversation_id', $conversation_id)
    //         ->where('patient_history_id', $patientHistoryId)
    //         ->with([
    //             'sender:id,first_name,middle_name,last_name',
    //             'children' => function($query) {
    //                 $query->with('sender:id,first_name,middle_name,last_name')->oldest();
    //             }
    //         ])
    //         ->first();

    //     if (!$conversation) {
    //         return response()->json(['status' => false, 'message' => "Not found"], 404);
    //     }

    //     // 3. Construct the payload with User IDs (id) included
    //     return response()->json([
    //         "patient_history_id" => (int) $patientHistoryId,
    //         "conversation_id"    => $conversation->conversation_id,
    //         "user_id"            => $conversation->sender->id, // ID from users table
    //         "sender_full_name"   => $conversation->sender->full_name,
    //         "message"            => $conversation->message,
    //         "replies"            => $conversation->children->map(function ($reply) {
    //             return [
    //                 "conversation_id"    => $reply->conversation_id,
    //                 "user_id"            => $reply->sender->id, // ID from users table for the reply
    //                 "receiver_full_name" => $reply->sender->full_name,
    //                 "message"            => $reply->message,
    //             ];
    //         })
    //     ], 200);
    // }
    // public function show(Request $request, $patientHistoryId, $conversation_id)
    // {
    //     $user = auth()->user();

    //     $conversation = PatientHistoryConversation::where('conversation_id', $conversation_id)
    //         ->where('patient_history_id', $patientHistoryId)
    //         ->with([
    //             'sender:id,first_name,last_name',
    //             'children' => function($query) {
    //                 $query->with('sender:id,first_name,last_name')->oldest();
    //             }
    //         ])
    //         ->first();

    //     if (!$conversation) {
    //         return response()->json(['status' => false, 'message' => "Not found"], 404);
    //     }

    //     // SECURITY CHECK: Only the sender or receiver of the ROOT message can see the thread
    //     if ($user->id !== $conversation->sender_id && $user->id !== $conversation->receiver_id) {
    //         return response()->json(['status' => false, 'message' => "Unauthorized to view this thread"], 403);
    //     }

    //     return response()->json([
    //         "patient_history_id" => (int) $patientHistoryId,
    //         "conversation_id"    => $conversation->conversation_id,
    //         "sender_full_name"   => $conversation->sender->full_name,
    //         "message"            => $conversation->message,
    //         "replies"            => $conversation->children->map(function ($reply) {
    //             return [
    //                 "conversation_id"    => $reply->conversation_id,
    //                 "user_id"            => $reply->sender_id,
    //                 "sender_full_name"   => $reply->sender->full_name,
    //                 "message"            => $reply->message,
    //             ];
    //         })
    //     ], 200);
    // }

    public function show(Request $request, $patientHistoryId)
    {
        $user = auth()->user();

        // 1. Find the conversation where the logged-in user is the receiver 
        // and it is a "Root" message (parent_id is null)
        $conversation = PatientHistoryConversation::where('patient_history_id', $patientHistoryId)
            ->where('receiver_id', $user->id) // Current user is the one who received it
            ->whereNull('parent_id')         // It's the head of the thread
            ->with([
                'sender:id,first_name,last_name',
                'children' => function($query) {
                    $query->with('sender:id,first_name,last_name')->oldest();
                }
            ])
            ->latest() // Get the most recent one if multiple exist
            ->first();

        if (!$conversation) {
            return response()->json([
                'statusCode' => 404, 
                'message' => "No private conversation found for you regarding this patient history."
            ], 404);
        }

        // 2. Construct the response
        return response()->json([
            "statusCode"         => 200,
            "data" => [
                "patient_history_id" => (int) $patientHistoryId,
                "conversation_id"    => $conversation->conversation_id,
                "user_id"            => $conversation->sender->id, // The person who started it
                "sender_full_name"   => $conversation->sender->full_name,
                "message"            => $conversation->message,
                "date"               => $conversation->created_at->diffForHumans(),
                "replies"            => $conversation->children->map(function ($reply) {
                    return [
                        "conversation_id"    => $reply->conversation_id,
                        "user_id"            => $reply->sender_id,
                        "sender_full_name"   => $reply->sender->full_name,
                        "message"            => $reply->message,
                        "date"               => $reply->created_at->diffForHumans(),
                    ];
                })
            ]
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

