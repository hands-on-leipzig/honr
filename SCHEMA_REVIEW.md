# Schema Consistency Review

## Issues Found & Recommendations

### 1. Status Field Inconsistencies

**Issue:** Some tables marked as "Defined" instead of "Finalized"
- `email_verification_tokens`: Status = "Defined" (should be "Finalized")
- `levels`: Status = "Defined - uses Crowdsourced pattern" (should be "Finalized")

**Recommendation:** Update to "Finalized" for consistency

---

### 2. Timestamp Fields

**Issue:** `email_verification_tokens` missing `updated_at`
- All other tables have both `created_at` and `updated_at`
- This table only has `created_at`

**Recommendation:** 
- Add `updated_at` for consistency, OR
- Document why it's intentionally omitted (tokens are immutable after creation)

---

### 3. Foreign Key Constraint Documentation

**Issues:**
- `email_verification_tokens.user_id`: Missing "not null" in constraints section
- `seasons.first_program_id`: Has "not null" in constraints, but relationship section doesn't explicitly state it
- Some tables have explicit "not null" in constraints, others rely on relationship section

**Recommendation:** 
- Ensure all FK constraints are explicitly documented in both "Constraints" and "Relationships" sections
- Standardize: Always document "not null" in constraints section for FKs

---

### 4. Use Case References

**Issue:** Use cases reference "jobs table" instead of "engagements"
- `levels` use case #1: "User enters volunteer engagement (jobs table)"
- `roles` use case #1: "User enters volunteer engagement (jobs table)"
- `countries` use case #1: "User enters volunteer engagement (jobs table)"
- `locations` use case #1: "User enters volunteer engagement (jobs table)"
- `events` use case #1: "User enters volunteer engagement (jobs table)"

**Recommendation:** Update all references from "jobs table" to "engagements table"

---

### 5. Crowdsourced Pattern Status Documentation

**Issue:** Inconsistent pattern notation
- `levels`: "Status: Defined - uses Crowdsourced pattern"
- `roles`: "Status: Finalized - uses Crowdsourced pattern"
- `countries`: "Status: Finalized - uses Crowdsourced pattern"
- `locations`: "Status: Finalized - uses Crowdsourced pattern"
- `events`: "Status: Finalized - uses Crowdsourced pattern"

**Recommendation:** Standardize to "Status: Finalized - uses Crowdsourced pattern" for all

---

### 6. Missing Constraints Documentation

**Issues:**
- `email_verification_tokens`: Missing constraints section
  - `user_id` should be not null
  - `email` should be not null
  - `token` should be not null, unique
  - `type` should be not null
  - `expires_at` should be not null

**Recommendation:** Add constraints section for `email_verification_tokens`

---

### 7. Index Naming Consistency

**Observation:** All indexes are well-documented, but:
- Some tables list indexes in "Indexes" section
- All follow same pattern (Primary, Unique, Index)

**Status:** ✓ Consistent

---

### 8. Foreign Key Delete Behavior

**Current State:**
- Most FKs: `restrict on delete` ✓
- `email_verification_tokens.user_id`: `cascade on delete` (intentional - tokens should be deleted with user)

**Status:** ✓ Consistent and appropriate

---

### 9. Status Enum Values

**Current State:**
- `users.status`: enum (requested, confirmed)
- Crowdsourced tables: enum (pending, approved, rejected)
- `engagements.is_recognized`: boolean (different pattern, intentional)

**Issue:** Status enum values not consistently documented as "extensible, TBD" or with full list

**Recommendation:** 
- Document all status enums consistently
- Note which are extensible vs. final

---

### 10. Table Structure Consistency

**Observation:** All tables follow same structure:
- Fields section ✓
- Indexes section ✓
- Constraints section ✓
- Relationships section ✓
- Use Cases section ✓
- Notes section ✓

**Status:** ✓ Consistent

---

## Summary of Required Changes

1. ✅ Update `email_verification_tokens` status to "Finalized"
2. ✅ Update `levels` status to "Finalized - uses Crowdsourced pattern"
3. ⚠️ Decide on `updated_at` for `email_verification_tokens` (add or document omission)
4. ✅ Add constraints section to `email_verification_tokens`
5. ✅ Update all "jobs table" references to "engagements table"
6. ✅ Ensure all FK constraints explicitly documented in constraints section
7. ✅ Standardize status enum documentation

