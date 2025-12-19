<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSeasonController extends Controller
{
    public function index()
    {
        return response()->json(
            Season::with('firstProgram:id,name')
                ->withCount(['events', 'badges'])
                ->orderBy('start_year', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:1998|max:2100',
            'first_program_id' => 'required|exists:first_programs,id',
        ], [
            'name.required' => 'Der Name ist erforderlich.',
        ]);

        // Check composite unique constraints
        $exists = Season::where('first_program_id', $request->first_program_id)
            ->where(function ($q) use ($request) {
                $q->where('name', $request->name)
                  ->orWhere('start_year', $request->start_year);
            })->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Diese Saison existiert bereits für dieses Programm.'
            ], 422);
        }

        $season = Season::create($request->only(['name', 'start_year', 'first_program_id']));

        return response()->json($season, 201);
    }

    public function update(Request $request, Season $season)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:1998|max:2100',
            'first_program_id' => 'required|exists:first_programs,id',
        ]);

        // Check composite unique constraints (excluding current record)
        $exists = Season::where('first_program_id', $request->first_program_id)
            ->where('id', '!=', $season->id)
            ->where(function ($q) use ($request) {
                $q->where('name', $request->name)
                  ->orWhere('start_year', $request->start_year);
            })->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Diese Saison existiert bereits für dieses Programm.'
            ], 422);
        }

        $season->update($request->only(['name', 'start_year', 'first_program_id']));

        return response()->json($season);
    }

    public function destroy(Season $season)
    {
        if ($season->events()->exists()) {
            return response()->json([
                'message' => 'Diese Saison kann nicht gelöscht werden, da noch Veranstaltungen damit verknüpft sind.'
            ], 422);
        }

        $season->delete();

        return response()->json(['message' => 'Saison gelöscht.']);
    }

    public function uploadLogo(Request $request, Season $season)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        // Delete old logo if exists
        if ($season->logo_path && Storage::disk('public')->exists($season->logo_path)) {
            Storage::disk('public')->delete($season->logo_path);
        }

        // Get file extension
        $extension = $request->file('logo')->getClientOriginalExtension();
        
        // Rename to {id}.{ext}
        $filename = $season->id . '.' . $extension;
        $path = 'logos/seasons/' . $filename;

        // Store file
        $request->file('logo')->storeAs('logos/seasons', $filename, 'public');

        // Update database
        $season->update(['logo_path' => $path]);

        return response()->json([
            'logo_path' => $path,
            'logo_url' => Storage::url($path),
        ]);
    }

    public function deleteLogo(Season $season)
    {
        if ($season->logo_path && Storage::disk('public')->exists($season->logo_path)) {
            Storage::disk('public')->delete($season->logo_path);
        }

        $season->update(['logo_path' => null]);

        return response()->json(['message' => 'Logo gelöscht.']);
    }
}

