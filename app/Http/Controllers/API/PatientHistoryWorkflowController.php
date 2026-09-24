<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PatientHistoryWorkflowEvent;
use App\Services\PatientHistoryWorkflowService;
use App\Support\Pagination;
use App\Support\SuperAdminAccess;
use Illuminate\Http\Request;
use RuntimeException;

class PatientHistoryWorkflowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request, int $patientHistoryId)
    {
        if (! SuperAdminAccess::allowed(auth()->user(), 'Undo Patient History Workflow')) {
            return response()->json(['message' => 'Forbidden', 'statusCode' => 403], 403);
        }

        $events = PatientHistoryWorkflowEvent::query()
            ->with([
                'actor:id,first_name,middle_name,last_name,email',
                'undoneBy:id,first_name,middle_name,last_name,email',
            ])
            ->where('patient_histories_id', $patientHistoryId)
            ->latest('id')
            ->paginate(Pagination::perPage($request, 50));

        return response()->json([
            'data' => $events->items(),
            'meta' => Pagination::meta($events),
            'statusCode' => 200,
        ]);
    }

    public function undo(Request $request, int $eventId, PatientHistoryWorkflowService $workflow)
    {
        if (! SuperAdminAccess::allowed(auth()->user(), 'Undo Patient History Workflow')) {
            return response()->json(['message' => 'Forbidden', 'statusCode' => 403], 403);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        try {
            $event = $workflow->undo($eventId, auth()->user(), $validated['reason']);

            return response()->json([
                'message' => 'Workflow transition undone successfully.',
                'data' => $event,
                'statusCode' => 200,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'statusCode' => 422,
            ], 422);
        }
    }
}
