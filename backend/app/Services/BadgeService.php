<?php

namespace App\Services;

class BadgeService
{
    /**
     * Badge threshold values
     * Levels: 0→1, 1→5, 5→20, 20→50
     * Level 1: 1 engagement (Basic)
     * Level 2: 5 engagements (Bronze)
     * Level 3: 20 engagements (Silver)
     * Level 4: 50 engagements (Gold)
     */
    public const THRESHOLDS = [0, 1, 5, 20, 50];

    /**
     * Calculate badge level based on engagement count
     * Returns 1-4 based on thresholds: 0→1, 1→5, 5→20, 20→50
     * 
     * @param int $count Engagement count
     * @return int Badge level (0-4, where 0 = no badge)
     */
    public static function calculateLevel(int $count): int
    {
        if ($count >= self::THRESHOLDS[4]) {
            return 4; // 50+ engagements = Gold
        } elseif ($count >= self::THRESHOLDS[3]) {
            return 3; // 20+ engagements = Silver
        } elseif ($count >= self::THRESHOLDS[2]) {
            return 2; // 5+ engagements = Bronze
        } elseif ($count >= self::THRESHOLDS[1]) {
            return 1; // 1+ engagement = Basic
        } else {
            return 0; // 0 engagements = No badge
        }
    }

    /**
     * Get level name for a given level
     * 
     * @param int $level Badge level (1-4)
     * @return string Level name (basic, bronze, silver, gold)
     */
    public static function getLevelName(int $level): string
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

