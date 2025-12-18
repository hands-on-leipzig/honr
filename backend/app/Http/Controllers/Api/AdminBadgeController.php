<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\FirstProgram;
use App\Models\Season;
use App\Models\Level;
use App\Models\Country;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminBadgeController extends Controller
{
    public function index()
    {
        return response()->json(
            Badge::with([
                'firstProgram:id,name',
                'season:id,name',
                'level:id,name',
                'country:id,name',
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
            'type' => 'required|in:tick_box,grow',
            'status' => 'required|in:pending_icon,released',
            'icon_path' => 'nullable|string|max:255',
            'first_program_id' => 'nullable|exists:first_programs,id',
            'season_id' => 'nullable|exists:seasons,id',
            'level_id' => 'nullable|exists:levels,id',
            'country_id' => 'nullable|exists:countries,id',
            'role_id' => 'nullable|exists:roles,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        // Validate exactly one criteria is set
        $criteria = array_filter([
            $request->first_program_id,
            $request->season_id,
            $request->level_id,
            $request->country_id,
            $request->role_id,
        ]);

        if (count($criteria) !== 1) {
            return response()->json([
                'message' => 'Genau ein Kriterium muss gesetzt sein (Programm, Saison, Level, Land oder Rolle).'
            ], 422);
        }

        // Grow badges must have role_id
        if ($request->type === 'grow' && !$request->role_id) {
            return response()->json([
                'message' => 'Grow-Badges müssen eine Rolle als Kriterium haben.'
            ], 422);
        }

        $badge = Badge::create($request->only([
            'name', 'type', 'status', 'icon_path',
            'first_program_id', 'season_id', 'level_id', 'country_id', 'role_id',
            'description', 'sort_order'
        ]));

        return response()->json($badge->load([
            'firstProgram:id,name',
            'season:id,name',
            'level:id,name',
            'country:id,name',
            'role:id,name',
        ]), 201);
    }

    public function update(Request $request, Badge $badge)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:tick_box,grow',
            'status' => 'required|in:pending_icon,released',
            'icon_path' => 'nullable|string|max:255',
            'first_program_id' => 'nullable|exists:first_programs,id',
            'season_id' => 'nullable|exists:seasons,id',
            'level_id' => 'nullable|exists:levels,id',
            'country_id' => 'nullable|exists:countries,id',
            'role_id' => 'nullable|exists:roles,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        // Validate exactly one criteria is set
        $criteria = array_filter([
            $request->first_program_id,
            $request->season_id,
            $request->level_id,
            $request->country_id,
            $request->role_id,
        ]);

        if (count($criteria) !== 1) {
            return response()->json([
                'message' => 'Genau ein Kriterium muss gesetzt sein (Programm, Saison, Level, Land oder Rolle).'
            ], 422);
        }

        // Grow badges must have role_id
        if ($request->type === 'grow' && !$request->role_id) {
            return response()->json([
                'message' => 'Grow-Badges müssen eine Rolle als Kriterium haben.'
            ], 422);
        }

        $badge->update($request->only([
            'name', 'type', 'status', 'icon_path',
            'first_program_id', 'season_id', 'level_id', 'country_id', 'role_id',
            'description', 'sort_order'
        ]));

        return response()->json($badge->load([
            'firstProgram:id,name',
            'season:id,name',
            'level:id,name',
            'country:id,name',
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
            'programs' => FirstProgram::orderBy('sort_order')->get(['id', 'name']),
            'seasons' => Season::orderBy('start_year', 'desc')->get(['id', 'name']),
            'levels' => Level::where('status', 'approved')->orderBy('sort_order')->get(['id', 'name']),
            'countries' => Country::where('status', 'approved')->orderBy('name')->get(['id', 'name']),
            'roles' => Role::where('status', 'approved')->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }
}

