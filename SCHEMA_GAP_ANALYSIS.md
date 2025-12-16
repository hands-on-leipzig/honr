# Schema Gap Analysis - Missing Components Review

## Current Tables (13 total)

1. users
2. email_verification_tokens
3. first_programs
4. seasons
5. levels (Crowdsourced)
6. roles (Crowdsourced)
7. countries (Crowdsourced)
8. locations (Crowdsourced)
9. events (Crowdsourced)
10. engagements
11. badges
12. badge_thresholds
13. earned_badges

---

## Potential Missing Components

### 1. Leaderboard System ⚠️ **MENTIONED IN REQUIREMENTS**

**Status**: Leaderboard mentioned in requirements but no tables defined

**Options**:
- **Option A**: Computed on-the-fly (recommended for MVP)
  - Query `earned_badges` and `engagements` tables
  - Calculate rankings dynamically
  - Cache results if performance needed
  - **Pros**: Simple, no extra tables, always current
  - **Cons**: Can be slow with many users

- **Option B**: Cached leaderboard table
  - `leaderboard_snapshots` table
  - Fields: user_id, rank, score, period (all_time, yearly, monthly), calculated_at
  - Updated periodically via background job
  - **Pros**: Fast queries, can show historical rankings
  - **Cons**: More complex, needs background jobs, data can be stale

**Recommendation**: Start with Option A (computed), add Option B later if needed

---

### 2. Notification System 📧 **EMAIL MENTIONED**

**Status**: Email sending mentioned, but no notification tracking

**Current State**: 
- Email sending capability mentioned in requirements
- No tracking of what emails were sent, when, or delivery status

**Options**:
- **Option A**: No tracking table (recommended for MVP)
  - Send emails via Laravel Mail
  - Rely on email service provider logs
  - **Pros**: Simple, less overhead
  - **Cons**: No in-app tracking

- **Option B**: Notification tracking table
  - `notifications` table
  - Fields: user_id, type (badge_earned, engagement_recognized, etc.), sent_at, status, email_subject
  - **Pros**: Full audit trail, can resend, track delivery
  - **Cons**: More complexity, storage overhead

**Recommendation**: Start with Option A, add Option B if needed for debugging/resending

---

### 3. Admin Action Log 🔍 **USEFUL FOR AUDIT**

**Status**: Not mentioned, but useful for admin accountability

**Purpose**: Track who approved/rejected what, when

**Potential Table**: `admin_actions`
- Fields: id, admin_user_id, action_type (approve, reject, create, update, delete), target_type (level, role, etc.), target_id, details (JSON), created_at
- **Pros**: Full audit trail, accountability, debugging
- **Cons**: Additional storage, complexity

**Recommendation**: Nice to have, but not critical for MVP. Can add later.

---

### 4. Badge Earning History 📊 **OPTIONAL ENHANCEMENT**

**Status**: Current design tracks current state, not history

**Current**: `earned_badges` tracks current threshold level
**Missing**: History of when each threshold was reached

**Options**:
- **Option A**: Current design (recommended)
  - Only track current level
  - **Pros**: Simple, sufficient for most use cases
  - **Cons**: Can't show "earned silver on Jan 15, gold on Mar 20"

- **Option B**: Threshold history table
  - `earned_badge_thresholds` table
  - Fields: id, earned_badge_id, threshold_id, earned_at
  - **Pros**: Full history, can show progression timeline
  - **Cons**: More complexity, storage overhead

**Recommendation**: Option A for MVP. Option B can be added if users want to see progression history.

---

### 5. User Preferences/Settings ⚙️ **OPTIONAL**

**Status**: Basic preferences in users table (consent_to_newsletter)

**Current**: `users.consent_to_newsletter`
**Missing**: Other preferences (email frequency, privacy settings, etc.)

**Options**:
- **Option A**: Add fields to users table (recommended for MVP)
  - Add fields as needed (email_frequency, show_profile, etc.)
  - **Pros**: Simple, no joins needed
  - **Cons**: Can bloat users table

- **Option B**: Separate user_preferences table
  - Key-value or JSON storage
  - **Pros**: Flexible, doesn't bloat users table
  - **Cons**: More complex queries

**Recommendation**: Option A for MVP. Add fields to users table as needed.

---

### 6. Statistics/Analytics 📈 **OPTIONAL**

**Status**: Not mentioned, but useful for reporting

**Purpose**: Pre-calculated statistics for dashboards/reports

**Potential Tables**:
- `user_statistics` - pre-calculated stats per user (total engagements, badges, etc.)
- `system_statistics` - overall system stats (total users, engagements, etc.)

**Recommendation**: Not needed for MVP. Can compute on-the-fly or add later.

---

### 7. Email Templates 📝 **OPTIONAL**

**Status**: Not mentioned

**Purpose**: Store customizable email templates

**Options**:
- Store in code/config files (recommended for MVP)
- Database table for admin-editable templates

**Recommendation**: Code/config files for MVP. Database table only if admins need to edit templates.

---

### 8. Password Reset Tokens ✅ **HANDLED BY LARAVEL**

**Status**: Laravel provides `password_reset_tokens` table automatically

**No action needed** - Laravel handles this

---

### 9. User Sessions ✅ **HANDLED BY LARAVEL**

**Status**: Laravel handles sessions (database or file-based)

**No action needed** - Laravel handles this

---

### 10. Failed Login Attempts 🔒 **SECURITY FEATURE**

**Status**: Not mentioned, but good security practice

**Purpose**: Track failed login attempts, implement rate limiting

**Options**:
- Laravel's built-in throttling (recommended)
- Custom `failed_login_attempts` table if more control needed

**Recommendation**: Use Laravel's built-in throttling. Custom table only if needed.

---

## Summary & Recommendations

### ✅ **Well Covered**
- User authentication & verification
- Engagement tracking
- Badge system (definitions, thresholds, earned badges)
- Crowdsourced data management
- Core business logic

### ⚠️ **Mentioned but Not Designed**
1. **Leaderboard** - Can compute on-the-fly for MVP
2. **Email notifications** - Can use Laravel Mail without tracking table

### 💡 **Nice to Have (Not Critical)**
1. **Admin action log** - Add later if needed for audit
2. **Badge threshold history** - Add later if users want progression timeline
3. **User preferences** - Add fields to users table as needed
4. **Statistics tables** - Compute on-the-fly or add later

### ✅ **Handled by Framework**
- Password reset tokens (Laravel)
- User sessions (Laravel)
- Failed login attempts (Laravel throttling)

---

## Final Recommendation

**The current schema is complete for MVP!** 

The only potential gap is the **leaderboard**, but it can be computed dynamically from existing tables (`engagements`, `earned_badges`). No additional tables needed for initial release.

Consider adding later (if needed):
- Leaderboard cache table (if performance becomes issue)
- Admin action log (if audit trail needed)
- Badge threshold history (if progression timeline desired)

