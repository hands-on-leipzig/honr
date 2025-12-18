<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Event;
use App\Models\Engagement;
use App\Models\FirstProgram;
use App\Models\Season;
use App\Models\Level;
use App\Models\Location;
use App\Models\Country;
use Illuminate\Http\Request;

class EngagementController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Engagement::where('user_id', $request->user()->id)
                ->with([
                    'role:id,name,first_program_id,status',
                    'role.firstProgram:id,name',
                    'event:id,date,season_id,level_id,location_id,status',
                    'event.season:id,name',
                    'event.level:id,name',
                    'event.location:id,name,city',
                ])
                ->join('events', 'engagements.event_id', '=', 'events.id')
                ->orderBy('events.date', 'desc')
                ->select('engagements.*')
                ->get()
        );
    }

    public function options(Request $request)
    {
        $userId = $request->user()->id;

        $roles = Role::where(function ($q) use ($userId) {
                $q->where('status', 'approved')
                  ->orWhere('proposed_by_user_id', $userId);
            })
            ->with('firstProgram:id,name')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'first_program_id', 'status', 'proposed_by_user_id'])
            ->map(function ($role) use ($userId) {
                if ($role->proposed_by_user_id === $userId && $role->status !== 'approved') {
                    $role->name = $role->name . ' (von dir vorgeschlagen)';
                }
                return $role;
            });

        $events = Event::where(function ($q) use ($userId) {
                $q->where('status', 'approved')
                  ->orWhere('proposed_by_user_id', $userId);
            })
            ->with(['season:id,name', 'level:id,name', 'location:id,name,city'])
            ->orderBy('date', 'desc')
            ->get(['id', 'date', 'season_id', 'level_id', 'location_id', 'status', 'proposed_by_user_id'])
            ->map(function ($event) use ($userId) {
                if ($event->proposed_by_user_id === $userId && $event->status !== 'approved') {
                    $event->user_proposed = true;
                }
                return $event;
            });

        return response()->json([
            'roles' => $roles,
            'events' => $events,
            'programs' => FirstProgram::orderBy('sort_order')->get(['id', 'name']),
            'seasons' => Season::orderBy('start_year', 'desc')->get(['id', 'name']),
            'levels' => Level::where('status', 'approved')->orderBy('sort_order')->get(['id', 'name']),
            'locations' => Location::where('status', 'approved')->orderBy('name')->get(['id', 'name', 'city']),
            'countries' => Country::where('status', 'approved')->orderBy('name')->get(['id', 'name']),
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

    public function proposeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'first_program_id' => 'required|exists:first_programs,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'first_program_id' => $request->first_program_id,
            'status' => 'pending',
            'proposed_by_user_id' => $request->user()->id,
            'sort_order' => (Role::max('sort_order') ?? 0) + 1,
        ]);

        return response()->json($role, 201);
    }

    public function proposeEvent(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'season_id' => 'required|exists:seasons,id',
            'level_id' => 'required|exists:levels,id',
            'location_id' => 'required|exists:locations,id',
        ]);

        // Get the first_program_id from the season
        $season = Season::find($request->season_id);

        $event = Event::create([
            'date' => $request->date,
            'first_program_id' => $season->first_program_id,
            'season_id' => $request->season_id,
            'level_id' => $request->level_id,
            'location_id' => $request->location_id,
            'status' => 'pending',
            'proposed_by_user_id' => $request->user()->id,
        ]);

        return response()->json($event, 201);
    }

    public function proposeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
        ]);

        $location = Location::create([
            'name' => $request->name,
            'city' => $request->city,
            'country_id' => $request->country_id,
            'status' => 'pending',
            'proposed_by_user_id' => $request->user()->id,
        ]);

        return response()->json($location, 201);
    }
}

