<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeatmapController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('engagements')
            ->join('events', 'engagements.event_id', '=', 'events.id')
            ->join('locations', 'events.location_id', '=', 'locations.id')
            ->where('engagements.is_recognized', true)
            ->whereNotNull('locations.latitude')
            ->whereNotNull('locations.longitude');

        // Optional filters
        if ($request->filled('program_id')) {
            $query->where('events.first_program_id', $request->program_id);
        }
        if ($request->filled('season_id')) {
            $query->where('events.season_id', $request->season_id);
        }
        if ($request->filled('level_id')) {
            $query->where('events.level_id', $request->level_id);
        }

        $data = $query->select(
                'locations.id',
                'locations.name',
                'locations.city',
                'locations.latitude',
                'locations.longitude',
                DB::raw('COUNT(engagements.id) as engagement_count')
            )
            ->groupBy('locations.id', 'locations.name', 'locations.city', 'locations.latitude', 'locations.longitude')
            ->orderByDesc('engagement_count')
            ->get();

        return response()->json($data);
    }

    public function options()
    {
        return response()->json([
            'programs' => DB::table('first_programs')->orderBy('sort_order')->get(['id', 'name']),
            'seasons' => DB::table('seasons')->orderBy('start_year', 'desc')->get(['id', 'name', 'first_program_id']),
            'levels' => DB::table('levels')->where('status', 'approved')->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }
}


