<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Event;
use App\Models\Engagement;
use Illuminate\Http\Request;

class EngagementController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Engagement::where('user_id', $request->user()->id)
                ->with([
                    'role:id,name,first_program_id',
                    'role.firstProgram:id,name',
                    'event:id,date,season_id,level_id,location_id',
                    'event.season:id,name',
                    'event.level:id,name',
                    'event.location:id,name,city',
                ])
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function options()
    {
        return response()->json([
            'roles' => Role::where('status', 'approved')
                ->with('firstProgram:id,name')
                ->orderBy('sort_order')
                ->get(['id', 'name', 'first_program_id']),
            'events' => Event::where('status', 'approved')
                ->with(['season:id,name', 'level:id,name', 'location:id,name,city'])
                ->orderBy('date', 'desc')
                ->get(['id', 'date', 'season_id', 'level_id', 'location_id']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'event_id' => 'required|exists:events,id',
        ]);

        $user = $request->user();

        // Check for duplicate
        $exists = Engagement::where('user_id', $user->id)
            ->where('role_id', $request->role_id)
            ->where('event_id', $request->event_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Dieser Einsatz wurde bereits erfasst.'
            ], 422);
        }

        // Check if should be recognized
        $role = Role::find($request->role_id);
        $event = Event::find($request->event_id);

        $isRecognized = $user->status === 'active'
            && $role->status === 'approved'
            && $event->status === 'approved';

        $engagement = Engagement::create([
            'user_id' => $user->id,
            'role_id' => $request->role_id,
            'event_id' => $request->event_id,
            'is_recognized' => $isRecognized,
            'recognized_at' => $isRecognized ? now() : null,
        ]);

        return response()->json($engagement->load([
            'role:id,name,first_program_id',
            'role.firstProgram:id,name',
            'event:id,date,season_id,level_id,location_id',
            'event.season:id,name',
            'event.level:id,name',
            'event.location:id,name,city',
        ]), 201);
    }

    public function destroy(Request $request, Engagement $engagement)
    {
        // Ensure user owns this engagement
        if ($engagement->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Nicht autorisiert.'], 403);
        }

        $engagement->delete();

        return response()->json(['message' => 'Einsatz gelöscht.']);
    }
}

