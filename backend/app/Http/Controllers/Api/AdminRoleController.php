<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\FirstProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminRoleController extends Controller
{
    public function index()
    {
        return response()->json(
            Role::with(['firstProgram:id,name', 'proposedByUser:id,nickname,email'])
                ->withCount('engagements')
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'first_program_id' => 'required|exists:first_programs,id',
            'role_category' => 'nullable|in:volunteer,regional_partner,coach',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $maxSortOrder = Role::max('sort_order') ?? 0;

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $maxSortOrder + 1,
            'first_program_id' => $request->first_program_id,
            'role_category' => $request->role_category,
            'status' => $request->status,
        ]);

        return response()->json($role->load(['firstProgram:id,name']), 201);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'first_program_id' => 'required|exists:first_programs,id',
            'role_category' => 'nullable|in:volunteer,regional_partner,coach',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'first_program_id' => $request->first_program_id,
            'role_category' => $request->role_category,
            'status' => $request->status,
        ]);

        return response()->json($role->load(['firstProgram:id,name']));
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id',
        ]);

        foreach ($request->ids as $index => $id) {
            Role::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Reihenfolge aktualisiert.']);
    }

    public function destroy(Role $role)
    {
        if ($role->engagements()->exists()) {
            return response()->json([
                'message' => 'Diese Rolle kann nicht gelöscht werden, da noch Einsätze damit verknüpft sind.'
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Rolle gelöscht.']);
    }

    public function programs()
    {
        return response()->json(
            FirstProgram::orderBy('sort_order')->get(['id', 'name'])
        );
    }

    public function uploadLogo(Request $request, Role $role)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        // Delete old logo if exists
        if ($role->logo_path && Storage::disk('public')->exists($role->logo_path)) {
            Storage::disk('public')->delete($role->logo_path);
        }

        // Get file extension
        $extension = $request->file('logo')->getClientOriginalExtension();
        
        // Rename to {id}.{ext}
        $filename = $role->id . '.' . $extension;
        $path = 'logos/roles/' . $filename;

        // Store file
        $request->file('logo')->storeAs('logos/roles', $filename, 'public');

        // Update database
        $role->update(['logo_path' => $path]);

        return response()->json([
            'logo_path' => $path,
            'logo_url' => Storage::url($path),
        ]);
    }

    public function deleteLogo(Role $role)
    {
        if ($role->logo_path && Storage::disk('public')->exists($role->logo_path)) {
            Storage::disk('public')->delete($role->logo_path);
        }

        $role->update(['logo_path' => null]);

        return response()->json(['message' => 'Logo gelöscht.']);
    }
}

