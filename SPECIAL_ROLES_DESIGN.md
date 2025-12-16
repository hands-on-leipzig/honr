# Special Roles Design: Regional Partner & Coach

## Requirements

### Regional Partner ("Regionalpartner")
- Organizes events at their "home" location
- Does not travel
- Role cannot be combined with other roles (for same user)
- Count: Number of **different seasons** they hosted events (not total engagements)
- Badges: Threshold-based (different from standard, no silver/gold, just awards for many seasons)
- Listing page: All regional partners sorted by number of seasons, showing location and season badges
- Can also be volunteer (separate count)

### Coach
- Guides one team per season, potentially through multiple levels
- Count: Number of **different seasons** (not total engagements)
- Listing page: Same as regional partner - sorted by number of seasons with season badges shown
- Can also be volunteer (separate count)

### Constraints
- No different user types (all users are the same)
- Roles should be available seamlessly with all other roles
- On leaderboard: volunteers, regional partners, and coaches are **separate**

---

## Proposed Database Design

### 1. Add `role_category` to `roles` table

**Field:**
- `role_category` (enum: 'volunteer', 'regional_partner', 'coach', nullable)
  - Default: 'volunteer' (or NULL for backward compatibility)
  - Special roles: 'regional_partner', 'coach'
  - Most roles: 'volunteer' (or NULL)

**Purpose:**
- Categorize roles for different counting/leaderboard logic
- Keep roles in same table (seamless)
- No user type changes needed

### 2. Add `home_location` to `users` table

**Field:**
- `home_location` (string, nullable - user-entered text)
  - Regional Partners can enter their home location
  - Free text field (not FK to locations table)
  - Displayed in leaderboard instead of nickname for Regional Partners

**Purpose:**
- Allow Regional Partners to specify their home location
- Display on Regional Partner leaderboard/listing
- User-entered, flexible (not tied to locations table)

### 3. Role Combining Rules

**Business Logic:**
- **Any combination of roles is allowed** (same person, different roles)
- Regional Partner + Coach + Volunteer all possible
- Even in same season
- No validation needed - trust users

### 4. Special Counting Logic

**For Regional Partner & Coach:**
- Count: **DISTINCT seasons** (not total engagements)
- Query: `COUNT(DISTINCT events.season_id) WHERE role_category IN ('regional_partner', 'coach')`

**For Volunteers:**
- Count: **Total engagements** (existing logic)
- Query: `COUNT(engagements.id) WHERE role_category = 'volunteer' OR role_category IS NULL`

### 5. Separate Leaderboards

**Three separate leaderboards:**
1. **Volunteer Leaderboard** - roles where `role_category = 'volunteer' OR role_category IS NULL`
2. **Regional Partner Leaderboard** - roles where `role_category = 'regional_partner'`
3. **Coach Leaderboard** - roles where `role_category = 'coach'`

**Counting:**
- Volunteers: Total engagement count
- Regional Partners: Distinct season count
- Coaches: Distinct season count

### 6. Special Badge Thresholds

**Decision:**
- Leave flexible for now
- Thresholds to be determined later
- Same badge system, thresholds TBD

### 7. Listing Pages

**Regional Partner Listing:**
- Query: Users with regional_partner engagements
- Sort by: Number of distinct seasons (DESC)
- Display: Location (home_location), season badges, number of seasons

**Coach Listing:**
- Query: Users with coach engagements
- Sort by: Number of distinct seasons (DESC)
- Display: Season badges, number of seasons

---

## Database Schema Changes

### Changes to `roles` table

**Add field:**
```sql
role_category ENUM('volunteer', 'regional_partner', 'coach') NULL
```

**Index:**
- Add index on `role_category` for leaderboard queries

**Notes:**
- Default: NULL (treated as 'volunteer' for backward compatibility)
- Special roles explicitly set to 'regional_partner' or 'coach'
- Most roles remain NULL or 'volunteer'

### Changes to `users` table

**Add field:**
```sql
home_location VARCHAR(255) NULL
```

**Index:**
- Optional: Add index if needed for searching

**Purpose:**
- Regional Partners can enter their home location
- Free text field (user-entered)
- Displayed in Regional Partner leaderboard instead of nickname

---

## Query Examples

### Regional Partner Leaderboard (distinct seasons)
```sql
SELECT 
  users.id,
  COALESCE(users.home_location, users.nickname) as display_name,
  users.nickname,
  users.home_location,
  COUNT(DISTINCT events.season_id) as season_count
FROM users
JOIN engagements ON engagements.user_id = users.id
JOIN roles ON engagements.role_id = roles.id
JOIN events ON engagements.event_id = events.id
WHERE engagements.is_recognized = true
  AND roles.role_category = 'regional_partner'
GROUP BY users.id, users.nickname, users.home_location
ORDER BY season_count DESC
```

### Coach Leaderboard (distinct seasons)
```sql
SELECT 
  users.id,
  users.nickname,
  COUNT(DISTINCT events.season_id) as season_count
FROM users
JOIN engagements ON engagements.user_id = users.id
JOIN roles ON engagements.role_id = roles.id
JOIN events ON engagements.event_id = events.id
WHERE engagements.is_recognized = true
  AND roles.role_category = 'coach'
GROUP BY users.id, users.nickname
ORDER BY season_count DESC
```

### Regional Partner Listing Page
```sql
SELECT 
  users.id,
  COALESCE(users.home_location, users.nickname) as display_name,
  users.nickname,
  users.home_location,
  COUNT(DISTINCT events.season_id) as season_count,
  -- Get season badges
  GROUP_CONCAT(DISTINCT seasons.name) as seasons
FROM users
JOIN engagements ON engagements.user_id = users.id
JOIN roles ON engagements.role_id = roles.id
JOIN events ON engagements.event_id = events.id
JOIN seasons ON events.season_id = seasons.id
WHERE engagements.is_recognized = true
  AND roles.role_category = 'regional_partner'
GROUP BY users.id, users.nickname, users.home_location
ORDER BY season_count DESC
```

---

## Decisions Made

1. **Role Category Default**: NULL (backward compatible, treated as volunteer)

2. **Badge Thresholds**: Leave flexible for now (TBD)

3. **Home Location**: 
   - User-entered text field in users table
   - Not tied to locations table
   - Displayed in leaderboard instead of nickname for Regional Partners
   - No validation needed

4. **Combining Roles**: 
   - ✅ Any combination allowed (Regional Partner + Coach + Volunteer)
   - ✅ Even in same season
   - ✅ No validation - trust users

---

## Implementation Notes

- All roles remain in same `roles` table (seamless)
- No user type changes needed
- Special logic handled via `role_category` field
- Separate leaderboards based on role category
- Counting logic differs: total engagements vs distinct seasons
- Badge system can handle special role badges separately

