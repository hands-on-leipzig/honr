# Database Schema Design

## Tables

### 1. users
*Status: Finalized*

**Fields:**
- `id` (primary key, auto-increment)
- `email` (unique, username for login, indexed)
- `password` (hashed)
- `status` (enum: requested, confirmed - extensible for future)
- `nickname` (unique, visible to other users, indexed)
- `consent_to_newsletter` (boolean, default false)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)
- `last_login_at` (timestamp, nullable)
- `is_admin` (boolean, default false, indexed)

**Indexes:**
- Primary: `id`
- Unique: `email`, `nickname`
- Index: `status`, `is_admin`

**Constraints:**
- Email: unique, not null
- Nickname: unique, not null
- Password: not null

**Relationships:**
- [To be defined as we add other tables]

**Use Cases:**
1. **Self Registration**
   - User enters email + password
   - New entry created in DB with status='requested'
   - Email sent with confirmation link
   - After link click, status changes to 'confirmed', login possible

2. **Forgot Password**
   - User requests password reset
   - Email sent with reset link
   - Link allows password change

3. **Change Password**
   - Authenticated user changes password
   - Requires current password verification

4. **Change Other Fields**
   - Authenticated user can change: nickname, consent_to_newsletter
   - Email change: requires verification (new email verification token created)
   - Updates `updated_at` automatically

5. **Delete User**
   - Hard delete (remove row from database)
   - Note: This will cascade delete related records (to be defined in relationships)

---

### 2. email_verification_tokens
*Status: Finalized*

**Purpose:** Store email verification tokens for account confirmation and email changes

**Fields:**
- `id` (primary key, auto-increment)
- `user_id` (foreign key to users.id)
- `email` (email address being verified)
- `token` (unique verification token)
- `type` (enum: 'registration', 'email_change')
- `expires_at` (timestamp)
- `created_at` (timestamp)

**Indexes:**
- Primary: `id`
- Unique: `token`
- Index: `user_id`, `email`

**Constraints:**
- User_id: not null, foreign key
- Email: not null
- Token: unique, not null
- Type: not null
- Expires_at: not null

**Relationships:**
- `user_id` → `users.id` (on delete: cascade - tokens deleted when user is deleted)

**Use Cases:**
- Store token when user registers (type='registration')
- Store token when user requests email change (type='email_change')
- Token expires after set time period
- Token deleted after successful verification

**Notes:**
- No `updated_at` field - tokens are immutable after creation (only created and deleted)
- Tokens are automatically deleted when user is deleted (cascade)

---

### 3. first_programs
*Status: Finalized*

**Purpose:** Store FIRST LEGO League program types (Explore, Challenge, future programs)

**Fields:**
- `id` (primary key, auto-increment)
- `name` (string, unique, e.g., "Explore", "Challenge")
- `sort_order` (integer, manual entry, for UI display ordering - lower numbers appear first)
- `valid_from` (date, nullable - program available from this date)
- `valid_to` (date, nullable - program available until this date)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Unique: `name`
- Index: `sort_order`, `valid_from`, `valid_to`

**Constraints:**
- Name: unique, not null
- Sort_order: not null, integer

**Relationships:**
- [To be defined as we add related tables]

**Use Cases:**
1. **CRUD Operations** (Admin only)
   - Create new program
   - Read/list programs
   - Update program details (name, sort_order, validity dates)
   - Programs cannot be deleted (use valid_to date to deactivate)

**Notes:**
- Admin-only maintenance (no simple UI needed)
- Programs are predefined (Explore, Challenge) with more to come
- Use valid_from/valid_to to restrict program availability by date
- Sort_order is manually set by admin (simple integer)

---

### 4. seasons
*Status: Finalized*

**Purpose:** Store FLL seasons (e.g., "CARGO CONNECT 2023/2024")

**Fields:**
- `id` (primary key, auto-increment)
- `name` (string, e.g., "CARGO CONNECT")
- `start_year` (integer, e.g., 2023 - represents "2023/2024" season, formatted in application)
- `first_program_id` (foreign key to first_programs.id, not null)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Index: `first_program_id`, `start_year`
- Composite unique: (`first_program_id`, `start_year`) - prevent duplicate seasons per program
- Composite unique: (`first_program_id`, `name`) - name unique per program

**Constraints:**
- Name: not null, unique per program (via composite unique)
- Start_year: not null, integer
- First_program_id: not null, foreign key

**Relationships:**
- `first_program_id` → `first_programs.id` (on delete: restrict - cannot delete program if seasons exist)

**Use Cases:**
- [To be defined]

**Notes:**
- Year stored as `start_year` integer (e.g., 2023)
- Display formatted as "2023/2024" in application layer (start_year/start_year+1)

---

### 5. levels
*Status: Finalized - uses "Crowdsourced" pattern*

**Purpose:** Store competition levels (e.g., Regional, State, National). Users can propose new levels for admin approval.

**Fields:**
- `id` (primary key, auto-increment)
- `name` (string, unique)
- `description` (text, nullable - helps users select and admins approve)
- `sort_order` (integer, manual entry, for UI display ordering)
- `status` (enum: pending, approved, rejected - extensible, TBD)
- `proposed_by_user_id` (foreign key to users.id, nullable - user who proposed this entry)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Unique: `name`
- Index: `sort_order`, `status`, `proposed_by_user_id`

**Constraints:**
- Name: unique, not null
- Sort_order: not null, integer
- Status: not null
- Proposed_by_user_id: nullable, foreign key

**Relationships:**
- `proposed_by_user_id` → `users.id` (on delete: set null - preserve entry if user deleted)
- [Foreign keys from other tables will reference this - restrict on delete]

**Use Cases:**
1. **User Proposes New Level**
   - User enters volunteer engagement (engagements table)
   - Level not found in list
   - User can enter new level data (name, description)
   - Entry created with status='pending'
   - Admin notified (or admin reviews pending entries)

2. **Admin Approves/Rejects**
   - Admin reviews pending entries
   - Admin can approve (status='approved') or reject (status='rejected')
   - Approved entries become available for all users
   - Rejected entries remain for reference (or can be deleted?)

3. **User Selects Level**
   - Users see approved levels in dropdown/selection
   - Description helps users pick correct level
   - Only approved levels shown in selection UI

**Notes:**
- First table with "user proposal → admin approval" workflow
- Pattern will be reused in other tables
- Description field assists both selection and approval decisions

---

### 6. roles
*Status: Finalized - uses "Crowdsourced" pattern*

**Purpose:** Store volunteer roles (e.g., Referee, Judge, Organizer). Roles are program-specific. Users can propose new roles for admin approval.

**Fields:**
- `id` (primary key, auto-increment)
- `name` (string)
- `description` (text, nullable - helps users select and admins approve)
- `sort_order` (integer, manual entry, for UI display ordering)
- `first_program_id` (foreign key to first_programs.id, not null)
- `status` (enum: pending, approved, rejected - extensible, TBD)
- `proposed_by_user_id` (foreign key to users.id, nullable - user who proposed this entry)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Index: `first_program_id`, `status`, `sort_order`, `proposed_by_user_id`
- Composite unique: (`first_program_id`, `name`) - name unique per program

**Constraints:**
- Name: not null, unique per program (via composite unique)
- Sort_order: not null, integer
- First_program_id: not null, foreign key
- Status: not null
- Proposed_by_user_id: nullable, foreign key

**Relationships:**
- `first_program_id` → `first_programs.id` (on delete: restrict - cannot delete program if roles exist)
- `proposed_by_user_id` → `users.id` (on delete: set null - preserve entry if user deleted)

**Use Cases:**
1. **User Proposes New Role**
   - User enters volunteer engagement (engagements table)
   - Role not found in list for selected program
   - User can enter new role data (name, description)
   - Entry created with status='pending'
   - Admin reviews pending entries

2. **Admin Approves/Rejects**
   - Admin reviews pending entries
   - Admin can approve (status='approved') or reject (status='rejected')
   - Approved roles become available for all users in that program
   - Rejected entries remain for reference

3. **User Selects Role**
   - Users see approved roles for selected program in dropdown/selection
   - Description helps users pick correct role
   - Only approved roles shown in selection UI

**Notes:**
- Uses Crowdsourced pattern
- Roles are program-specific (same role name can exist in different programs)
- Description field assists both selection and approval decisions

---

### 7. countries
*Status: Finalized - uses "Crowdsourced" pattern*

**Purpose:** Store countries where volunteer activities take place. Users can propose new countries for admin approval.

**Fields:**
- `id` (primary key, auto-increment)
- `name` (string, e.g., "Germany", "Austria", "Switzerland")
- `iso_code` (string, 2 characters, ISO 3166-1 alpha-2, unique, e.g., "DE", "AT", "CH")
- `status` (enum: pending, approved, rejected - extensible, TBD)
- `proposed_by_user_id` (foreign key to users.id, nullable - user who proposed this entry)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Unique: `name`, `iso_code`
- Index: `status`, `proposed_by_user_id`

**Constraints:**
- Name: unique, not null
- Iso_code: unique, not null, 2 characters (ISO 3166-1 alpha-2 standard)
- Status: not null
- Proposed_by_user_id: nullable, foreign key

**Relationships:**
- `proposed_by_user_id` → `users.id` (on delete: set null - preserve entry if user deleted)
- [Foreign keys from other tables will reference this - restrict on delete]

**Use Cases:**
1. **User Proposes New Country**
   - User enters volunteer engagement (engagements table)
   - Country not found in list
   - User can enter new country data (name, ISO code)
   - Entry created with status='pending'
   - Admin reviews pending entries

2. **Admin Approves/Rejects**
   - Admin reviews pending entries
   - Admin can approve (status='approved') or reject (status='rejected')
   - Approved countries become available for all users
   - Rejected entries remain for reference

3. **User Selects Country**
   - Users see approved countries in dropdown/selection
   - Only approved countries shown in selection UI

**Notes:**
- Uses Crowdsourced pattern
- ISO 3166-1 alpha-2 standard (2-letter codes: DE, AT, CH)
- Both name and ISO code are unique

---

### 8. locations
*Status: Finalized - uses "Crowdsourced" pattern*

**Purpose:** Store physical locations where volunteer activities take place. Address fields split for map integration. Users can propose new locations for admin approval.

**Fields:**
- `id` (primary key, auto-increment)
- `name` (string, e.g., "University of Munich", "Vienna Convention Center")
- `country_id` (foreign key to countries.id, not null)
- `street_address` (string, nullable - street name and number)
- `city` (string, nullable)
- `postal_code` (string, nullable)
- `state_province` (string, nullable - state/province/region, not all countries use this)
- `latitude` (decimal, nullable - GPS coordinate, precision: 10,8 for ~1cm accuracy)
- `longitude` (decimal, nullable - GPS coordinate, precision: 10,8 for ~1cm accuracy)
- `status` (enum: pending, approved, rejected - extensible, TBD)
- `proposed_by_user_id` (foreign key to users.id, nullable - user who proposed this entry)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Unique: `name` (globally unique)
- Index: `country_id`, `status`, `proposed_by_user_id`
- Index: `latitude`, `longitude` (for spatial queries)

**Constraints:**
- Name: unique, not null
- Country_id: not null, foreign key
- Status: not null
- Latitude: nullable, decimal(10,8) - range: -90 to 90
- Longitude: nullable, decimal(11,8) - range: -180 to 180
- Proposed_by_user_id: nullable, foreign key

**Relationships:**
- `country_id` → `countries.id` (on delete: restrict - cannot delete country if locations exist)
- `proposed_by_user_id` → `users.id` (on delete: set null - preserve entry if user deleted)

**Use Cases:**
1. **User Proposes New Location**
   - User enters volunteer engagement (engagements table)
   - Location not found in list
   - User can enter new location data (name, address fields, optionally GPS)
   - Entry created with status='pending'
   - Admin reviews pending entries

2. **Admin Approves/Rejects**
   - Admin reviews pending entries
   - Admin can approve (status='approved') or reject (status='rejected')
   - Approved locations become available for all users
   - Rejected entries remain for reference

3. **User Selects Location**
   - Users see approved locations in dropdown/selection
   - Location can be displayed on map using GPS coordinates or address
   - Only approved locations shown in selection UI

**Notes:**
- Uses Crowdsourced pattern
- Name is globally unique (not per country)
- Address fields split for easy map integration (geocoding, display)
- GPS coordinates stored as decimal for precision, nullable (may not be available during crowdsourced entry)
- Can geocode address to GPS coordinates later if not provided

---

### 9. events
*Status: Finalized - uses "Crowdsourced" pattern*

**Purpose:** Store FLL events/competitions. Users can propose new events for admin approval.

**Fields:**
- `id` (primary key, auto-increment)
- `first_program_id` (foreign key to first_programs.id, not null)
- `season_id` (foreign key to seasons.id, not null)
- `level_id` (foreign key to levels.id, not null)
- `location_id` (foreign key to locations.id, not null)
- `date` (date, not null - event date)
- `status` (enum: pending, approved, rejected - extensible, TBD)
- `proposed_by_user_id` (foreign key to users.id, nullable - user who proposed this entry)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Index: `first_program_id`, `season_id`, `level_id`, `location_id`, `status`, `date`, `proposed_by_user_id`
- Composite unique: (`first_program_id`, `season_id`, `level_id`, `location_id`, `date`) - prevent duplicate events

**Constraints:**
- All foreign keys (first_program_id, season_id, level_id, location_id): not null, restrict on delete
- Proposed_by_user_id: nullable, foreign key
- Date: not null
- Status: not null

**Relationships:**
- `first_program_id` → `first_programs.id` (on delete: restrict)
- `season_id` → `seasons.id` (on delete: restrict)
- `level_id` → `levels.id` (on delete: restrict)
- `location_id` → `locations.id` (on delete: restrict)
- `proposed_by_user_id` → `users.id` (on delete: set null - preserve entry if user deleted)

**Use Cases:**
1. **User Proposes New Event**
   - User enters volunteer engagement (engagements table)
   - Event not found in list
   - User can enter new event data (select program, season, level, location, date)
   - Entry created with status='pending'
   - Admin reviews pending entries

2. **Admin Approves/Rejects**
   - Admin reviews pending entries
   - Admin can approve (status='approved') or reject (status='rejected')
   - Approved events become available for all users
   - Rejected entries remain for reference

3. **User Selects Event**
   - Users see approved events in dropdown/selection
   - Events filtered by program, season, level, location, date
   - Only approved events shown in selection UI

**Notes:**
- Uses Crowdsourced pattern
- All foreign keys are required (not null, restrict on delete)
- Event is defined by combination of program, season, level, location, and date
- Composite unique constraint prevents duplicate events (same program+season+level+location+date)
- Single date field - for multi-day events, use first day
- Event name/description can be built from FK table entries (no separate name/description fields needed)

---

### 10. engagements
*Status: Finalized*

**Purpose:** Store volunteer engagements (jobs/assignments) at events. Represents a user's role at a specific event.

**Fields:**
- `id` (primary key, auto-increment)
- `user_id` (foreign key to users.id, not null)
- `role_id` (foreign key to roles.id, not null)
- `event_id` (foreign key to events.id, not null)
- `is_recognized` (boolean, default false - becomes true when all FK dependencies are approved)
- `recognized_at` (timestamp, nullable - when engagement became recognized)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Index: `user_id`, `role_id`, `event_id`, `is_recognized`
- Composite unique: (`user_id`, `role_id`, `event_id`) - prevent duplicate engagements

**Constraints:**
- All foreign keys: not null, restrict on delete
- Is_recognized: not null, boolean
- Recognized_at: nullable, timestamp

**Relationships:**
- `user_id` → `users.id` (on delete: restrict)
- `role_id` → `roles.id` (on delete: restrict)
- `event_id` → `events.id` (on delete: restrict)

**Status Logic:**
- `is_recognized` becomes `true` when ALL of the following are true:
  - `user.status = 'confirmed'`
  - `role.status = 'approved'`
  - `event.status = 'approved'`
- When `is_recognized` switches to `true`, set `recognized_at` timestamp and trigger processes (e.g., badge calculation, leaderboard updates)
- This is NOT admin approval - it's a computed/derived status based on dependency approvals

**Use Cases:**
1. **User Logs Engagement**
   - User creates engagement record (user, role, event)
   - `is_recognized` starts as `false`
   - If all dependencies are already approved, `is_recognized` becomes `true` immediately, `recognized_at` is set
   - If any dependency is pending, `is_recognized` remains `false` until all are approved

2. **Status Updates**
   - When role/event/user status changes, check if engagement should become recognized
   - Update `is_recognized` accordingly, set `recognized_at` when it becomes true
   - Trigger processes when engagement becomes recognized (badge calculation, leaderboard updates)

3. **View Engagements**
   - Users can view their engagements
   - Filter by recognized/unrecognized status
   - Show engagement details (role, event, date, recognition status, etc.)

**Notes:**
- This is NOT a Crowdsourced pattern table
- Status is computed from FK dependencies, not admin approval
- `is_recognized` becomes true when all dependencies are approved (user, role, event)
- `recognized_at` timestamp tracks when engagement became recognized
- When `is_recognized` becomes true, triggers processes (badge calculation, leaderboard, etc.)
- Table name: `engagements` (short, avoids reserved word "jobs")
- Composite unique constraint prevents duplicate engagements (same user+role+event)

---

### 11. badges
*Status: Finalized*

**Purpose:** Store badge definitions. Admin CRUD only (no Crowdsourced pattern).

**Badge Types:**
1. **"Tick the box"** (type='tick_box') - Binary badges
   - Criteria: first_program, season, level, or country
   - Earned once when criteria met (e.g., at least one engagement in country)
   - UI shows icon (e.g., country flag)
   - Does not change with multiple engagements

2. **"Grow"** (type='grow') - Progressive badges with thresholds
   - Criteria: role
   - Earned at different thresholds (1x = badge, 10x = silver, 20x = gold)
   - Requires multiple thresholds and different icons per threshold
   - User progresses through levels

**Fields:**
- `id` (primary key, auto-increment)
- `name` (string, e.g., "Germany Badge", "Referee Badge")
- `type` (enum: 'tick_box', 'grow')
- `status` (enum: 'pending_icon', 'released' - extensible, TBD)
- `icon_path` (string, nullable - file path/URL for "Tick the box" badges, required when released)
- `first_program_id` (foreign key to first_programs.id, nullable - for "Tick the box" criteria)
- `season_id` (foreign key to seasons.id, nullable - for "Tick the box" criteria)
- `level_id` (foreign key to levels.id, nullable - for "Tick the box" criteria)
- `country_id` (foreign key to countries.id, nullable - for "Tick the box" criteria)
- `role_id` (foreign key to roles.id, nullable - for "Grow" criteria)
- `description` (text, nullable - badge description)
- `sort_order` (integer, nullable - for UI display ordering)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Index: `type`, `status`, `first_program_id`, `season_id`, `level_id`, `country_id`, `role_id`, `sort_order`

**Constraints:**
- Name: not null
- Type: not null
- Status: not null
- Exactly one criteria field must be set (first_program_id OR season_id OR level_id OR country_id OR role_id)
- Icon_path: nullable (required for "Tick the box" when status='released', not used for "Grow" - icons in badge_thresholds)

**Relationships:**
- `first_program_id` → `first_programs.id` (on delete: restrict)
- `season_id` → `seasons.id` (on delete: restrict)
- `level_id` → `levels.id` (on delete: restrict)
- `country_id` → `countries.id` (on delete: restrict)
- `role_id` → `roles.id` (on delete: restrict)

**Use Cases:**
1. **Automatic Badge Creation**
   - When admin approves crowdsourced entry OR admin adds entry via CRUD
   - System automatically creates corresponding badge (if doesn't exist)
   - Badge created with status='pending_icon'
   - Badge appears in admin "pending icons" list

2. **Badge Icon Upload & Release**
   - Admin uploads icon for badge
   - Admin marks badge as released (status='released')
   - When status becomes 'released':
     - System checks all users' engagements
     - If user earned badge → award badge → send email notification

3. **Badge Earning Logic (After Release)**
   - Only badges with status='released' are eligible for earning
   - When engagement becomes recognized, check released badge criteria
   - "Tick the box": Award if user has at least one recognized engagement matching criteria
   - "Grow": Count recognized engagements matching role, award/update based on thresholds

**Badge Creation Rules:**
- Country approved → Create tick_box badge for that country
- Level approved → Create tick_box badge for that level
- Season approved → Create tick_box badge for that season
- First_program added → Create tick_box badge for that program
- Role approved → Create grow badge for that role

**Notes:**
- Badges are NOT created via CRUD - only created internally when other tables get new entries
- Icons stored in filesystem, path/URL stored in `icon_path` field
- Benefits: Better performance, scalability, easier maintenance, CDN support
- Status workflow: 
  - Badge created with status='pending_icon' when entry approved/added
  - Admin uploads icon(s): For "Tick the box" → upload badge icon; For "Grow" → upload icons for all thresholds
  - Admin marks badge as 'released'
  - When badge released → system checks all users → awards badges to eligible users → sends notifications
- For "Grow" badges: Threshold icons stored in `badge_thresholds.icon_path`, badge icon_path not used
- Leaderboard display: Only tick_box badges shown (season, program, level, country badges)
  - Grow badges (role badges) are NOT shown in leaderboard
  - Only badges with status='released' are shown

---

### 12. badge_thresholds
*Status: Finalized*

**Purpose:** Store thresholds for "Grow" type badges (e.g., 1, 10, 20 engagements). Each threshold represents a level (bronze, silver, gold, etc.)

**Fields:**
- `id` (primary key, auto-increment)
- `badge_id` (foreign key to badges.id, not null)
- `threshold_value` (integer, not null - number of engagements required, e.g., 1, 10, 20)
- `level_name` (string, nullable - e.g., 'bronze', 'silver', 'gold', 'platinum')
- `icon_path` (string, nullable - file path/URL for this threshold level)
- `sort_order` (integer, not null - for ordering thresholds, lower = earlier level)
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Index: `badge_id`, `sort_order`
- Composite unique: (`badge_id`, `threshold_value`) - prevent duplicate thresholds

**Constraints:**
- Badge_id: not null, foreign key
- Threshold_value: not null, integer, must be > 0
- Sort_order: not null, integer
- Icon_path: nullable (but recommended for UI display)

**Relationships:**
- `badge_id` → `badges.id` (on delete: cascade - thresholds deleted when badge deleted)

**Use Cases:**
1. **Admin Creates Thresholds**
   - Admin creates threshold entries for "Grow" badge
   - Sets threshold_value (1, 10, 20, etc.)
   - Sets level_name and uploads icon for each level
   - Sort_order determines progression order

2. **Badge Progression**
   - User earns badge at threshold_value = 1
   - As user accumulates more engagements, check if they've reached next threshold
   - Update earned_badges.current_threshold_id when threshold reached

**Notes:**
- Thresholds are fixed values (number and values TBD)
- Each threshold has its own icon (silver lining, gold border, etc.)
- Thresholds must be in ascending order (sort_order)

---

### 13. earned_badges
*Status: Finalized*

**Purpose:** Track which badges users have earned and their current level (for "Grow" badges)

**Fields:**
- `id` (primary key, auto-increment)
- `user_id` (foreign key to users.id, not null)
- `badge_id` (foreign key to badges.id, not null)
- `earned_at` (timestamp, not null - when badge was first earned)
- `current_threshold_id` (foreign key to badge_thresholds.id, nullable - for "Grow" badges only, tracks highest threshold achieved)
- `updated_at` (timestamp, auto-updated - updated when threshold progresses)
- `created_at` (timestamp)

**Indexes:**
- Primary: `id`
- Index: `user_id`, `badge_id`, `current_threshold_id`
- Composite unique: (`user_id`, `badge_id`) - prevent duplicate badge awards

**Constraints:**
- User_id: not null, foreign key
- Badge_id: not null, foreign key
- Earned_at: not null, timestamp
- Current_threshold_id: nullable (only set for "Grow" badges)

**Relationships:**
- `user_id` → `users.id` (on delete: cascade - earned badges deleted when user deleted)
- `badge_id` → `badges.id` (on delete: cascade - earned badges deleted when badge deleted)
- `current_threshold_id` → `badge_thresholds.id` (on delete: set null - if threshold deleted, reset to null)

**Use Cases:**
1. **Badge Earned**
   - When engagement becomes recognized, check if user qualifies for badge
   - "Tick the box": Create earned_badges entry if not exists, set earned_at
   - "Grow": Create entry at threshold 1, set current_threshold_id to first threshold

2. **Badge Progression (Grow badges)**
   - When user accumulates more engagements, check if threshold increased
   - Update current_threshold_id to highest threshold achieved
   - Update updated_at timestamp

3. **View User Badges**
   - Query earned_badges for user
   - For "Tick the box": Show badge icon
   - For "Grow": Show icon from current_threshold_id

**Notes:**
- One row per user per badge (composite unique)
- "Tick the box": earned_at set once, current_threshold_id remains null
- "Grow": earned_at set at first threshold, current_threshold_id updated as user progresses

---

## Badge System Discussion

### Design Questions & Challenges

**1. Badge Criteria Definition**
- **Question**: How do we define what triggers a badge?
- **Examples**:
  - Country badge: "at least one engagement in country X"
  - Role badge: "X engagements as role Y"
  - Level badge: "at least one engagement at level Z"
- **Options**:
  - Store criteria as JSON in badges table (flexible but harder to query)
  - Separate criteria fields (country_id, role_id, level_id, etc.) - more structured
  - Separate `badge_criteria` table with polymorphic relationships
- **Recommendation**: Start with specific criteria fields (country_id, role_id, level_id, program_id, etc.) - simpler, more queryable

**2. Badge Type Storage**
- **Question**: How to distinguish "Tick the box" vs "Grow" badges?
- **Options**:
  - `type` enum field ('tick_box', 'grow')
  - `is_progressive` boolean
- **Recommendation**: `type` enum for clarity and extensibility

**3. Threshold Storage for "Grow" Badges**
- **Question**: How to store thresholds (1, 10, 20)?
- **Options**:
  - Separate `badge_thresholds` table (recommended)
    - Fields: id, badge_id, threshold_value (integer), level_name (string: 'bronze', 'silver', 'gold'), icon_path, sort_order
  - JSON array in badges table (less flexible)
- **Recommendation**: Separate table for flexibility and proper normalization

**4. Current Level Tracking**
- **Question**: How to track which threshold level user has reached?
- **Options**:
  - `earned_badges.current_threshold_id` - points to highest threshold achieved
  - Separate `earned_badge_thresholds` table - tracks all thresholds earned
- **Recommendation**: `current_threshold_id` in `earned_badges` - tracks highest level achieved

**5. Badge Earning Logic**
- **Question**: When are badges earned/updated?
- **Answer**: When `engagement.is_recognized` becomes `true`
- **Process**:
  1. Engagement becomes recognized
  2. Check all badge criteria against user's recognized engagements
  3. Award/update badges accordingly
  4. For "Grow" badges, check if user has reached next threshold

**6. Duplicate Badge Prevention**
- **Question**: Can user earn same badge multiple times?
- **Answer**: 
  - "Tick the box": No - once earned, stays earned
  - "Grow": Yes - progresses through thresholds (but only one row in earned_badges per badge)
- **Recommendation**: Composite unique on (`user_id`, `badge_id`) in `earned_badges`

**7. Badge Icons/Images - DECIDED**
- **Decision**: Store icons in filesystem, store path/URL in database
- **Implementation**:
  - `badges.icon_path` for "Tick the box" badges (stores file path/URL)
  - `badge_thresholds.icon_path` for "Grow" badge levels (stores file path/URL per threshold)
- **Benefits**: Better performance, scalability, easier maintenance, CDN support

**8. Badge Criteria - DECIDED**
- **Decision**: Single criteria per badge (no combinations)
- **"Tick the box" criteria**: first_program, season, level, or country (one per badge)
- **"Grow" criteria**: role only
- **Rationale**: Keep it simple, enough roles to create variety

---

## Use Cases by Table

### users
*To be documented*

---

## Notes
- All additions/changes tracked here
- Questions and decisions documented

---

## Questions & Challenges

### users table

**Decisions Made:**

1. **Email Verification Token Storage**: Separate `email_verification_tokens` table ✓
2. **Password Reset Token Storage**: Use Laravel's built-in `password_reset_tokens` table ✓
3. **Status Enum**: Start simple (requested, confirmed), extensible for future needs ✓
4. **Delete User**: Hard delete (keep it simple) ✓
5. **Email & Nickname**: Both unique constraints ✓
6. **Email Change**: Allowed, requires verification ✓
7. **Audit Trail**: Not needed for MVP ✓
8. **Indexes**: Applied as documented ✓

