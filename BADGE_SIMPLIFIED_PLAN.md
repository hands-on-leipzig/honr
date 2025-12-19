# Simplified Badge Implementation Plan

## Overview
Remove badge tables, calculate badge levels on-the-fly from engagement counts. Store role icons in roles table.

## Plan Review

### ✅ Step 1: Remove Badge Tables
- **Migration**: Drop `badges`, `badge_thresholds`, `earned_badges` tables
- **Remove CRUDs**: 
  - `AdminCrudBadges.vue`
  - `AdminCrudEarnedBadges.vue`
  - `AdminBadgeController.php`
  - `AdminEarnedBadgeController.php`
  - Routes in `api.php`
- **Remove Models**: `Badge.php`, `BadgeThreshold.php`, `EarnedBadge.php`
- **Remove relationships**: `Role::badges()` relationship

**Status**: ✅ Clear

### ✅ Step 2: Add logo_path to roles table
- **Migration**: Add `logo_path` (nullable string) to `roles` table
- **Update Role model**: Add `logo_path` to `$fillable`
- **Update AdminCrudRoles.vue**: Add icon upload functionality (similar to first_programs/seasons)

**Status**: ✅ Clear

### ✅ Step 3: Badge Controller
- **Create**: `BadgeController.php` with method `getUserBadges($userId)`
- **Logic**:
  1. Query engagements: `WHERE user_id = $userId AND is_recognized = true`
  2. Group by `role_id`, count engagements per role
  3. For each role with engagements:
     - Count engagements
     - Calculate level based on thresholds:
       - Level 1: 1 engagement
       - Level 2: 5 engagements
       - Level 3: 20 engagements
       - Level 4: 50 engagements
     - Return: `['role_name' => string, 'level' => int (1-4), 'logo_path' => string|null]`
- **API Route**: `GET /badges/user/{userId}` or `GET /users/{userId}/badges`

**Status**: ✅ Clear, but need to clarify:
- **Threshold values**: Confirm 1, 5, 20, 50?
- **Level calculation**: If user has 7 engagements, level = 2 (highest threshold achieved)?
- **Only roles with engagements**: Or show all roles with level 0 if no engagements?

### ✅ Step 4: Awards View Display
- **Location**: Between leaderboard ranks and program logos
- **Display**: For each badge in array:
  - If `logo_path` exists: Show icon with border (none/bronze/silver/gold based on level)
  - If `logo_path` empty: Show chip with role name
- **Styling**: CSS borders for bronze (#CD7F32), silver (#C0C0C0), gold (#FFD700)

**Status**: ✅ Clear

## Potential Gaps & Questions

### 1. **Threshold Values** ⚠️
- **Question**: Confirm threshold values are 1, 5, 20, 50?
- **Recommendation**: Make these constants in BadgeController or config

### 2. **Level Calculation Logic** ⚠️
- **Question**: How to calculate level?
  - Option A: Highest threshold achieved (e.g., 7 engagements → level 2)
  - Option B: Next threshold to reach (e.g., 7 engagements → level 3)
- **Recommendation**: Option A (highest achieved) - shows current status

### 3. **Which Roles to Show** ⚠️
- **Question**: Show only roles user has engagements in, or all roles?
- **Recommendation**: Only roles with engagements (level > 0)
- **Alternative**: Show all roles, level 0 = no badge shown

### 4. **Icon Upload in Admin UI** ⚠️
- **Missing**: Need to add icon upload to `AdminCrudRoles.vue`
- **Similar to**: FirstPrograms/Seasons logo upload
- **Route**: `POST /admin/roles/{id}/logo`, `DELETE /admin/roles/{id}/logo`

### 5. **API Endpoint Structure** ⚠️
- **Question**: Endpoint path?
  - Option A: `GET /badges/user/{userId}`
  - Option B: `GET /users/{userId}/badges`
- **Recommendation**: Option B (more RESTful, under users resource)

### 6. **Performance Considerations** ℹ️
- **Current**: Calculate on every request
- **Future**: Could cache if needed, but for MVP this is fine
- **Query**: Should be efficient with proper indexes on `engagements.user_id` and `engagements.is_recognized`

### 7. **Empty State** ℹ️
- **Question**: What if user has no recognized engagements?
- **Recommendation**: Return empty array, don't show badge box

### 8. **Role Status Filtering** ⚠️
- **Question**: Should we only count engagements for approved roles?
- **Current**: Engagements require approved roles to be recognized
- **Recommendation**: Already handled (only recognized engagements counted)

### 9. **Icon Path Resolution** ⚠️
- **Question**: How to construct full URL for logo_path?
- **Recommendation**: Same as first_programs/seasons: `${backendUrl}/storage/${logo_path}`
- **Frontend**: Use existing `getLogoUrl()` helper

### 10. **Badge Component** ⚠️
- **Missing**: Create reusable `BadgeIcon.vue` component
- **Props**: `logoPath`, `level`, `roleName`
- **Logic**: Apply border CSS based on level, fallback to chip if no logo

## Implementation Checklist

### Database
- [ ] Migration: Drop badges, badge_thresholds, earned_badges tables
- [ ] Migration: Add logo_path to roles table
- [ ] Update Role model: Add logo_path to fillable

### Backend
- [ ] Remove Badge, BadgeThreshold, EarnedBadge models
- [ ] Remove AdminBadgeController, AdminEarnedBadgeController
- [ ] Remove badge routes from api.php
- [ ] Remove Role::badges() relationship
- [ ] Create BadgeController with getUserBadges() method
- [ ] Add route: GET /users/{userId}/badges
- [ ] Add logo upload routes to AdminRoleController
- [ ] Update AdminRoleController: Add uploadLogo(), deleteLogo() methods

### Frontend
- [ ] Remove AdminCrudBadges.vue
- [ ] Remove AdminCrudEarnedBadges.vue
- [ ] Remove badges from AdminTablesView.vue
- [ ] Update AdminCrudRoles.vue: Add logo upload UI
- [ ] Create BadgeIcon.vue component (with border styling)
- [ ] Update AwardsSummaryTab.vue: Add badge box, fetch badges, display
- [ ] Update AwardsView.vue: Pass userId to AwardsSummaryTab if viewing other user

### Testing
- [ ] Test badge calculation with different engagement counts (1, 5, 20, 50+)
- [ ] Test with user having no engagements
- [ ] Test with roles that have/ don't have logos
- [ ] Test icon upload in admin UI
- [ ] Test badge display with borders (bronze, silver, gold)

## Recommended Additions

1. **Constants file**: Define threshold values (1, 5, 20, 50) in BadgeController
2. **BadgeIcon component**: Reusable component for displaying badges
3. **Helper method**: `getBadgeLevel($count)` in BadgeController
4. **Icon upload**: Add to AdminCrudRoles (similar to first_programs/seasons)

## Summary

Your plan is **solid and much simpler** than the original! The main things to clarify/add:

1. ✅ Confirm threshold values (1, 5, 20, 50)
2. ✅ Add icon upload to AdminCrudRoles
3. ✅ Create BadgeIcon component for display
4. ✅ Define API endpoint structure
5. ✅ Handle empty states

The on-the-fly calculation approach is perfect for MVP and can be optimized later if needed.

