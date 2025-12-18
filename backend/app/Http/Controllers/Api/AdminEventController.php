<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FirstProgram;
use App\Models\Season;
use App\Models\Level;
use App\Models\Location;
use Illuminate\Http\Request;

class AdminEventController extends Controller
{
    public function index()
    {
        return response()->json(
            Event::with([
                'firstProgram:id,name',
                'season:id,name,start_year',
                'level:id,name',
                'location:id,name,city',
                'proposedByUser:id,nickname,email'
            ])
                ->withCount('engagements')
                ->orderBy('date', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_program_id' => 'required|exists:first_programs,id',
            'season_id' => 'required|exists:seasons,id',
            'level_id' => 'required|exists:levels,id',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        // Check for duplicate
        $exists = Event::where('first_program_id', $request->first_program_id)
            ->where('season_id', $request->season_id)
            ->where('level_id', $request->level_id)
            ->where('location_id', $request->location_id)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Diese Veranstaltung existiert bereits (gleiche Kombination aus Programm, Saison, Level, Standort und Datum).'
            ], 422);
        }

        $event = Event::create($request->only([
            'first_program_id', 'season_id', 'level_id', 'location_id', 'date', 'status'
        ]));

        return response()->json($event->load([
            'firstProgram:id,name',
            'season:id,name,start_year',
            'level:id,name',
            'location:id,name,city'
        ]), 201);
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'first_program_id' => 'required|exists:first_programs,id',
            'season_id' => 'required|exists:seasons,id',
            'level_id' => 'required|exists:levels,id',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        // Check for duplicate (excluding current)
        $exists = Event::where('first_program_id', $request->first_program_id)
            ->where('season_id', $request->season_id)
            ->where('level_id', $request->level_id)
            ->where('location_id', $request->location_id)
            ->where('date', $request->date)
            ->where('id', '!=', $event->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Diese Veranstaltung existiert bereits (gleiche Kombination aus Programm, Saison, Level, Standort und Datum).'
            ], 422);
        }

        $event->update($request->only([
            'first_program_id', 'season_id', 'level_id', 'location_id', 'date', 'status'
        ]));

        return response()->json($event->load([
            'firstProgram:id,name',
            'season:id,name,start_year',
            'level:id,name',
            'location:id,name,city'
        ]));
    }

    public function destroy(Event $event)
    {
        if ($event->engagements()->exists()) {
            return response()->json([
                'message' => 'Diese Veranstaltung kann nicht gelöscht werden, da noch Einsätze damit verknüpft sind.'
            ], 422);
        }

        $event->delete();

        return response()->json(['message' => 'Veranstaltung gelöscht.']);
    }

    public function options()
    {
        return response()->json([
            'programs' => FirstProgram::orderBy('sort_order')->get(['id', 'name']),
            'seasons' => Season::orderBy('start_year', 'desc')->get(['id', 'name', 'start_year', 'first_program_id']),
            'levels' => Level::where('status', 'approved')->orderBy('sort_order')->get(['id', 'name']),
            'locations' => Location::where('status', 'approved')->orderBy('name')->get(['id', 'name', 'city']),
        ]);
    }
}

