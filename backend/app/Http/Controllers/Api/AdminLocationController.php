<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Country;
use App\Services\ApprovalValidationService;
use Illuminate\Http\Request;

class AdminLocationController extends Controller
{
    public function index()
    {
        return response()->json(
            Location::with(['country:id,name,iso_code', 'proposedByUser:id,nickname,email'])
                ->withCount('events')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'country_id' => 'required|exists:countries,id',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:pending,approved,rejected',
        ], [
            'name.unique' => 'Dieser Standortname existiert bereits.',
        ]);

        // Validate approval dependencies if trying to approve
        if ($request->status === 'approved') {
            $country = Country::find($request->country_id);
            if ($country && $country->status !== 'approved') {
                return response()->json([
                    'message' => "Das Land '{$country->name}' muss zuerst genehmigt werden."
                ], 422);
            }
        }

        $location = Location::create($request->only([
            'name', 'country_id', 'street_address', 'city', 'postal_code',
            'latitude', 'longitude', 'status'
        ]));

        return response()->json($location->load(['country:id,name,iso_code']), 201);
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'country_id' => 'required|exists:countries,id',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'required|in:pending,approved,rejected',
        ], [
            'name.unique' => 'Dieser Standortname existiert bereits.',
        ]);

        // Validate approval dependencies
        $oldStatus = $location->status;
        $newStatus = $request->status;
        $oldCountryId = $location->country_id;

        // If trying to approve, check dependencies with NEW country
        if ($oldStatus !== 'approved' && $newStatus === 'approved') {
            $newCountry = Country::find($request->country_id);
            if (!$newCountry || $newCountry->status !== 'approved') {
                return response()->json([
                    'message' => "Das Land '{$newCountry->name}' muss zuerst genehmigt werden."
                ], 422);
            }
        }

        $location->update($request->only([
            'name', 'country_id', 'street_address', 'city', 'postal_code',
            'latitude', 'longitude', 'status'
        ]));

        // If location status changed or country changed, we need to update related events
        // But events don't auto-update, so we just validate on approval
        // The events will be validated when someone tries to approve them

        return response()->json($location->load(['country:id,name,iso_code']));
    }

    public function destroy(Location $location)
    {
        if ($location->events()->exists()) {
            return response()->json([
                'message' => 'Dieser Standort kann nicht gelöscht werden, da noch Veranstaltungen damit verknüpft sind.'
            ], 422);
        }

        $location->delete();

        return response()->json(['message' => 'Standort gelöscht.']);
    }

    public function countries()
    {
        return response()->json(
            Country::orderBy('name')
                ->get(['id', 'name', 'iso_code', 'status'])
        );
    }
}

