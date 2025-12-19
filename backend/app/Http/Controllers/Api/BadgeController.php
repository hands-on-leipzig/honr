<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Engagement;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    // Badge threshold values (can be changed in one place)
    private const THRESHOLDS = [1, 5, 20, 50];

    /**
     * Get badges for a user based on their recognized engagements
     */
    public function getUserBadges($userId)
    {
        $user = User::findOrFail($userId);

        // Get all recognized engagements for this user, grouped by role
        $engagementCounts = Engagement::where('user_id', $user->id)
            ->where('is_recognized', true)
            ->with('role:id,name,logo_path')
            ->get()
            ->groupBy('role_id')
            ->map(function ($engagements) {
                return [
                    'count' => $engagements->count(),
                    'role' => $engagements->first()->role,
                ];
            })
            ->filter(function ($item) {
                // Only include roles that exist and have engagements
                return $item['role'] !== null && $item['count'] > 0;
            });

        $badges = [];

        foreach ($engagementCounts as $item) {
            $count = $item['count'];
            $role = $item['role'];

            // Calculate level based on thresholds
            // Level 1: 1 engagement, Level 2: 5, Level 3: 20, Level 4: 50
            $level = $this->calculateLevel($count);

            $badges[] = [
                'role_name' => $role->name,
                'level' => $level,
                'logo_path' => $role->logo_path,
            ];
        }

        // Sort by role name
        usort($badges, function ($a, $b) {
            return strcmp($a['role_name'], $b['role_name']);
        });

        return response()->json($badges);
    }

    /**
     * Calculate badge level based on engagement count
     * Returns 1-4 based on thresholds: 1, 5, 20, 50
     */
    private function calculateLevel(int $count): int
    {
        if ($count >= self::THRESHOLDS[3]) {
            return 4; // 50+ engagements = Gold
        } elseif ($count >= self::THRESHOLDS[2]) {
            return 3; // 20+ engagements = Silver
        } elseif ($count >= self::THRESHOLDS[1]) {
            return 2; // 5+ engagements = Bronze
        } else {
            return 1; // 1+ engagement = Base
        }
    }
}
