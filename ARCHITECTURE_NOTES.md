# Architecture Discussion Notes

## Project: HOTR (Hands-on Recognition)

### Context
- **HoT (Hands-on-Technology)**: Non-profit organizing FIRST LEGO League in D-A-CH region (Germany, Austria, Switzerland)
- **FLL (FIRST LEGO League)**: STEM education program with robotics competitions
- **Purpose**: Track volunteer engagement, award badges, display leaderboards

## Tech Stack (Confirmed)
- Frontend: Vue
- Backend: Laravel
- Database: SQL (TBD - PostgreSQL/MySQL/SQL Server)

## Laravel Conventions (Strict)
- **Table Names**: Plural (users, seasons, first_programs)
- **Model Names**: Singular (User, Season, FirstProgram)
- **Primary Keys**: `id` (auto-increment)
- **Foreign Keys**: `{model}_id` (e.g., `first_program_id`)
- **Timestamps**: `created_at`, `updated_at` (auto-managed)
- **Pivot Tables**: Alphabetical order (e.g., `first_program_season`)

## Requirements
- Mobile-first web app (smartphones/tablets)
- Basic support for larger screens
- Database tables and business logic
- Email sending capability
- No external system integrations (except email)

## Core Features (Initial Understanding)
- Volunteer activity logging/recording
- Badge system (earn badges based on engagement)
- Leaderboard functionality
- Email notifications

---

## Architecture Decisions

### Authentication & Registration
- Self-registration for all users
- Admin role for select users
- Email verification via HoT mail server

### Activity Logging
- Users can log their own activities
- Predefined lists of roles, locations, and other elements
- Users can propose additions (admin approval required)

### Crowdsourced Pattern
- **Pattern Name**: "Crowdsourced" (short name for user-proposed entries with admin approval)
- **Description**: Tables with user-proposed entries requiring admin approval
- **Common Fields**: `name`, `description`, `status` (pending/approved/rejected), `sort_order`
- **Workflow**: 
  1. User proposes entry when not found in list
  2. Entry created with status='pending'
  3. Admin reviews and approves/rejects
  4. Approved entries become available to all users
- **Tables Using This Pattern**: levels (and more to come)

### Badge & Leaderboard
- Concept to be discussed

---

## Database Schema

### Tables

#### 1. users
*Status: In discussion*

---

## Questions & Considerations

### [To be filled during discussion]

---

## Recommendations

### [To be filled during discussion]

---

## Implementation Plan

### [To be filled after discussion]

