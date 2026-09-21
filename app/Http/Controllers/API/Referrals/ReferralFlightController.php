<?php

namespace App\Http\Controllers\API\Referrals;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Models\Referral;
use App\Models\ReferralFlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralFlightController extends Controller
{
    /**
     * Store flight information for a referral.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'referral_id' => [
                'required',
                'integer',
                'exists:referrals,referral_id',
            ],

            'arrival_date' => [
                'nullable',
                'date',
            ],

            'arrival_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'arrival_airport' => [
                'nullable',
                'string',
                'max:255',
            ],

            'airline' => [
                'nullable',
                'string',
                'max:255',
            ],

            'flight_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'departure_city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'departure_airport' => [
                'nullable',
                'string',
                'max:255',
            ],

            'arrival_city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'terminal' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        try {
            DB::beginTransaction();

            // Add logged-in user
            $validated['created_by'] = Auth::id();

            $flight = ReferralFlight::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Flight information saved successfully.',
                'data' => $flight->load('referral'),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return Helper::serverError($e, 'Failed to save flight information.');
        }
    }

    /**
     * Get flight information by referral.
     */
    public function showByReferral($referralId)
    {
        $flight = ReferralFlight::where(
            'referral_id',
            $referralId
        )
            ->with('referral')
            ->latest('referral_flight_id')
            ->first();

        if (! $flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight information not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $flight,
        ]);
    }

    /**
     * Get a specific flight record.
     */
    public function show($id)
    {
        $flight = ReferralFlight::with('referral')->find($id);

        if (! $flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight information not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $flight,
        ]);
    }

    /**
     * Update flight information.
     */
    public function update(Request $request, $id)
    {
        $flight = ReferralFlight::find($id);

        if (! $flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight information not found.',
            ], 404);
        }

        $validated = $request->validate([
            'arrival_date' => [
                'nullable',
                'date',
            ],

            'arrival_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'arrival_airport' => [
                'nullable',
                'string',
                'max:255',
            ],

            'airline' => [
                'nullable',
                'string',
                'max:255',
            ],

            'flight_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'departure_city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'departure_airport' => [
                'nullable',
                'string',
                'max:255',
            ],

            'arrival_city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'terminal' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $flight->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Flight information updated successfully.',
            'data' => $flight->fresh(),
        ]);
    }

    /**
     * Delete flight information.
     */
    public function destroy($id)
    {
        $flight = ReferralFlight::find($id);

        if (! $flight) {
            return response()->json([
                'success' => false,
                'message' => 'Flight information not found.',
            ], 404);
        }

        $flight->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flight information deleted successfully.',
        ]);
    }
}
