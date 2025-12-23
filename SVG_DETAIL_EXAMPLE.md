# SVG Detail Example: Simple vs Detailed

## Example: Moderator:in Microphone Icon

### Current Version (Simple)
```xml
<g class="icon-fill">
  <rect x="28" y="12" width="8" height="16" rx="1"/>
  <path d="M 30 28 Q 32 30 34 28"/>
  <path d="M 26 32 Q 26 36 32 36 Q 38 36 38 32"/>
</g>
<g class="icon-stroke">
  <path d="M 24 44 L 24 48 M 28 44 L 28 48 M 36 44 L 36 48 M 40 44 L 40 48"/>
</g>
```

**What you see**: Basic microphone shape with stand

### Enhanced Version (More Detail)
```xml
<!-- Microphone body with grille -->
<g class="icon-fill">
  <rect x="28" y="12" width="8" height="16" rx="1"/>
  <!-- Grille lines for detail -->
  <line x1="30" y1="16" x2="30" y2="26" stroke="#ffffff" stroke-width="0.5"/>
  <line x1="34" y1="16" x2="34" y2="26" stroke="#ffffff" stroke-width="0.5"/>
</g>
<!-- Stand with more detail -->
<g class="icon-fill">
  <path d="M 30 28 Q 32 30 34 28"/>
  <rect x="26" y="32" width="12" height="2" rx="1"/>
</g>
<!-- Stand legs -->
<g class="icon-stroke" stroke-width="2">
  <line x1="24" y1="44" x2="24" y2="48"/>
  <line x1="28" y1="44" x2="28" y2="48"/>
  <line x1="36" y1="44" x2="36" y2="48"/>
  <line x1="40" y1="44" x2="40" y2="48"/>
</g>
```

**What you see**: More recognizable microphone with grille, better stand

## Quick Test: Increase UI Size

To test if larger size helps, temporarily change `BadgeIcon.vue`:

```vue
<!-- Line 4: Change from w-12 h-12 to w-16 h-16 -->
<button
  @click="$emit('click')"
  class="relative w-16 h-16 flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity"
>
```

This changes badges from 48px to 64px. You'll see:
- ✅ Same icons, just bigger
- ✅ More breathing room
- ❌ No additional detail (same shapes)

## Recommendation

**Try UI size increase first** (easiest):
1. Change `w-12` to `w-16` in BadgeIcon.vue
2. See if icons are clearer
3. If still not clear enough, then add detail to SVG files

**If you want more detail**, I can update the icon generation to include:
- More visual elements (grilles, buttons, labels)
- Thinner strokes for details
- Better proportions
- More distinctive features per role

