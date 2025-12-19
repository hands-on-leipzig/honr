# Contact Link Feature Proposal

## Overview
Allow users to provide a contact link (LinkedIn, Instagram, email, etc.) that is displayed on their profile for others to contact them.

---

## Database Changes

### Migration: `add_contact_link_to_users_table.php`

**New Field:**
- `contact_link` (string, nullable)
  - Type: `VARCHAR(255)` or `TEXT` (if longer URLs needed)
  - Nullable: Yes (optional field)
  - Default: `NULL`
  - Validation: Should be a valid URL format

**Considerations:**
- Should we also store a `contact_link_type` (enum: 'linkedin', 'instagram', 'email', 'other')?
  - **Recommendation**: Start simple with just `contact_link` field. Can add type later if needed.
- Should we validate URL format in database or application layer?
  - **Recommendation**: Application layer validation (more flexible, can handle mailto:, tel:, etc.)

---

## Backend Implementation

### 1. Migration
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('contact_link')->nullable()->after('short_bio');
});
```

### 2. User Model (`app/Models/User.php`)
- Add `contact_link` to `$fillable` array
- No special casting needed (string)

### 3. UserController (`app/Http/Controllers/Api/UserController.php`)

**Update `updateProfile()` method:**
- Add `contact_link` to validation rules
- Validation: `'contact_link' => 'sometimes|nullable|url|max:255'`
- Allow `mailto:` and `tel:` protocols (not just http/https)

**Update `show()` method:**
- Include `contact_link` in response (already public, no privacy concerns)

**Update `index()` method:**
- Include `contact_link` in user list response (if needed for search/display)

### 4. Validation Rules
```php
'contact_link' => [
    'sometimes',
    'nullable',
    'string',
    'max:255',
    'regex:/^(https?:\/\/|mailto:|tel:)/', // Allow http, https, mailto, tel
]
```

**Alternative**: Use Laravel's `url` rule with custom validation for mailto/tel

---

## Frontend Implementation

### 1. Settings Page (`SettingsView.vue`)

**Add new menu item:**
- "Kontakt-Link ändern" (or "Kontakt-Link hinzufügen" if empty)
- Opens modal similar to other settings modals
- Input field for URL
- Placeholder: "z.B. https://linkedin.com/in/... oder mailto:email@example.com"
- Help text: "LinkedIn, Instagram, E-Mail oder andere Kontaktmöglichkeit"
- Validation: Show error if invalid URL format

**Modal Structure:**
- Title: "Kontakt-Link ändern"
- Input: URL field
- Help text: Examples of valid formats
- Buttons: Abbrechen, Speichern

### 2. User Profile Display (`AwardsSummaryTab.vue`)

**Location Options:**
1. **Option A**: Next to nickname (icon + tooltip)
2. **Option B**: In a new "Kontakt" section below bio
3. **Option C**: In header area, right-aligned

**Recommendation**: Option A or B - keep it visible but not intrusive

**Icon Options:**
- `LinkIcon` from Heroicons (chain link icon)
- `EnvelopeIcon` if it's an email
- `GlobeAltIcon` for general link
- **Recommendation**: `LinkIcon` (generic, works for all link types)

**Display Logic:**
```vue
<!-- If contact link exists -->
<a 
  :href="displayUser.contact_link" 
  target="_blank" 
  rel="noopener noreferrer"
  class="inline-flex items-center text-gray-600 hover:text-blue-600"
>
  <LinkIcon class="w-5 h-5" />
</a>

<!-- If no contact link -->
<LinkIcon class="w-5 h-5 text-gray-300" />
```

**Styling:**
- Active link: Gray icon, hover to blue
- Inactive (no link): Light gray icon (grayed out)
- Tooltip on hover: Show the link URL or "Kein Kontakt-Link"

### 3. User Detail View (`UserDetailView.vue`)
- No changes needed (uses AwardsSummaryTab which will show the icon)

---

## UI/UX Design Decisions

### 1. Icon Placement
**Recommendation**: Place icon next to nickname in the header area
- Visible immediately
- Doesn't take up extra space
- Clear association with the user

**Alternative**: Small section below bio titled "Kontakt" with icon + link text

### 2. Icon Behavior
- **If link exists**: Clickable, opens in new tab, hover effect
- **If no link**: Grayed out, not clickable, no hover effect
- **Tooltip**: Show full URL on hover (truncated if long)

### 3. Privacy Considerations
- Contact link is public (visible to all authenticated users)
- User controls what they share (can be email, social media, etc.)
- No additional privacy settings needed (user decides what to put in)

### 4. Link Types Support
- `https://linkedin.com/in/...` - Opens LinkedIn
- `https://instagram.com/...` - Opens Instagram
- `mailto:email@example.com` - Opens email client
- `tel:+49123456789` - Opens phone dialer (mobile)
- Any other valid URL

---

## Implementation Steps

### Phase 1: Database & Backend
1. Create migration for `contact_link` field
2. Update User model
3. Update UserController validation and responses
4. Test API endpoints

### Phase 2: Settings UI
1. Add "Kontakt-Link ändern" to SettingsView
2. Create modal for editing contact link
3. Add validation and error handling
4. Test save/update functionality

### Phase 3: Profile Display
1. Update AwardsSummaryTab to show contact icon
2. Add icon next to nickname (or in contact section)
3. Implement click handler (open in new tab)
4. Add tooltip/hover effects
5. Style grayed-out state for missing link

### Phase 4: Testing
1. Test with various link types (http, https, mailto, tel)
2. Test validation (invalid URLs)
3. Test display (with/without link)
4. Test on mobile devices

---

## Open Questions

1. **Icon Placement**: Next to nickname or in separate section?
   - **Recommendation**: Next to nickname (more visible, less space)

2. **Link Type Detection**: Should we detect and show different icons?
   - LinkedIn icon for LinkedIn links
   - Instagram icon for Instagram links
   - Email icon for mailto: links
   - **Recommendation**: Start with generic link icon, can enhance later

3. **Link Preview**: Show full URL or just icon?
   - **Recommendation**: Icon only, show URL in tooltip on hover

4. **Validation Strictness**: How strict should URL validation be?
   - **Recommendation**: Allow http, https, mailto, tel protocols
   - Validate basic URL format but be lenient

5. **Character Limit**: Max length for contact_link?
   - **Recommendation**: 255 characters (standard URL length)

6. **Multiple Links**: Should users be able to add multiple contact methods?
   - **Recommendation**: Start with single link, can expand later if needed

---

## Example UI Mockup

### Settings Page
```
┌─────────────────────────────────┐
│ Kontakt-Link ändern            │
├─────────────────────────────────┤
│ Kontakt-Link                    │
│ [https://linkedin.com/in/...]  │
│                                 │
│ z.B. https://linkedin.com/...  │
│ oder mailto:email@example.com  │
│                                 │
│ [Abbrechen] [Speichern]        │
└─────────────────────────────────┘
```

### Profile Display (with link)
```
← Anna Müller 🔗
   Zum Ändern geh in die Einstellungen
```

### Profile Display (without link)
```
← Anna Müller 🔗 (grayed out)
   Zum Ändern geh in die Einstellungen
```

---

## Alternative Approaches

### Option 1: Separate Contact Section
Add a dedicated "Kontakt" card below bio:
```
┌─────────────────────────────────┐
│ Kontakt                         │
│ 🔗 LinkedIn Profil              │
│    https://linkedin.com/in/...  │
└─────────────────────────────────┘
```

### Option 2: Icon with Dropdown
Icon opens dropdown with link options (if multiple links supported later)

### Option 3: Badge/Button Style
Show as a small button/badge: "[Kontakt]" instead of just icon

---

## Recommendation Summary

1. **Database**: Single `contact_link` field (nullable, VARCHAR(255))
2. **Backend**: Validate URL format, support http/https/mailto/tel
3. **Settings**: Add modal for editing contact link
4. **Display**: Icon next to nickname
   - Active: Gray icon, clickable, opens in new tab
   - Inactive: Light gray icon, not clickable
5. **Start Simple**: Single link, generic icon, can enhance later

This approach is:
- ✅ Simple to implement
- ✅ Flexible (supports any URL type)
- ✅ Non-intrusive (icon doesn't take much space)
- ✅ Extensible (can add link types, multiple links later)

