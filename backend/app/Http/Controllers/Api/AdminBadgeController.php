<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminBadgeController extends Controller
{
    public function index()
    {
        return response()->json(
            Badge::with([
                'role:id,name',
            ])
                ->withCount(['thresholds', 'earnedBadges'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:pending_icon,released',
            'role_id' => 'required|exists:roles,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $badge = Badge::create($request->only([
            'name', 'status', 'role_id', 'description', 'sort_order'
        ]));

        return response()->json($badge->load([
            'role:id,name',
        ]), 201);
    }

    public function update(Request $request, Badge $badge)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:pending_icon,released',
            'role_id' => 'required|exists:roles,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $badge->update($request->only([
            'name', 'status', 'role_id', 'description', 'sort_order'
        ]));

        return response()->json($badge->load([
            'role:id,name',
        ]));
    }

    public function destroy(Badge $badge)
    {
        if ($badge->earnedBadges()->exists()) {
            return response()->json([
                'message' => 'Dieses Badge kann nicht gelöscht werden, da es bereits von Benutzern verdient wurde.'
            ], 422);
        }

        if ($badge->thresholds()->exists()) {
            return response()->json([
                'message' => 'Dieses Badge kann nicht gelöscht werden, da noch Schwellenwerte damit verknüpft sind.'
            ], 422);
        }

        $badge->delete();
        return response()->json(['message' => 'Badge gelöscht.']);
    }

    public function options()
    {
        return response()->json([
            'roles' => Role::where('status', 'approved')->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }
}

