<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;

class AdminLevelController extends Controller
{
    public function index()
    {
        return response()->json(
            Level::with('proposedByUser:id,nickname,email')
                ->withCount('events')
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:levels,name',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'required|integer',
            'status' => 'required|in:pending,approved,rejected',
        ], [
            'name.unique' => 'Dieser Level-Name existiert bereits.',
        ]);

        $level = Level::create($request->only(['name', 'description', 'sort_order', 'status']));

        return response()->json($level, 201);
    }

    public function update(Request $request, Level $level)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:levels,name,' . $level->id,
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'required|integer',
            'status' => 'required|in:pending,approved,rejected',
        ], [
            'name.unique' => 'Dieser Level-Name existiert bereits.',
        ]);

        $level->update($request->only(['name', 'description', 'sort_order', 'status']));

        return response()->json($level);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:levels,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            Level::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Reihenfolge aktualisiert.']);
    }

    public function destroy(Level $level)
    {
        if ($level->events()->exists()) {
            return response()->json([
                'message' => 'Dieses Level kann nicht gelöscht werden, da noch Veranstaltungen damit verknüpft sind.'
            ], 422);
        }

        if ($level->badges()->exists()) {
            return response()->json([
                'message' => 'Dieses Level kann nicht gelöscht werden, da noch Badges damit verknüpft sind.'
            ], 422);
        }

        $level->delete();

        return response()->json(['message' => 'Level gelöscht.']);
    }
}

