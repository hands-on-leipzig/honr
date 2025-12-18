<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Country;
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

        $location->update($request->only([
            'name', 'country_id', 'street_address', 'city', 'postal_code',
            'latitude', 'longitude', 'status'
        ]));

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
            Country::where('status', 'approved')
                ->orderBy('name')
                ->get(['id', 'name', 'iso_code'])
        );
    }
}

