# App UI Structure

## Design Principles
- Responsive design (mobile-first)
- All text in German language
- Bottom navigation with 4 main sections

---

## Screen 1: Login

### Features
- Login form (email + password)
- "Remember login" checkbox
- If login fails → offer password reset
- After successful login → Main screen

### Database Support
- ✅ Email/password authentication: `users.email`, `users.password`
- ✅ Password reset: Laravel's built-in `password_reset_tokens` table

---

## Main Screen: Bottom Navigation

**4 Icons (Bottom Navigation):**

1. **Me** (Smiling face icon) → User Profile
2. **Engagement** (Calendar icon) → User's Engagements List
3. **All** (Globe icon) → Leaderboards & Heat Maps
4. **Settings** (Gear icon) → User Settings

---

## Screen 1: Me (User Profile)

### Description
User's own profile page. Shows up to 3 sections (only those that apply):
- Volunteer section
- Regional Partner section
- Coach section

### Content
As defined in Flow 6:
- Nickname
- Home location (if Regional Partner)
- First/last engagement dates per section
- Engagement counts per section
- Rank in respective leaderboard per section
- Badges (season, program, level, country, role)
- Geo heatmap (all engagements combined)

### Database Support
- ✅ All fields supported (see Flow 6)

---

## Screen 2: Engagement (User's Engagements)

### Description
List of user's own engagements with CRUD operations.

### Features
- **Table View:**
  - All engagements of the user
  - Sorted by newest to oldest (`created_at DESC`)
  - Show: Role, Event, Date, Status (Acknowledged/Pending)
  
- **Actions:**
  - **Add** - Create new engagement (Flow 1)
  - **Delete** - Remove engagement
  - **Update** - Edit engagement details

### Database Support
- ✅ List engagements: `SELECT * FROM engagements WHERE user_id = X ORDER BY created_at DESC`
- ✅ Add engagement: Insert into `engagements` table
- ✅ Delete engagement: Delete from `engagements` table
- ✅ Update engagement: Update `engagements` table
- ✅ Show status: `is_recognized` field

---

## Screen 3: All (Leaderboards & Heat Maps)

### Description
Public view with leaderboards and global heat map.

### Navigation (Top)
- **Leaderboard** tab
- **Map** tab

### Leaderboard View
Shows 3 sections (top to bottom):
1. **Volunteers** - Volunteer leaderboard
2. **Regional Partner** - Regional Partner leaderboard
3. **Coach** - Coach leaderboard

Each section:
- Top 10 users
- Current user's row (if not in top 10)
- Filters: Season, Program, Level, Country (where applicable)

### Map View
- **Global heat map** for volunteers
- Shows engagement density by location
- Uses GPS coordinates from locations

### Overlay (Bottom)
- **Search user name** input
- If user selected → show their profile
- **Back button** → return to world view

### Database Support
- ✅ Leaderboards: See Flow 5 (all 3 categories supported)
- ✅ Global heat map: See Flow 8 (volunteer filter)
- ✅ User search: `SELECT * FROM users WHERE nickname LIKE '%search%' AND status = 'confirmed'`
- ✅ User profile: All fields supported

---

## Screen 4: Settings

### Description
User settings and account management.

### Features

#### Account Settings
1. **Change Email**
   - Requires email verification (Flow 1 use case #4)
   - ✅ Supported: `users.email` + `email_verification_tokens`

2. **Change Password**
   - Requires current password verification
   - ✅ Supported: `users.password`

3. **Change Nickname**
   - ✅ Supported: `users.nickname`

4. **Change Short Bio**
   - User-entered text field
   - ✅ Supported: `users.short_bio` (text, nullable)

5. **Change Consent to Newsletter**
   - ✅ Supported: `users.consent_to_newsletter`

6. **Change Regional Partner Name**
   - ✅ Supported: `users.home_location`

7. **Contact / Support**
   - Link to send email for help, feedback, and ideas
   - Opens default mail client with pre-filled recipient (HoT mail server)
   - Subject line: "HOTR - [Help/Feedback/Ideas]"
   - ✅ Supported: `mailto:` link (no database required)

8. **Delete Account**
   - Hard delete (Flow 1 use case #5)
   - ✅ Supported: Delete from `users` table (cascade deletes related records)

#### Admin Feature
- **Switch to Admin UI**
  - Only shown if `users.is_admin = true`
  - ✅ Supported: `users.is_admin` field exists
  - Switches to Admin UI (replaces user navigation)
  - Can switch back to User View from Admin UI

### Database Support Check

**✅ Supported:**
- Change email: `users.email` + email verification workflow
- Change password: `users.password`
- Change nickname: `users.nickname`
- Change short bio: `users.short_bio` (text, nullable)
- Change consent: `users.consent_to_newsletter`
- Change home location: `users.home_location`
- Contact/Support: `mailto:` link (no database required)
- Delete account: Hard delete supported
- Admin switch: `users.is_admin` field exists

---

## UI Flow Summary

### User Flow
1. **Login** → Main Screen
2. **Me** → User Profile (3 sections if applicable)
3. **Engagement** → User's Engagements List (CRUD)
4. **All** → Leaderboards (3 categories) + Map (global heat map) + User Search
5. **Settings** → Account Settings + Admin Switch (if admin)

### Admin Flow
1. **Settings** → Switch to Admin UI (if admin)
2. **Tables** → CRUD for all tables + Pending approvals filter
3. **Statistics** → Statistics dashboard (TBD)
4. **Back to User View** → Return to user interface

---

## Language & Design
- All text: German
- Responsive: Mobile-first design
- Bottom navigation: 4 main sections (User) / 3 main sections (Admin)
- Navigation patterns: Tabs, overlays, back buttons

---

## Admin UI

### Description
Admin interface that replaces user navigation. Accessible from Settings if `users.is_admin = true`.

### Bottom Navigation (3 Icons)

1. **Tables** (CRUD icon) → Table Management & Crowdsourced Approvals
2. **Statistics** (Chart icon) → Statistics Dashboard (Details TBD)
3. **Back to User View** (User icon) → Return to regular user interface

---

## Admin Screen 1: Tables (CRUD)

### Description
CRUD interface for managing all database tables.

### UI Structure

#### First Level: Table Selection
- **List of all tables:**
  - first_programs
  - seasons
  - levels
  - roles
  - countries
  - locations
  - events
  - badges
  - badge_thresholds
  - users (admin management)
  - (Other tables as needed)

- **Select table** → Navigate to table entries list

#### Second Level: Table Entries List
- **List all entries** in selected table
- **Display:** All fields in table format
- **Sorting:** Default sorting (TBD - likely by created_at DESC)

#### Actions Available
1. **Add Empty** - Create new entry with empty/default values
2. **Delete** - Remove entry (with confirmation)
3. **Edit** - Opens modal with:
   - All fields editable
   - **Save** button - Save changes
   - **Cancel** button - Discard changes

#### Global Filter
- **"Admin Action Required"** filter
- Shows entries from Crowdsourced workflows that need approval
- Filters tables: `levels`, `roles`, `countries`, `locations`, `events`
- Shows only entries where `status = 'pending'`
- Allows quick access to items needing admin review

### Database Support

**✅ Supported:**
- List all tables: Database metadata/configuration
- List entries: `SELECT * FROM {table}`
- Add entry: `INSERT INTO {table}`
- Delete entry: `DELETE FROM {table} WHERE id = X`
- Edit entry: `UPDATE {table} SET ... WHERE id = X`
- Filter pending: `SELECT * FROM {table} WHERE status = 'pending'`

**Crowdsourced Tables with Pending Filter:**
- `levels` - `status = 'pending'`
- `roles` - `status = 'pending'`
- `countries` - `status = 'pending'`
- `locations` - `status = 'pending'`
- `events` - `status = 'pending'`

**Admin-Only Tables:**
- `first_programs` - Admin CRUD only
- `seasons` - Admin CRUD only
- `badges` - Admin CRUD only (auto-created, but admin manages)
- `badge_thresholds` - Admin CRUD only
- `users` - Admin can manage (with restrictions)

---

## Admin Screen 2: Statistics

### Description
Statistics dashboard for admins.

### Status
- **Details TBD** - To be defined later

### Potential Statistics (Future)
- Total users
- Total engagements
- Pending approvals count
- Badge distribution
- Engagement trends
- (Other metrics as needed)

### Database Support
- ✅ Can compute statistics from existing tables
- Specific queries TBD based on requirements

---

## Admin Screen 3: Back to User View

### Description
Switch back to regular user interface.

### Functionality
- Returns to user navigation (Me, Engagement, All, Settings)
- Admin retains admin privileges
- Can switch back to Admin UI from Settings

---

## Admin Workflow Integration

### Crowdsourced Approval Workflow
1. Admin navigates to **Tables**
2. Applies **"Admin Action Required"** filter
3. Sees all pending entries across all Crowdsourced tables
4. Selects table → Reviews pending entries
5. Edits entry → Changes `status` to 'approved' or 'rejected'
6. Saves → Triggers notification to proposing user (Flow 2)
7. Triggers engagement recognition check (Flow 2)

### Badge Management Workflow
1. Admin navigates to **Tables** → **badges**
2. Sees badges with `status = 'pending_icon'`
3. Edits badge → Uploads icon → Sets `status = 'released'`
4. Saves → Triggers badge checking for all users (Flow 3)

---

## Database Support Summary

**✅ All Admin UI Features Supported:**
- Table CRUD operations
- Pending entries filter
- Status updates (approve/reject)
- Badge management
- User management
- Statistics (can be computed)

**No additional database changes needed** - All features use existing schema.

