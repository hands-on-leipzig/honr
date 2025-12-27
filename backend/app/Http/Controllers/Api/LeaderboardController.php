<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Volunteer leaderboard - total recognized engagements where role_category = 'volunteer' or NULL
     */
    public function volunteers(Request $request)
    {
        $query = DB::table('engagements')
            ->join('roles', 'engagements.role_id', '=', 'roles.id')
            ->join('events', 'engagements.event_id', '=', 'events.id')
            ->join('users', 'engagements.user_id', '=', 'users.id')
            ->where('engagements.is_recognized', true)
            ->where('users.status', 'active')
            ->where(function ($q) {
                $q->where('roles.role_category', 'volunteer')
                  ->orWhereNull('roles.role_category');
            });

        // Apply filters
        if ($request->filled('program_id')) {
            $query->where('events.first_program_id', $request->program_id);
        }
        if ($request->filled('season_id')) {
            $query->where('events.season_id', $request->season_id);
        }
        if ($request->filled('level_id')) {
            $query->where('events.level_id', $request->level_id);
        }

        $leaderboard = $query->select(
                'users.id',
                'users.nickname',
                DB::raw('COUNT(engagements.id) as engagement_count')
            )
            ->groupBy('users.id', 'users.nickname')
            ->orderByDesc('engagement_count')
            ->limit(100)
            ->get();

        // Add rank with ties (users with same count get same rank)
        $rank = 1;
        $previousCount = null;
        $ranked = $leaderboard->values()->map(function ($item, $index) use (&$rank, &$previousCount) {
            if ($previousCount !== null && $item->engagement_count == $previousCount) {
                // Same count as previous, use same rank
                $item->rank = $rank;
            } else {
                // Different count, calculate new rank (index + 1)
                $rank = $index + 1;
                $item->rank = $rank;
            }
            $previousCount = $item->engagement_count;
            return $item;
        });

        return response()->json($ranked);
    }

    /**
     * Regional Partner leaderboard - distinct seasons where role_category = 'regional_partner'
     */
    public function regionalPartners(Request $request)
    {
        $query = DB::table('engagements')
            ->join('roles', 'engagements.role_id', '=', 'roles.id')
            ->join('events', 'engagements.event_id', '=', 'events.id')
            ->join('users', 'engagements.user_id', '=', 'users.id')
            ->where('engagements.is_recognized', true)
            ->where('users.status', 'active')
            ->where('roles.role_category', 'regional_partner');

        // Apply filters
        if ($request->filled('program_id')) {
            $query->where('events.first_program_id', $request->program_id);
        }
        if ($request->filled('season_id')) {
            $query->where('events.season_id', $request->season_id);
        }
        if ($request->filled('level_id')) {
            $query->where('events.level_id', $request->level_id);
        }

        $leaderboard = $query->select(
                'users.id',
                'users.nickname',
                'users.regional_partner_name',
                DB::raw('COUNT(DISTINCT events.season_id) as season_count')
            )
            ->groupBy('users.id', 'users.nickname', 'users.regional_partner_name')
            ->orderByDesc('season_count')
            ->limit(100)
            ->get();

        // Add rank with ties (users with same count get same rank), use regional_partner_name if set
        $rank = 1;
        $previousCount = null;
        $ranked = $leaderboard->values()->map(function ($item, $index) use (&$rank, &$previousCount) {
            if ($previousCount !== null && $item->season_count == $previousCount) {
                // Same count as previous, use same rank
                $item->rank = $rank;
            } else {
                // Different count, calculate new rank (index + 1)
                $rank = $index + 1;
                $item->rank = $rank;
            }
            $previousCount = $item->season_count;
            $item->display_name = $item->regional_partner_name ?: $item->nickname;
            return $item;
        });

        return response()->json($ranked);
    }

    /**
     * Coach leaderboard - distinct seasons where role_category = 'coach'
     */
    public function coaches(Request $request)
    {
        $query = DB::table('engagements')
            ->join('roles', 'engagements.role_id', '=', 'roles.id')
            ->join('events', 'engagements.event_id', '=', 'events.id')
            ->join('users', 'engagements.user_id', '=', 'users.id')
            ->where('engagements.is_recognized', true)
            ->where('users.status', 'active')
            ->where('roles.role_category', 'coach');

        // Apply filters
        if ($request->filled('program_id')) {
            $query->where('events.first_program_id', $request->program_id);
        }
        if ($request->filled('season_id')) {
            $query->where('events.season_id', $request->season_id);
        }
        if ($request->filled('level_id')) {
            $query->where('events.level_id', $request->level_id);
        }

        $leaderboard = $query->select(
                'users.id',
                'users.nickname',
                DB::raw('COUNT(DISTINCT events.season_id) as season_count')
            )
            ->groupBy('users.id', 'users.nickname')
            ->orderByDesc('season_count')
            ->limit(100)
            ->get();

        // Add rank with ties (users with same count get same rank)
        $rank = 1;
        $previousCount = null;
        $ranked = $leaderboard->values()->map(function ($item, $index) use (&$rank, &$previousCount) {
            if ($previousCount !== null && $item->season_count == $previousCount) {
                // Same count as previous, use same rank
                $item->rank = $rank;
            } else {
                // Different count, calculate new rank (index + 1)
                $rank = $index + 1;
                $item->rank = $rank;
            }
            $previousCount = $item->season_count;
            return $item;
        });

        return response()->json($ranked);
    }

    /**
     * Get filter options for leaderboard
     */
    public function options()
    {
        return response()->json([
            'programs' => DB::table('first_programs')->orderBy('sort_order')->get(['id', 'name']),
            'seasons' => DB::table('seasons')->orderBy('start_year', 'desc')->get(['id', 'name', 'first_program_id']),
            'levels' => DB::table('levels')->where('status', 'approved')->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }
}
