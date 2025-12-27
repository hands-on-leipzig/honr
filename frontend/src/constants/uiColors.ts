/**
 * Centralized UI color constants
 * All color values used across the application should reference these constants
 */

// Badge level colors (bronze, silver, gold)
export const BADGE_COLORS = {
  bronze: '#CD7F32',  // Level 2
  silver: '#C0C0C0',  // Level 3
  gold: '#FFD700',    // Level 4
} as const

// Map colors
export const MAP_COLORS = {
  fill: '#3b82f6',    // blue-500
  border: '#1d4ed8',  // blue-700
} as const

// Status colors - Success/Approved
export const STATUS_SUCCESS = {
  badge: 'bg-green-100 text-green-800',
  text: 'text-green-600',
  alert: 'bg-green-50 text-green-700',
  icon: 'text-green-500',
} as const

// Status colors - Error/Rejected
export const STATUS_ERROR = {
  badge: 'bg-red-100 text-red-800',
  text: 'text-red-600',
  button: 'bg-red-600 text-white',
  buttonHover: 'hover:bg-red-700',
  hover: 'hover:bg-red-50',
} as const

// Status colors - Warning/Pending
export const STATUS_WARNING = {
  badge: 'bg-yellow-100 text-yellow-800',
  text: 'text-amber-600',
  icon: 'text-amber-500',
} as const

// Status colors - Info/Invited
export const STATUS_INFO = {
  badge: 'bg-blue-100 text-blue-800',
  text: 'text-blue-600',
  icon: 'text-blue-500',
} as const

// Rank colors
export const RANK_COLORS = {
  first: 'bg-yellow-100 text-yellow-700',      // Rank 1
  second: 'bg-gray-200 text-gray-700',         // Rank 2
  third: 'bg-orange-100 text-orange-700',      // Rank 3
  other: 'bg-white border border-gray-300 text-gray-600', // Rank > 3
} as const

// Primary action colors (Blue)
export const PRIMARY_COLORS = {
  button: 'bg-blue-600 text-white',
  buttonHover: 'hover:bg-blue-700',
  buttonDisabled: 'disabled:opacity-50',
  link: 'text-blue-600',
  linkHover: 'hover:text-blue-800',
  focusRing: 'focus:ring-blue-500',
  peerFocusRing: 'peer-focus:ring-blue-300',
  activeTab: 'bg-white shadow text-blue-600',
  selected: 'bg-blue-50',
} as const

// Neutral colors (Gray)
export const NEUTRAL_COLORS = {
  background: {
    page: 'bg-gray-50',
    card: 'bg-white',
    hover: 'hover:bg-gray-50',
    disabled: 'bg-gray-100',
    modalOverlay: 'bg-black bg-opacity-50',
  },
  text: {
    primary: 'text-gray-900',
    secondary: 'text-gray-700',
    tertiary: 'text-gray-600',
    muted: 'text-gray-500',
    disabled: 'text-gray-400',
    light: 'text-gray-300',
  },
  border: {
    default: 'border-gray-300',
    light: 'border-gray-200',
    divider: 'divide-gray-200',
    dividerLight: 'divide-gray-100',
  },
} as const

/**
 * Get the border color class for a badge level
 * @param level - Badge level (1-4)
 * @returns Tailwind CSS border color class
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

/**
 * Get the background tint color for a badge level (very light, 10-15% opacity)
 * @param level - Badge level (1-4)
 * @returns CSS background color with rgba
 */
export function getBadgeBackgroundTint(level: number): string {
  switch (level) {
    case 1:
      return 'rgba(255, 255, 255, 1)' // White (no tint)
    case 2:
      return 'rgba(205, 127, 50, 0.12)' // Very light bronze tint
    case 3:
      return 'rgba(192, 192, 192, 0.15)' // Very light silver tint
    case 4:
      return 'rgba(255, 215, 0, 0.12)' // Very light gold tint
    default:
      return 'rgba(255, 255, 255, 1)' // White (no tint)
  }
}

/**
 * Get the box-shadow glow for a badge level
 * @param level - Badge level (1-4)
 * @param size - Badge size ('md' or 'lg')
 * @returns CSS box-shadow value
 */
export function getBadgeGlow(level: number, size: 'md' | 'lg' = 'md'): string {
  const baseBlur = size === 'lg' ? 8 : 4
  const baseSpread = size === 'lg' ? 2 : 1
  
  switch (level) {
    case 1:
      return 'none' // No glow for level 1
    case 2:
      // Subtle bronze glow
      return `0 0 ${baseBlur}px ${baseSpread}px rgba(205, 127, 50, 0.3)`
    case 3:
      // Medium silver glow
      return `0 0 ${baseBlur * 1.5}px ${baseSpread * 1.5}px rgba(192, 192, 192, 0.4)`
    case 4:
      // Strong gold glow
      return `0 0 ${baseBlur * 2}px ${baseSpread * 2}px rgba(255, 215, 0, 0.5)`
    default:
      return 'none'
  }
}

/**
 * Get the rank color classes for a given rank
 * @param rank - Rank number (1, 2, 3, or > 3)
 * @returns Tailwind CSS classes for the rank
 */
export function getRankColorClass(rank: number): string {
  if (rank === 1) return RANK_COLORS.first
  if (rank === 2) return RANK_COLORS.second
  if (rank === 3) return RANK_COLORS.third
  return RANK_COLORS.other
}

/**
 * Get status color classes
 * @param status - Status string ('approved', 'pending', 'rejected', 'recognized', 'unrecognized', 'invited')
 * @returns Tailwind CSS classes for the status
 */
export function getStatusColorClass(status: string): string {
  switch (status.toLowerCase()) {
    case 'approved':
    case 'recognized':
      return STATUS_SUCCESS.badge
    case 'pending':
    case 'unrecognized':
      return STATUS_WARNING.badge
    case 'rejected':
      return STATUS_ERROR.badge
    case 'invited':
      return STATUS_INFO.badge
    default:
      return NEUTRAL_COLORS.background.disabled + ' ' + NEUTRAL_COLORS.text.secondary
  }
}

