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
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Unique: `name`
- Index: `sort_order`, `status`

**Constraints:**
- Name: unique, not null
- Sort_order: not null, integer
- Status: not null

**Relationships:**
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
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Index: `first_program_id`, `status`, `sort_order`
- Composite unique: (`first_program_id`, `name`) - name unique per program

**Constraints:**
- Name: not null, unique per program (via composite unique)
- Sort_order: not null, integer
- First_program_id: not null, foreign key
- Status: not null

**Relationships:**
- `first_program_id` → `first_programs.id` (on delete: restrict - cannot delete program if roles exist)

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
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Unique: `name`, `iso_code`
- Index: `status`

**Constraints:**
- Name: unique, not null
- Iso_code: unique, not null, 2 characters (ISO 3166-1 alpha-2 standard)
- Status: not null

**Relationships:**
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
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Unique: `name` (globally unique)
- Index: `country_id`, `status`
- Index: `latitude`, `longitude` (for spatial queries)

**Constraints:**
- Name: unique, not null
- Country_id: not null, foreign key
- Status: not null
- Latitude: nullable, decimal(10,8) - range: -90 to 90
- Longitude: nullable, decimal(11,8) - range: -180 to 180

**Relationships:**
- `country_id` → `countries.id` (on delete: restrict - cannot delete country if locations exist)

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
- `created_at` (timestamp)
- `updated_at` (timestamp, auto-updated)

**Indexes:**
- Primary: `id`
- Index: `first_program_id`, `season_id`, `level_id`, `location_id`, `status`, `date`
- Composite unique: (`first_program_id`, `season_id`, `level_id`, `location_id`, `date`) - prevent duplicate events

**Constraints:**
- All foreign keys: not null, restrict on delete
- Date: not null
- Status: not null

**Relationships:**
- `first_program_id` → `first_programs.id` (on delete: restrict)
- `season_id` → `seasons.id` (on delete: restrict)
- `level_id` → `levels.id` (on delete: restrict)
- `location_id` → `locations.id` (on delete: restrict)

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

