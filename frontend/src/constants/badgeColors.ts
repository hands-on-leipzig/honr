/**
 * Badge level colors
 * Used for bronze (level 2), silver (level 3), and gold (level 4) badge borders
 */
export const BADGE_COLORS = {
  bronze: '#CD7F32',  // Level 2
  silver: '#C0C0C0',  // Level 3
  gold: '#FFD700',    // Level 4
} as const

/**
 * Get the border color class for a badge level
 * @param level - Badge level (1-4)
 * @returns Tailwind CSS border color class with arbitrary value
 */
export function getBadgeBorderClass(level: number): string {
  switch (level) {
    case 1:
      return 'border-gray-300' // Light gray border (consistent with ranks > 3)
    case 2:
      return 'border-[#CD7F32]' // Bronze
    case 3:
      return 'border-[#C0C0C0]' // Silver
    case 4:
      return 'border-[#FFD700]' // Gold
    default:
      return 'border-gray-300' // Light gray border (consistent with ranks > 3)
  }
}

