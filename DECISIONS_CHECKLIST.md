# Pre-Implementation Decisions Checklist

## Critical Decisions (Must Decide Before Starting)

### Infrastructure & Technology
- [ ] **Database**: PostgreSQL / MySQL / SQL Server (Skipped - waiting for hosting decision)
- [x] **Laravel Version**: Laravel 11 ✓
- [x] **Vue Version**: Vue 3 ✓
- [x] **UI Framework**: Tailwind CSS + Headless UI (same as FLOW project) ✓
- [x] **Deployment Platform**: Hetzner VPS with Docker (same as FLOW project) ✓
- [x] **Email Service**: Microsoft O365 (HoT Mail Server) with Graph API ✓
- [x] **Icon Storage**: Local Filesystem (Laravel storage) - can migrate to Hetzner Storage Box later ✓
- [x] **Map Service**: Leaflet with OpenStreetMap (free) ✓

### Architecture
- [x] **Authentication**: Laravel Sanctum ✓
- [x] **API Structure**: RESTful API with Laravel API Resources ✓
- [x] **State Management**: Pinia (same as FLOW project) ✓
- [x] **Form Validation**: Laravel validation only (backend) ✓
- [x] **Testing Strategy**: Feature Tests (Laravel) for critical workflows ✓
- [x] **Localization**: Hardcoded German strings (for MVP) ✓

### Business Logic
- [x] **Badge Threshold Values**: 
  - "Grow" Role Badges: 1, 5, 20, 50 engagements (4 levels)
  - Regional Partner & Coach: 5, 10, 15 seasons (pending colleague confirmation)
  - Level borders: None (level 1), Bronze (level 2), Silver (level 3), Gold (level 4)
  - Icons per role, borders applied visually in UI
  - No level names, visual borders only ✓
- [x] **Remember Login**: Laravel's built-in "Remember Me" token ✓
- [ ] **Statistics Requirements**: Define what statistics to show

## Nice-to-Have Decisions (Can Decide During Implementation)

- [ ] **Caching Strategy**: Redis / Memcached / File cache
- [ ] **Queue System**: Database / Redis / SQS
- [ ] **File Upload Limits**: Max file size, allowed types
- [ ] **Rate Limiting**: API rate limits, login attempts
- [ ] **Logging**: Log level, log storage, monitoring
- [ ] **Backup Strategy**: Database backups, file backups

## Notes

- Mark decisions as you make them
- Document decisions in `ARCHITECTURE_NOTES.md`
- Update implementation plan if decisions affect timeline
- Some decisions may need stakeholder input (e.g., badge thresholds)

