<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Engagement;
use App\Models\Role;
use App\Models\Event;
use App\Models\Location;
use App\Models\Country;
use App\Models\FirstProgram;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminStatisticsController extends Controller
{
    // Badge thresholds (same as BadgeController)
    private const THRESHOLDS = [0, 1, 5, 20, 50];

    public function index(Request $request)
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // 1. Users
        $usersTotal = User::count();
        $usersNew30Days = User::where('created_at', '>=', $thirtyDaysAgo)->count();
        $usersLogged30Days = User::where('last_login_at', '>=', $thirtyDaysAgo)->count();

        // 2. Engagements
        $engagementsTotal = Engagement::count();
        $engagementsNew30Days = Engagement::where('created_at', '>=', $thirtyDaysAgo)->count();
        $activeUsersCount = User::where('status', 'active')->count();
        $engagementsAvgPerUser = $activeUsersCount > 0 ? round($engagementsTotal / $activeUsersCount, 1) : 0;

        // 4. Roles
        $rolesTop = Role::where('status', 'approved')
            ->withCount(['engagements' => function ($query) {
                $query->where('is_recognized', true);
            }])
            ->orderBy('engagements_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'count' => $role->engagements_count,
                ];
            });

        $rolesTotalEngagements = Engagement::where('is_recognized', true)->count();
        $rolesAvgPerUser = $activeUsersCount > 0 ? round($rolesTotalEngagements / $activeUsersCount, 1) : 0;

        // 7. Geography
        $countriesCount = Country::where('status', 'approved')->count();
        $locationsCount = Location::where('status', 'approved')->count();
        
        // Count engagements per location
        $locationsWithEngagements = DB::table('engagements')
            ->join('events', 'engagements.event_id', '=', 'events.id')
            ->join('locations', 'events.location_id', '=', 'locations.id')
            ->where('engagements.is_recognized', true)
            ->where('locations.status', 'approved')
            ->select('locations.id', 'locations.name', 'locations.city', DB::raw('COUNT(*) as count'))
            ->groupBy('locations.id', 'locations.name', 'locations.city')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'city' => $item->city,
                    'count' => $item->count,
                ];
            });

        $locationsTotalEngagements = Engagement::where('is_recognized', true)
            ->whereHas('event.location')
            ->count();
        $locationsAvgPerLocation = $locationsCount > 0 ? round($locationsTotalEngagements / $locationsCount, 1) : 0;

        // 8. Programs
        // Count engagements per program through roles
        $programsWithEngagements = DB::table('engagements')
            ->join('roles', 'engagements.role_id', '=', 'roles.id')
            ->join('first_programs', 'roles.first_program_id', '=', 'first_programs.id')
            ->where('engagements.is_recognized', true)
            ->select('first_programs.id', 'first_programs.name', DB::raw('COUNT(*) as count'))
            ->groupBy('first_programs.id', 'first_programs.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'count' => $item->count,
                ];
            });

        $programsTotalEngagements = Engagement::where('is_recognized', true)
            ->whereHas('role.firstProgram')
            ->count();
        $programsCount = FirstProgram::count();
        $programsAvgPerProgram = $programsCount > 0 ? round($programsTotalEngagements / $programsCount, 1) : 0;

        // 8. Seasons
        // Count engagements per season through events
        $seasonsWithEngagements = DB::table('engagements')
            ->join('events', 'engagements.event_id', '=', 'events.id')
            ->join('seasons', 'events.season_id', '=', 'seasons.id')
            ->where('engagements.is_recognized', true)
            ->select('seasons.id', 'seasons.name', DB::raw('COUNT(*) as count'))
            ->groupBy('seasons.id', 'seasons.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'count' => $item->count,
                ];
            });

        $seasonsTotalEngagements = Engagement::where('is_recognized', true)
            ->whereHas('event.season')
            ->count();
        $seasonsCount = Season::count();
        $seasonsAvgPerSeason = $seasonsCount > 0 ? round($seasonsTotalEngagements / $seasonsCount, 1) : 0;

        // 9. Badges
        $badgeStats = $this->calculateBadgeStatistics();

        return response()->json([
            'users' => [
                'total' => $usersTotal,
                'new_30_days' => $usersNew30Days,
                'logged_30_days' => $usersLogged30Days,
            ],
            'engagements' => [
                'total' => $engagementsTotal,
                'avg_per_user' => $engagementsAvgPerUser,
                'new_30_days' => $engagementsNew30Days,
            ],
            'roles' => [
                'top' => $rolesTop,
                'avg_per_user' => $rolesAvgPerUser,
            ],
            'geography' => [
                'countries_count' => $countriesCount,
                'locations_count' => $locationsCount,
                'top_locations' => $locationsWithEngagements,
                'avg_per_location' => $locationsAvgPerLocation,
            ],
            'programs' => [
                'top' => $programsWithEngagements,
                'avg_per_program' => $programsAvgPerProgram,
            ],
            'seasons' => [
                'top' => $seasonsWithEngagements,
                'avg_per_season' => $seasonsAvgPerSeason,
            ],
            'badges' => $badgeStats,
        ]);
    }

    private function calculateBadgeStatistics()
    {
        // Get all users with their recognized engagements grouped by role
        $users = User::where('status', 'active')->get();
        
        $badgeCounts = [
            'basic' => 0,    // Level 1
            'bronze' => 0,   // Level 2
            'silver' => 0,   // Level 3
            'gold' => 0,     // Level 4
        ];

        $badgeCombinations = []; // role_id => level => count
        $userBadgeCounts = []; // user_id => badge_count

        foreach ($users as $user) {
            $engagementsByRole = Engagement::where('user_id', $user->id)
                ->where('is_recognized', true)
                ->with('role:id,name')
                ->get()
                ->groupBy('role_id');

            $userBadgeCount = 0;

            foreach ($engagementsByRole as $roleId => $engagements) {
                $count = $engagements->count();
                $level = $this->calculateLevel($count);

                if ($level > 0) {
                    $badgeCounts[$this->getLevelName($level)]++;
                    $userBadgeCount++;

                    // Track badge combinations (role + level)
                    if (!isset($badgeCombinations[$roleId])) {
                        $badgeCombinations[$roleId] = [];
                    }
                    if (!isset($badgeCombinations[$roleId][$level])) {
                        $badgeCombinations[$roleId][$level] = 0;
                    }
                    $badgeCombinations[$roleId][$level]++;
                }
            }

            $userBadgeCounts[] = $userBadgeCount;
        }

        // Find most awarded badge combination
        $mostAwarded = null;
        $mostAwardedCount = 0;
        foreach ($badgeCombinations as $roleId => $levels) {
            foreach ($levels as $level => $count) {
                if ($count > $mostAwardedCount) {
                    $role = Role::find($roleId);
                    if ($role) {
                        $mostAwarded = [
                            'role_name' => $role->name,
                            'level' => $level,
                            'level_name' => $this->getLevelName($level),
                            'count' => $count,
                        ];
                        $mostAwardedCount = $count;
                    }
                }
            }
        }

        $maxBadgesPerUser = !empty($userBadgeCounts) ? max($userBadgeCounts) : 0;
        $avgBadgesPerUser = !empty($userBadgeCounts) ? round(array_sum($userBadgeCounts) / count($userBadgeCounts), 1) : 0;

        return [
            'counts' => $badgeCounts,
            'most_awarded' => $mostAwarded,
            'max_per_user' => $maxBadgesPerUser,
            'avg_per_user' => $avgBadgesPerUser,
        ];
    }

    private function calculateLevel(int $count): int
    {
        if ($count >= self::THRESHOLDS[4]) {
            return 4; // Gold
        } elseif ($count >= self::THRESHOLDS[3]) {
            return 3; // Silver
        } elseif ($count >= self::THRESHOLDS[2]) {
            return 2; // Bronze
        } elseif ($count >= self::THRESHOLDS[1]) {
            return 1; // Basic
        } else {
            return 0; // No badge
        }
    }

    private function getLevelName(int $level): string
    {
        return match($level) {
            1 => 'basic',
            2 => 'bronze',
            3 => 'silver',
            4 => 'gold',
            default => 'none',
        };
    }
}

