<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FirstProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminFirstProgramController extends Controller
{
    public function index()
    {
        return response()->json(
            FirstProgram::withCount(['seasons', 'roles', 'events', 'badges'])
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:first_programs,name',
            'sort_order' => 'required|integer',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ], [
            'name.unique' => 'Dieser Programmname existiert bereits.',
        ]);

        $program = FirstProgram::create($request->only(['name', 'sort_order', 'valid_from', 'valid_to']));

        return response()->json($program, 201);
    }

    public function update(Request $request, FirstProgram $firstProgram)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:first_programs,name,' . $firstProgram->id,
            'sort_order' => 'required|integer',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ], [
            'name.unique' => 'Dieser Programmname existiert bereits.',
        ]);

        $firstProgram->update($request->only(['name', 'sort_order', 'valid_from', 'valid_to']));

        return response()->json($firstProgram);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'programs' => 'required|array',
            'programs.*.id' => 'required|exists:first_programs,id',
            'programs.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->programs as $item) {
            FirstProgram::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Reihenfolge aktualisiert.']);
    }

    public function destroy(FirstProgram $firstProgram)
    {
        // Check for dependent records
        if ($firstProgram->seasons()->exists()) {
            return response()->json([
                'message' => 'Dieses Programm kann nicht gelöscht werden, da noch Saisons damit verknüpft sind.'
            ], 422);
        }

        if ($firstProgram->roles()->exists()) {
            return response()->json([
                'message' => 'Dieses Programm kann nicht gelöscht werden, da noch Rollen damit verknüpft sind.'
            ], 422);
        }

        if ($firstProgram->events()->exists()) {
            return response()->json([
                'message' => 'Dieses Programm kann nicht gelöscht werden, da noch Veranstaltungen damit verknüpft sind.'
            ], 422);
        }

        if ($firstProgram->badges()->exists()) {
            return response()->json([
                'message' => 'Dieses Programm kann nicht gelöscht werden, da noch Badges damit verknüpft sind.'
            ], 422);
        }

        $firstProgram->delete();

        return response()->json(['message' => 'Programm gelöscht.']);
    }

    public function uploadLogo(Request $request, FirstProgram $firstProgram)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        // Delete old logo if exists
        if ($firstProgram->logo_path && Storage::disk('public')->exists($firstProgram->logo_path)) {
            Storage::disk('public')->delete($firstProgram->logo_path);
        }

        // Get file extension
        $extension = $request->file('logo')->getClientOriginalExtension();
        
        // Rename to {id}.{ext}
        $filename = $firstProgram->id . '.' . $extension;
        $path = 'logos/first_programs/' . $filename;

        // Store file
        $request->file('logo')->storeAs('logos/first_programs', $filename, 'public');

        // Update database
        $firstProgram->update(['logo_path' => $path]);

        return response()->json([
            'logo_path' => $path,
            'logo_url' => Storage::url($path),
        ]);
    }

    public function deleteLogo(FirstProgram $firstProgram)
    {
        if ($firstProgram->logo_path && Storage::disk('public')->exists($firstProgram->logo_path)) {
            Storage::disk('public')->delete($firstProgram->logo_path);
        }

        $firstProgram->update(['logo_path' => null]);

        return response()->json(['message' => 'Logo gelöscht.']);
    }
}

