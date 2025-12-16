# User Flows Documentation

## Flow 1: Capture Engagement

### Description
User adds a new engagement to their history.

### Steps
1. User navigates to engagement history
2. User clicks "add" button
3. User selects:
   - **Role** (from existing approved roles)
   - **Event** (from existing approved events)
4. **If both role and event exist:**
   - User saves engagement
   - Recognition process starts automatically
   - If new badges awarded or leaderboard rank changed → notification shown
5. **If role or event missing:**
   - Crowdsourcing process starts
   - For events: Can be multi-level (all tables with FK from events table)
   - All new entries go to admin approval
   - User notified of pending approval

### Database Support Check

**✅ Supported:**
- `engagements` table has: user_id, role_id, event_id
- `roles` table has: status (pending, approved, rejected) - Crowdsourced
- `events` table has: status (pending, approved, rejected) - Crowdsourced
- `events` table has FKs to: first_program_id, season_id, level_id, location_id
- All referenced tables (first_programs, seasons, levels, locations) support crowdsourcing

**✅ Supported:**
- All crowdsourced tables now have `proposed_by_user_id` field
- Can track who proposed entry for notifications

---

## Flow 2: Admin Approve Crowdsourcing

### Description
Admin reviews and approves/rejects crowdsourced entries.

### Steps
1. **Daily Check (Automated):**
   - System checks for new crowdsourced entries (status='pending')
   - If any found → send email notification to admins
2. **Admin Review:**
   - Admin goes to CRUD interface for respective tables
   - Admin approves or rejects entries
3. **After Decision:**
   - User who proposed entry is notified of decision
   - System checks if pending engagements can now be acknowledged
   - If engagement acknowledged:
     - Mail sent to user
     - Includes: new badges earned, leaderboard rank changes

### Database Support Check

**✅ Supported:**
- All crowdsourced tables have: status (pending, approved, rejected)
- `engagements` table has: is_recognized, recognized_at
- Recognition logic: engagement becomes recognized when user.status='confirmed' AND role.status='approved' AND event.status='approved'
- `earned_badges` table tracks badge awards
- Leaderboard can be computed from engagements + earned_badges

**✅ Supported:**
- `proposed_by_user_id` added to all crowdsourced tables ✅
- Can notify user who proposed entry ✅

**Optional (Not Critical for MVP):**
1. **Notification tracking** - No table to track what notifications were sent
   - Can use Laravel Mail without tracking (acceptable for MVP)
2. **Admin action log** - No tracking of who approved what
   - `users.is_admin` exists ✅
   - Not critical for MVP, but useful for audit (can add later)

---

## Database Model Updates Applied

### ✅ Fixed: Track Who Proposed Crowdsourced Entries

**Solution**: Added `proposed_by_user_id` field to all crowdsourced tables

**Tables Updated:**
- `levels.proposed_by_user_id` ✅
- `roles.proposed_by_user_id` ✅
- `countries.proposed_by_user_id` ✅
- `locations.proposed_by_user_id` ✅
- `events.proposed_by_user_id` ✅

**Implementation:**
- Field: `proposed_by_user_id` (foreign key to users.id, nullable)
- On delete: set null (preserve entry if user deleted)
- Logic:
  - When user proposes entry → set `proposed_by_user_id` to current user
  - When admin approves/rejects → notify user via `proposed_by_user_id`
  - If entry created by admin → `proposed_by_user_id` = null

---

## Flow Summary

### Capture Engagement Flow
1. ✅ User selects role/event → Supported
2. ✅ Save if both exist → Supported
3. ✅ Recognition process → Supported (is_recognized logic)
4. ✅ Badge/leaderboard notifications → Supported (can compute)
5. ✅ Crowdsourcing if missing → **Supported** (proposed_by_user_id added)
6. ✅ User notification of pending → **Supported** (proposed_by_user_id added)

### Admin Approval Flow
1. ✅ Daily check for pending entries → Can query status='pending'
2. ✅ Email to admins → Laravel Mail (no tracking table needed)
3. ✅ Admin approve/reject → Supported (status field)
4. ✅ Notify proposing user → **Supported** (proposed_by_user_id added)
5. ✅ Check pending engagements → Supported (recognition logic)
6. ✅ Notify user of acknowledgment → Laravel Mail (no tracking table needed)
7. ✅ Include badges/leaderboard → Supported (can compute)

---

## Flow 3: New Badges Workflow

### Description
Automatic badge creation when entries are approved/added, with icon upload workflow before release.

### Steps
1. **Badge Creation Trigger:**
   - When admin approves crowdsourced entry OR admin adds entry via CRUD
   - System automatically creates corresponding badge (if doesn't exist)
   - Badge created with status='pending_icon' (not released yet)

2. **Badge Icon Upload:**
   - New badges appear in admin "pending icons" list
   - Admin uploads icon for badge
   - Admin marks badge as complete/released

3. **Badge Release:**
   - When badge status becomes 'released'
   - System checks all users' engagements
   - If user earned the badge → award badge → send email notification

### Badge Creation Rules
- **Country approved** → Create tick_box badge for that country
- **Level approved** → Create tick_box badge for that level
- **Season approved** → Create tick_box badge for that season
- **First_program added** → Create tick_box badge for that program
- **Role approved** → Create grow badge for that role

### Database Support Check

**✅ Supported:**
- `badges` table now has `status` field ('pending_icon', 'released')
- Badge creation workflow supported
- Icon upload workflow supported
- Badge release and user checking supported

---

## Flow 4: Admin Direct Entry

### Description
Admin can add entries directly via CRUD (not through crowdsourcing).

### Steps
1. Admin adds entry directly to any table via CRUD
2. Entry is immediately approved (no crowdsourcing workflow)
3. Badge creation workflow triggered (same as Flow 3)
4. Badge created with status='pending_icon'

### Database Support Check

**✅ Supported:**
- Admin can add entries directly (no special status needed)
- Badge creation workflow same as crowdsourced approval
- All tables support direct admin entry

**Note:**
- Badges cannot be added via CRUD
- Badges only created internally when other tables get new entries

---

## Flow 5: UI Leaderboard on Engagement

### Description
Leaderboard showing engagement counts per user, with filters and badge displays.

### UI Structure
**Table Columns:**
1. Rank (1, 2, 3, ...)
2. Nickname (from users.nickname)
3. Total count of engagements (count of recognized engagements)
4. List of all season badges (tick_box badges where badge.season_id IS NOT NULL)
5. List of all program badges (tick_box badges where badge.first_program_id IS NOT NULL)
6. List of all level badges (tick_box badges where badge.level_id IS NOT NULL)
7. List of all country badges (tick_box badges where badge.country_id IS NOT NULL)

**Display Logic:**
- Shows top 10 users by engagement count
- Always shows current user's row (even if not in top 10)
- If current user is in top 10, only show 10 rows total

**Filters (Above Table):**
- Season (all entries OR specific season)
- Program (all entries OR specific program)
- Level (all entries OR specific level)
- Country (all entries OR specific country)
- Filters work as logical AND (all must match)

### Database Support Check

**✅ Supported:**
- Count engagements: `engagements` table with `is_recognized=true`
- Filter by season: `engagements.event_id` → `events.season_id`
- Filter by program: `engagements.event_id` → `events.first_program_id`
- Filter by level: `engagements.event_id` → `events.level_id`
- Filter by country: `engagements.event_id` → `events.location_id` → `locations.country_id`
- Get user nickname: `users.nickname`
- Get season badges: `earned_badges` JOIN `badges` WHERE `badges.type='tick_box'` AND `badges.season_id IS NOT NULL`
- Get program badges: `earned_badges` JOIN `badges` WHERE `badges.type='tick_box'` AND `badges.first_program_id IS NOT NULL`
- Get level badges: `earned_badges` JOIN `badges` WHERE `badges.type='tick_box'` AND `badges.level_id IS NOT NULL`
- Get country badges: `earned_badges` JOIN `badges` WHERE `badges.type='tick_box'` AND `badges.country_id IS NOT NULL`

**Query Logic:**
```sql
-- Count recognized engagements per user with filters
SELECT 
  users.id,
  users.nickname,
  COUNT(engagements.id) as engagement_count
FROM users
JOIN engagements ON engagements.user_id = users.id
JOIN events ON engagements.event_id = events.id
JOIN locations ON events.location_id = locations.id
WHERE engagements.is_recognized = true
  AND (season_filter = 'all' OR events.season_id = season_filter)
  AND (program_filter = 'all' OR events.first_program_id = program_filter)
  AND (level_filter = 'all' OR events.level_id = level_filter)
  AND (country_filter = 'all' OR locations.country_id = country_filter)
GROUP BY users.id, users.nickname
ORDER BY engagement_count DESC
LIMIT 10
```

**Notes:**
- Only count recognized engagements (`is_recognized = true`)
- Badges shown are tick_box badges only (grow badges not shown in leaderboard)
- Badges must have status='released' to be shown
- Rank calculated based on engagement count (ties handled by application logic)

## Flow 6: UI User Profile

### Description
Profile page showing user statistics, badges, and engagement history.

### Profile Content (All Users Can View)
1. **Nickname** - from `users.nickname`
2. **First engagement date** - earliest `engagements.created_at` for user
3. **Last engagement date** - latest `engagements.created_at` for user
4. **Rank in leaderboard** - computed from engagement count (same logic as Flow 5)
5. **Badges Section:**
   - **Season Badges** - tick_box badges where `badges.season_id IS NOT NULL`
   - **Program Badges** - tick_box badges where `badges.first_program_id IS NOT NULL`
   - **Level Badges** - tick_box badges where `badges.level_id IS NOT NULL`
   - **Country Badges** - tick_box badges where `badges.country_id IS NOT NULL`
   - **Role Badges** - grow badges where `badges.role_id` matches roles user has engaged with
     - Show badge for each role the user has at least one recognized engagement
     - Display current threshold level (from `earned_badges.current_threshold_id`)
6. **Geo Heatmap** - See Flow 8 for details
   - User profile version: Shows only that user's engagements
   - Uses `locations.latitude` and `locations.longitude` from user's engagements

### Profile Content (Own Profile Only)
1. **Engagement List** - grouped by status:
   - **Acknowledged** - engagements where `is_recognized = true`
   - **Pending** - engagements where `is_recognized = false`
2. **Add New Engagement** - button/link to add engagement (Flow 1)

### Database Support Check

**✅ Supported:**
- Nickname: `users.nickname` ✅
- First engagement date: `MIN(engagements.created_at) WHERE user_id = X` ✅
- Last engagement date: `MAX(engagements.created_at) WHERE user_id = X` ✅
- Rank: Computed from engagement count (same as Flow 5) ✅
- Season/Program/Level/Country badges: `earned_badges` JOIN `badges` WHERE `badges.type='tick_box'` ✅
- Role badges: 
  - Get roles user engaged with: `SELECT DISTINCT role_id FROM engagements WHERE user_id = X AND is_recognized = true`
  - Get grow badges for those roles: `earned_badges` JOIN `badges` WHERE `badges.type='grow'` AND `badges.role_id IN (roles)`
  - Show current threshold from `earned_badges.current_threshold_id` ✅
- Geo heatmap: `engagements` → `events` → `locations` (latitude, longitude) ✅
- Engagement list: Filter `engagements` by `user_id` and `is_recognized` ✅
- Add engagement: Already supported (Flow 1) ✅

**Query Examples:**
```sql
-- First/Last engagement dates
SELECT 
  MIN(created_at) as first_engagement,
  MAX(created_at) as last_engagement
FROM engagements
WHERE user_id = X

-- Role badges (grow badges for roles user engaged with)
SELECT 
  badges.*,
  earned_badges.current_threshold_id,
  badge_thresholds.icon_path
FROM earned_badges
JOIN badges ON earned_badges.badge_id = badges.id
LEFT JOIN badge_thresholds ON earned_badges.current_threshold_id = badge_thresholds.id
WHERE earned_badges.user_id = X
  AND badges.type = 'grow'
  AND badges.role_id IN (
    SELECT DISTINCT role_id 
    FROM engagements 
    WHERE user_id = X AND is_recognized = true
  )

-- Geo heatmap data
SELECT 
  locations.latitude,
  locations.longitude,
  COUNT(engagements.id) as engagement_count
FROM engagements
JOIN events ON engagements.event_id = events.id
JOIN locations ON events.location_id = locations.id
WHERE engagements.user_id = X
  AND engagements.is_recognized = true
  AND locations.latitude IS NOT NULL
  AND locations.longitude IS NOT NULL
GROUP BY locations.latitude, locations.longitude
```

---

## Flow 7: User List & Search

### Description
UI view to browse and search for users by nickname.

### UI Structure
1. **User List View:**
   - Scrollable list of all users (nicknames)
   - Display: nickname (clickable to profile)
   
2. **Search Functionality:**
   - Search input field
   - Search by nickname (partial match)
   - Results show matching nicknames
   - Click nickname → jump to user profile (Flow 6)

### Database Support Check

**✅ Supported:**
- List all users: `SELECT id, nickname FROM users WHERE status = 'confirmed'` ✅
- Search nicknames: `SELECT id, nickname FROM users WHERE nickname LIKE '%search%' AND status = 'confirmed'` ✅
- Jump to profile: Use `users.id` to load profile page ✅

**Query Example:**
```sql
-- Search users by nickname
SELECT id, nickname
FROM users
WHERE status = 'confirmed'
  AND nickname LIKE '%search_term%'
ORDER BY nickname
```

**Notes:**
- Only show confirmed users (`status = 'confirmed'`)
- Search is case-insensitive (application layer or SQL)
- Results ordered alphabetically by nickname

## Flow 8: Geo Heatmap

### Description
World map with heatmap visualization based on engagement locations.

### Version 1: Global Heatmap (All Users)
**UI Location:** Same level as leaderboard and user search (main navigation)

**Features:**
- World map with heatmap overlay
- Heat intensity based on total engagement count per location
- Filters (AND logic):
  - **Program** (all entries OR specific program)
  - **Season** (all entries OR specific season)
  - **Level** (all entries OR specific level)
- No country filter (map shows all countries)

**Data Logic:**
- Count total engagements per location (with filters)
- Use location's GPS coordinates (latitude, longitude)
- Only use locations with valid GPS coordinates
- Only count recognized engagements (`is_recognized = true`)

### Version 2: User Profile Heatmap (Own Profile Only)
**UI Location:** User profile page

**Features:**
- World map with heatmap overlay
- Heat intensity based on user's engagement count per location
- No filters (only shows data for that specific user)
- Only shows user's recognized engagements

**Data Logic:**
- Count user's engagements per location
- Use location's GPS coordinates (latitude, longitude)
- Only use locations with valid GPS coordinates
- Only count recognized engagements (`is_recognized = true`)

### Database Support Check

**✅ Supported:**
- Location GPS coordinates: `locations.latitude`, `locations.longitude` ✅
- Count engagements per location: `engagements` JOIN `events` JOIN `locations` ✅
- Filter by program: `events.first_program_id` ✅
- Filter by season: `events.season_id` ✅
- Filter by level: `events.level_id` ✅
- Filter by user: `engagements.user_id` ✅
- Only recognized engagements: `engagements.is_recognized = true` ✅
- Only locations with GPS: `locations.latitude IS NOT NULL AND locations.longitude IS NOT NULL` ✅

**Query Examples:**

```sql
-- Global heatmap (with filters)
SELECT 
  locations.id,
  locations.name,
  locations.latitude,
  locations.longitude,
  COUNT(engagements.id) as engagement_count
FROM engagements
JOIN events ON engagements.event_id = events.id
JOIN locations ON events.location_id = locations.id
WHERE engagements.is_recognized = true
  AND locations.latitude IS NOT NULL
  AND locations.longitude IS NOT NULL
  AND (program_filter = 'all' OR events.first_program_id = program_filter)
  AND (season_filter = 'all' OR events.season_id = season_filter)
  AND (level_filter = 'all' OR events.level_id = level_filter)
GROUP BY locations.id, locations.name, locations.latitude, locations.longitude
ORDER BY engagement_count DESC

-- User profile heatmap (no filters, just user)
SELECT 
  locations.id,
  locations.name,
  locations.latitude,
  locations.longitude,
  COUNT(engagements.id) as engagement_count
FROM engagements
JOIN events ON engagements.event_id = events.id
JOIN locations ON events.location_id = locations.id
WHERE engagements.user_id = X
  AND engagements.is_recognized = true
  AND locations.latitude IS NOT NULL
  AND locations.longitude IS NOT NULL
GROUP BY locations.id, locations.name, locations.latitude, locations.longitude
ORDER BY engagement_count DESC
```

**Notes:**
- Heatmap intensity = engagement_count per location
- Only locations with GPS coordinates are shown (latitude AND longitude must be present)
- Global version: All users' recognized engagements (with filters)
- Profile version: Only that user's recognized engagements (no filters)
- Map visualization handled by frontend (e.g., Google Maps, Leaflet, Mapbox)

---

## Status: All Flows Supported ✅

### Database Updates Applied

**✅ Fixed: Badge Status Workflow**
- Added `status` field to `badges` table
- Values: 'pending_icon', 'released' (extensible)
- Supports badge creation → icon upload → release workflow

### Optional Enhancements (Not Critical for MVP)
1. Admin action log table (track who approved what)
2. Notification tracking table (track emails sent)
3. Badge threshold history (track progression timeline)

