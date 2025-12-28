<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegionalPartner;
use Illuminate\Http\Request;

class AdminRegionalPartnerController extends Controller
{
    public function index()
    {
        return response()->json(
            RegionalPartner::withCount('locations')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regional_partners,name',
        ], [
            'name.unique' => 'Dieser Regionalpartner-Name existiert bereits.',
        ]);

        $regionalPartner = RegionalPartner::create([
            'name' => $request->name,
        ]);

        return response()->json($regionalPartner, 201);
    }

    public function update(Request $request, RegionalPartner $regionalPartner)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regional_partners,name,' . $regionalPartner->id,
        ], [
            'name.unique' => 'Dieser Regionalpartner-Name existiert bereits.',
        ]);

        $regionalPartner->update([
            'name' => $request->name,
        ]);

        return response()->json($regionalPartner);
    }

    public function destroy(RegionalPartner $regionalPartner)
    {
        if ($regionalPartner->locations()->exists()) {
            return response()->json([
                'message' => 'Dieser Regionalpartner kann nicht gelöscht werden, da noch Standorte damit verknüpft sind.'
            ], 422);
        }

        $regionalPartner->delete();

        return response()->json(['message' => 'Regionalpartner gelöscht.']);
    }
}

