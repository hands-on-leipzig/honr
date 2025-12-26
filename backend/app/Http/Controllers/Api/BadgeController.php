<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Engagement;
use App\Models\Role;
use App\Mail\BadgeAwarded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BadgeController extends Controller
{
    // Badge threshold values (can be changed in one place)
    // Levels: 0→1, 1→5, 5→20, 20→50
    private const THRESHOLDS = [0, 1, 5, 20, 50];

    /**
     * Get badges for a user based on their recognized engagements
     */
    public function getUserBadges($userId)
    {
        $user = User::findOrFail($userId);

        // Get all recognized engagements for this user, grouped by role
        $engagementCounts = Engagement::where('user_id', $user->id)
            ->where('is_recognized', true)
            ->with('role:id,name,short_name,logo_path')
            ->get()
            ->groupBy('role_id')
            ->map(function ($engagements, $roleId) {
                return [
                    'count' => $engagements->count(),
                    'role' => $engagements->first()->role,
                    'role_id' => (int) $roleId,
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
            $roleId = $item['role_id'];

            // Calculate level based on thresholds
            // Level 1: 1 engagement, Level 2: 5, Level 3: 20, Level 4: 50
            $level = $this->calculateLevel($count);

            $badges[] = [
                'role_id' => $roleId,
                'role_name' => $role->name,
                'role_short_name' => $role->short_name,
                'level' => $level,
                'logo_path' => $role->logo_path,
                'engagement_count' => $count,
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
     * Returns 1-4 based on thresholds: 0→1, 1→5, 5→20, 20→50
     */
    private function calculateLevel(int $count): int
    {
        if ($count >= self::THRESHOLDS[4]) {
            return 4; // 50+ engagements = Gold
        } elseif ($count >= self::THRESHOLDS[3]) {
            return 3; // 20+ engagements = Silver
        } elseif ($count >= self::THRESHOLDS[2]) {
            return 2; // 5+ engagements = Bronze
        } elseif ($count >= self::THRESHOLDS[1]) {
            return 1; // 1+ engagement = Base
        } else {
            return 0; // 0 engagements = No badge
        }
    }

    /**
     * Check if newly added engagement hits a threshold and send notification
     * Called after an engagement is created (by user in UI or after approval by admin)
     */
    public function checkBadgeThresholds(User $user, int $roleId): void
    {
        // Only send if user wants tool info notifications
        if (!$user->email_tool_info) {
            return;
        }

        // Get current engagement count for this role
        $currentCount = Engagement::where('user_id', $user->id)
            ->where('role_id', $roleId)
            ->where('is_recognized', true)
            ->count();

        // Calculate current level
        $currentLevel = $this->calculateLevel($currentCount);

        // Calculate previous level (before this engagement was added)
        $previousCount = $currentCount - 1;
        $previousLevel = $this->calculateLevel($previousCount);

        // If level increased, send notification
        if ($currentLevel > $previousLevel && $currentLevel > 0) {
            $role = Role::find($roleId);
            if (!$role) {
                return;
            }

            Mail::to($user->email)->send(new BadgeAwarded(
                $user,
                $role->name,
                $role->short_name ?? '',
                $currentLevel,
                $currentCount,
                $role->logo_path
            ));
        }
    }
}
