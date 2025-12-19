# Badge Implementation Plan

## Overview
Implement "grow" badges for roles with 4 fixed thresholds (1, 5, 20, 50 engagements). One base SVG icon per role with visual borders (bronze, silver, gold) applied in UI.

## Database Structure

### Keep `badges` table? **YES**

**Reasoning:**
- Need to track `status` (pending_icon, released) - controls when badges can be earned
- Need `description` and `sort_order` for admin UI
- Need to link `badge_thresholds` and `earned_badges`
- Provides flexibility for future changes (e.g., disabling badges, reordering)

**Simplified badges table:**
- `id`, `role_id` (FK), `status` (pending_icon, released), `description`, `sort_order`, `created_at`, `updated_at`
- One badge per role (enforced by unique constraint on `role_id`)

### Simplify `badge_thresholds` table

**Current structure is mostly good, but simplify:**
- Keep: `id`, `badge_id` (FK), `threshold_value`, `sort_order`, `created_at`, `updated_at`
- Remove: `level_name` (not needed - visual borders only)
- Change: `icon_path` → store base SVG icon (same for all thresholds, borders applied in UI)

**Threshold values (fixed):**
- Level 1: 1 engagement (base icon, no border)
- Level 2: 5 engagements (base icon + bronze border)
- Level 3: 20 engagements (base icon + silver border)
- Level 4: 50 engagements (base icon + gold border)

### Keep `earned_badges` table

**Structure unchanged:**
- `id`, `user_id` (FK), `badge_id` (FK), `earned_at`, `current_threshold_id` (FK), `created_at`, `updated_at`
- Tracks which users have earned which badges and at what threshold level
- Reduces load time by pre-computing badge status

## Implementation Steps

### Phase 1: Database & Models

1. **Update badges table**
   - Add unique constraint on `role_id` (one badge per role)
   - Ensure migration is clean

2. **Update badge_thresholds table**
   - Remove `level_name` column (migration)
   - Keep `icon_path` for base SVG icon
   - Document that borders are applied in UI

3. **Update Badge model**
   - Add validation: one badge per role
   - Add relationship to Role

4. **Update BadgeThreshold model**
   - Remove level_name from fillable
   - Document threshold values (1, 5, 20, 50)

### Phase 2: Automatic Badge Creation

5. **Create badges automatically when roles are approved**
   - Observer/Event on Role model: when `status` changes to `approved`
   - Check if badge exists for role
   - If not, create badge with `status='pending_icon'`
   - Create 4 threshold entries (1, 5, 20, 50) with `sort_order` (1, 2, 3, 4)

6. **Create badges for existing approved roles**
   - Seeder/command to backfill badges for existing approved roles

### Phase 3: Admin UI for Badge Management

7. **Simplify AdminCrudBadges.vue**
   - Remove manual badge creation (auto-created from roles)
   - Show badges grouped by role
   - Allow editing: `status`, `description`, `sort_order`
   - Show "pending icon" indicator if `icon_path` not set in thresholds

8. **Create AdminCrudBadgeThresholds.vue**
   - List thresholds for a badge
   - Upload base SVG icon (stored in first threshold's `icon_path`, or badge-level?)
   - Show threshold values (read-only, fixed at 1, 5, 20, 50)
   - Visual preview with borders (bronze, silver, gold)

9. **Icon upload**
   - Upload SVG file
   - Store in `storage/badges/{role_id}/icon.svg` or similar
   - Store path in `badge_thresholds.icon_path` (same path for all thresholds of a badge)
   - Validate SVG format

### Phase 4: Badge Earning Logic

10. **Create BadgeService**
    - Method: `checkAndAwardBadges(User $user, Engagement $engagement)`
    - Called when engagement becomes `is_recognized=true`
    - For each role badge:
      - Count user's recognized engagements with that role
      - Find highest threshold achieved (1, 5, 20, 50)
      - Create/update `earned_badges` entry
      - Set `current_threshold_id` to highest threshold

11. **Hook into engagement recognition**
    - In `EngagementController::store()` - after setting `is_recognized=true`
    - In `AdminEngagementController::update()` - when status changes to recognized
    - Call `BadgeService::checkAndAwardBadges()`

12. **Handle badge release**
    - When badge `status` changes to `released`:
      - Check all users with recognized engagements for that role
      - Award badges to eligible users
      - Send notifications (future)

### Phase 5: Badge Display

13. **Update AwardsSummaryTab.vue**
    - Query `earned_badges` for user (with badge, role, current_threshold)
    - Display badges with appropriate border (none, bronze, silver, gold)
    - Group by role or show in grid

14. **Badge rendering component**
    - Component: `BadgeIcon.vue`
    - Props: `iconPath` (SVG), `thresholdLevel` (1-4)
    - Apply CSS border based on threshold:
      - Level 1: No border
      - Level 2: Bronze border (e.g., `#CD7F32`)
      - Level 3: Silver border (e.g., `#C0C0C0`)
      - Level 4: Gold border (e.g., `#FFD700`)

15. **Badge display in leaderboards** (if needed)
    - Show badges next to user names
    - Only show highest threshold achieved

### Phase 6: Performance & Optimization

16. **Indexing**
    - Ensure indexes on `earned_badges.user_id`, `earned_badges.badge_id`
    - Index on `badges.role_id` (unique)

17. **Caching** (future)
    - Cache badge counts per user
    - Invalidate when engagements recognized

## Design Decisions

### Icon Storage
- **Option A**: Store icon path in `badge_thresholds.icon_path` (same for all thresholds)
- **Option B**: Store icon path in `badges` table (one icon per badge/role)
- **Recommendation**: Option B - store in `badges` table as `icon_path`
  - Cleaner: one icon per badge, not per threshold
  - Easier to manage
  - Thresholds only need threshold_value and sort_order

### Border Application
- Apply borders via CSS in frontend
- Use SVG filters or CSS borders
- Colors: Bronze (#CD7F32), Silver (#C0C0C0), Gold (#FFD700)

### Badge Status Workflow
1. Role approved → Badge created with `status='pending_icon'`
2. Admin uploads icon → Badge `status` remains `pending_icon` (or auto-set to `released`?)
3. Admin sets `status='released'` → System checks all users, awards badges
4. Future engagements → BadgeService updates earned_badges

## Questions to Resolve

1. **Icon storage location**: `badges.icon_path` or `badge_thresholds.icon_path`?
   - **Decision**: `badges.icon_path` (one icon per badge/role)

2. **Auto-release when icon uploaded?**
   - **Decision**: Keep manual release (admin sets status='released')

3. **Threshold values fixed or configurable?**
   - **Decision**: Fixed (1, 5, 20, 50) for now, can be made configurable later

4. **Badge description required?**
   - **Decision**: Optional, can be auto-generated from role name

5. **Sort order**: By role name or manual?
   - **Decision**: Manual sort_order, default by role name

## Migration Strategy

1. Create badges for all existing approved roles
2. Create 4 thresholds per badge (1, 5, 20, 50)
3. For existing users, calculate and create earned_badges entries
4. Test with sample data

## Testing Checklist

- [ ] Badge created automatically when role approved
- [ ] Badge not created if already exists for role
- [ ] 4 thresholds created with correct values (1, 5, 20, 50)
- [ ] Icon upload works (SVG validation)
- [ ] Badge earning logic works (1, 5, 20, 50 engagements)
- [ ] Badge progression works (updates current_threshold_id)
- [ ] Badge display shows correct border (none, bronze, silver, gold)
- [ ] Badge status workflow (pending_icon → released)
- [ ] Badge release triggers check for all users
- [ ] Performance: earned_badges query is fast

