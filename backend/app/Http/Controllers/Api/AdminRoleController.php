<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\FirstProgram;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    public function index()
    {
        return response()->json(
            Role::with(['firstProgram:id,name', 'proposedByUser:id,nickname,email'])
                ->withCount(['engagements', 'badges'])
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

        if ($role->badges()->exists()) {
            return response()->json([
                'message' => 'Diese Rolle kann nicht gelöscht werden, da noch Badges damit verknüpft sind.'
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
}

