<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class AdminCountryController extends Controller
{
    public function index()
    {
        return response()->json(
            Country::with('proposedByUser:id,nickname,email')
                ->withCount('locations')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name',
            'iso_code' => 'required|string|size:2|unique:countries,iso_code',
            'status' => 'required|in:pending,approved,rejected',
        ], [
            'name.unique' => 'Dieser Ländername existiert bereits.',
            'iso_code.unique' => 'Dieser ISO-Code existiert bereits.',
            'iso_code.size' => 'Der ISO-Code muss genau 2 Zeichen haben.',
        ]);

        $country = Country::create([
            'name' => $request->name,
            'iso_code' => strtoupper($request->iso_code),
            'status' => $request->status,
        ]);

        return response()->json($country, 201);
    }

    public function update(Request $request, Country $country)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name,' . $country->id,
            'iso_code' => 'required|string|size:2|unique:countries,iso_code,' . $country->id,
            'status' => 'required|in:pending,approved,rejected',
        ], [
            'name.unique' => 'Dieser Ländername existiert bereits.',
            'iso_code.unique' => 'Dieser ISO-Code existiert bereits.',
            'iso_code.size' => 'Der ISO-Code muss genau 2 Zeichen haben.',
        ]);

        $country->update([
            'name' => $request->name,
            'iso_code' => strtoupper($request->iso_code),
            'status' => $request->status,
        ]);

        return response()->json($country);
    }

    public function destroy(Country $country)
    {
        if ($country->locations()->exists()) {
            return response()->json([
                'message' => 'Dieses Land kann nicht gelöscht werden, da noch Standorte damit verknüpft sind.'
            ], 422);
        }

        $country->delete();

        return response()->json(['message' => 'Land gelöscht.']);
    }
}

