# Prompt for Role Icons Improvement Chat

Copy and paste this prompt to start a new chat session:

---

**Context: Role SVG Icon Generation System**

I'm working on a Laravel + Vue.js application for FIRST LEGO League volunteers. We have a system that generates SVG icons for different volunteer roles, but the current icons are not visually distinctive enough and need improvement.

**Current System:**
- Location: `backend/app/Console/Commands/GenerateRoleIcons.php`
- Command: `php artisan roles:generate-icons --generate-svgs`
- Icons are stored in: `storage/app/public/logos/roles/` as `{role_id}.svg`
- Database: `roles` table has a `logo_path` column
- Current icons are simple line-art SVGs (64x64 viewBox, stroke-based, single color)

**The Problem:**
The programmatically generated icons don't provide clear visual clues to distinguish between different roles. They're too generic and not intuitive.

**What I Need:**
I want to work collaboratively to create better, more distinctive SVG icons for each role. The icons should:
- Be visually recognizable and intuitive
- Work well as badges (circular display, used in badge system)
- Be consistent in style
- Clearly differentiate between role types

**Available Roles:**
The system has roles like:
- Moderator:in
- Coach:in / Coach:in Challenge / Coach:in Explore
- Juror:in (and various specialized jurors: Roboter-Design, Grundwerte, Teamwork, Forschung, SAP Sonderpreis)
- Schiedsrichter:in / Ober-Schiedsrichter:in
- Robot-Checker:in
- Live Challenge Juror / Live Challenge Leiter:in
- Jury-Leiter:in
- Regional-Partner:in Challenge
- Gutachter:in

**Technical Details:**
- Icons are displayed in a circular badge format with borders (bronze/silver/gold based on level)
- Size: 64x64 viewBox
- Current style: stroke-based, single color (#1f2937)
- Format: SVG stored as files, referenced via `logo_path` in database

**Goal:**
Create improved SVG icons that are more visually distinctive and intuitive. I'm open to:
- Iterative design process
- Category-based approach (group similar roles)
- Reference-based design (I can provide descriptions/requirements)
- Style improvements (filled shapes, better symbols, etc.)

Let's start by reviewing the current icons and then work together to create better ones.

---

