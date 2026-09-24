<?php

namespace App\Http\Controllers\API\Followups;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use App\Support\Pagination;

class FollowupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View FollowUp|Create FollowUp|Update FollowUp|Delete FollowUp', ['only' => ['index','store','show','update','destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('View FollowUp')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $search = trim((string) $request->input('search', ''));
        $followups = FollowUp::with([
                'patient:patient_id,name,phone',
                'hospitalLetter:letter_id,referral_id,outcome,next_appointment_date',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $term = mb_strtolower($search);
                $query->where(function ($query) use ($term): void {
                    $query->whereHas('patient', function ($patientQuery) use ($term): void {
                        $patientQuery->whereRaw('LOWER(name) LIKE ?', [$term.'%'])
                            ->orWhereRaw('LOWER(phone) LIKE ?', [$term.'%']);
                    })->orWhereHas('hospitalLetter.referral', function ($referralQuery) use ($term): void {
                        $referralQuery->whereRaw('LOWER(referral_number) LIKE ?', [$term.'%']);
                    });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('followup_status', $request->input('status'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request): void {
                $query->whereDate('followup_date', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request): void {
                $query->whereDate('followup_date', '<=', $request->input('date_to'));
            })
            ->latest('followup_id')
            ->paginate(Pagination::perPage($request, 25));

        return response()->json([
            'data' => $followups->items(),
            'meta' => Pagination::meta($followups),
            'statusCode' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create FollowUp')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validated = $request->validate([
            'letter_id' => ['required','exists:hospital_letters,letter_id'],
            'patient_id' => ['required','exists:patients,patient_id'],
            'followup_date' => ['required','date'],
            'notes' => ['nullable','string'],
            'followup_status' => ['nullable','in:Ongoing,Closed,Transferred'],
            'status' => ['nullable','in:Ongoing,Closed,Transferred'],
        ]);

        $validated['followup_status'] ??= $validated['status'] ?? 'Ongoing';
        unset($validated['status']);

        $followup = FollowUp::create($validated);

        return response()->json([
            'message' => 'FollowUp created successfully',
            'data' => $followup,
            'statusCode' => 201
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->can('View FollowUp')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $followup = FollowUp::with([
            'patient:patient_id,name,phone',
            'hospitalLetter:letter_id,referral_id,outcome,next_appointment_date',
        ])->find($id);

        if (!$followup) {
            return response()->json([
                'message' => 'FollowUp not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $followup,
            'statusCode' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->can('Update FollowUp')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $followup = FollowUp::find($id);

        if (!$followup) {
            return response()->json([
                'message' => 'FollowUp not found',
                'statusCode' => 404
            ], 404);
        }

        $validated = $request->validate([
            'letter_id' => ['sometimes','exists:hospital_letters,letter_id'],
            'patient_id' => ['sometimes','exists:patients,patient_id'],
            'followup_date' => ['sometimes','date'],
            'notes' => ['nullable','string'],
            'followup_status' => ['sometimes','in:Ongoing,Closed,Transferred'],
            'status' => ['sometimes','in:Ongoing,Closed,Transferred'],
        ]);

        if (array_key_exists('status', $validated) && ! array_key_exists('followup_status', $validated)) {
            $validated['followup_status'] = $validated['status'];
        }
        unset($validated['status']);

        $followup->update($validated);

        return response()->json([
            'message' => 'FollowUp updated successfully',
            'data' => $followup,
            'statusCode' => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->can('Delete FollowUp')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $followup = FollowUp::find($id);

        if (!$followup) {
            return response()->json([
                'message' => 'FollowUp not found',
                'statusCode' => 404
            ], 404);
        }

        $followup->delete();

        return response()->json([
            'message' => 'FollowUp deleted successfully',
            'statusCode' => 200
        ]);
    }
}
