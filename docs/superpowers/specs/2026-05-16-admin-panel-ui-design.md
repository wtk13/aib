# Admin Panel UI Design — Structural Retheme

**Date:** 2026-05-16
**Status:** Approved
**Scope:** Visual retheme of the Filament PHP admin panel. No functional changes to resources, widgets, or data models.

---

## 1. Design Direction

**Persona:** Ania — 35–50, właścicielka firmy sprzątającej, otwiera panel rano na telefonie lub laptopie.

**Approved direction:** Clean / Structural
- Dark teal sidebar, white main area, sharp 8px border radius
- Professional but warm — not corporate, not toy
- Teal color chosen for its association with cleanliness and freshness

---

## 2. Color Palette

| Token | Hex | Usage |
|---|---|---|
| Primary dark | `#0d9488` | Sidebar background, primary buttons, brand |
| Primary | `#14b8a6` | Accent borders on cards, active states, links |
| Primary light | `#5eead4` | Hover states, icon backgrounds |
| Primary tint | `#ccfbf1` | Badge backgrounds (success/teal) |
| Background tint | `#f0fdfa` | Subtle tinted surfaces |
| Page background | `#f8fafc` | Main content area background |
| Border | `#e2e8f0` | Card borders, table dividers |
| Text primary | `#0f172a` | Headings, stat values |
| Text secondary | `#374151` | Body text, table rows |
| Text muted | `#64748b` | Labels, timestamps |
| Text placeholder | `#94a3b8` | Placeholders, secondary metadata |

**Warning/danger accent:** `#f97316` (orange) for overdue alerts — intentionally not red to stay warm.

---

## 3. Typography

**Font:** Inter (Google Fonts)
- Loaded via Filament's `.font('Inter', provider: GoogleFontProvider::class)`
- Weights in use: 400 (body), 500 (labels), 600 (card titles, nav), 700 (headings, stat values)
- No custom font size scale — uses Filament defaults with Inter substituted

---

## 4. Sidebar

**Background:** `#0d9488` (primary dark)
**Text:** white at 100% (active), 75% opacity (inactive items), 45% opacity (section labels)

Elements:
- **Brand mark:** white 28×28px rounded square with ✦ symbol + "Wyceny" text in white 700 14px
- **Nav items:** `border-radius: 6px`, active state: `rgba(255,255,255,0.18)` background
- **Section labels:** 9px uppercase, 0.1em letter-spacing, `rgba(255,255,255,0.45)`
- **User chip at bottom:** avatar initials circle + name + subdomain, separated by `rgba(255,255,255,0.15)` border

Implementation: Filament sidebar background customized via injected CSS custom properties. The `--sidebar-bg` variable override approach — injected via `renderHook(PanelsRenderHook::BODY_START)` or a Vite-loaded CSS file.

---

## 5. Widget Cards (Dashboard)

**Style:** White card, `border-radius: 8px`, `border: 1px solid #e2e8f0`, `border-top: 3px solid #14b8a6`
**Shadow:** `box-shadow: 0 1px 3px rgba(0,0,0,0.04)`

Stat widget layout:
- Label: 11px uppercase, 0.06em letter-spacing, `#64748b`
- Value: 24px 700 `#0f172a`
- Change indicator: 11px 500, green (`#10b981`) for positive, `#94a3b8` for neutral

Table widget layout:
- Header: 13px 600 `#0f172a` + badge (teal for normal, orange `#fff7ed / #c2410c` for overdue)
- Rows: 12px `#374151`, 1px `#f8fafc` divider, 12px vertical padding
- Colored dot indicator (6px circle) before each row

---

## 6. Login Page

**Layout:** Split — 42% left brand panel + 58% right form panel

**Left (brand):**
- Background: `#0d9488`
- Logo: 56×56px white rounded square with ✦, `border-radius: 14px`
- Brand name: 28px 700 white
- Tagline: "CRM dla małych firm usługowych", 13px, `rgba(255,255,255,0.65)`
- 3 feature bullets with frosted pill icons
- Two decorative circles (SVG/pseudo-element) for depth: `rgba(255,255,255,0.06)`

**Right (form):**
- Background: `#f8fafc`
- Heading "Zaloguj się" 20px 700 `#0f172a`
- Subheading 13px `#64748b`
- Inputs: white bg, `border: 1px solid #d1d5db`, `border-radius: 6px`
- Focus ring: `border-color: #14b8a6`, `box-shadow: 0 0 0 3px rgba(20,184,166,0.12)`
- Submit button: `#0d9488` bg, full width, 13px 600 white

---

## 7. Registration Page

**Layout:** Full-width centered form with Filament wizard (existing `Register.php` already uses `Wizard`)

**Additions:**
- Brand header: teal logo mark + "Wyceny" above the wizard
- Wizard step indicator styled with teal active/completed states
  - Completed: `#0d9488` filled circle with ✓
  - Active: `#14b8a6` filled circle
  - Pending: `#e2e8f0` with `#94a3b8` text
- Step connector line: `#14b8a6` for completed segments, `#e2e8f0` for upcoming

---

## 8. Mobile (Responsive)

Filament is responsive by default. Additional considerations:

- **Topbar on mobile:** teal background, brand mark left, hamburger right
- Dashboard widgets stack to single column
- Stat cards retain teal top border accent
- "Good morning, [name]" greeting visible on mobile dashboard header (via Filament topbar customization)

No custom mobile breakpoint overrides needed — Filament's built-in responsive behavior is sufficient for this scope.

---

## 9. Implementation Approach

All changes go through official Filament 3 extension points — no monkey-patching or overriding compiled assets.

### Filament panel config changes (`AppPanelProvider.php`)
```php
->colors(['primary' => Color::from('#14b8a6')])
->font('Inter', provider: GoogleFontProvider::class)
->brandName('Wyceny')
->favicon(asset('favicon.ico'))
// dark mode not enabled — omit ->darkMode() call entirely for light-only design
->sidebarCollapsibleOnDesktop()
```

### Custom CSS file (`resources/css/filament/app.css`)
Loaded via `->viteTheme('resources/css/filament/app.css')` in the panel provider.

Contains:
1. Sidebar background override: `--c-bg-sidebar: #0d9488`
2. Sidebar text color overrides
3. Card top border accent
4. Any small component tweaks that can't be done via Filament config

### Custom login view
Filament allows replacing the login page via `.login(CustomLogin::class)`. A custom `Login.php` page renders the split-panel layout via a custom Blade view.

### No changes to:
- Resources (ClientResource, JobResource)
- Widgets logic (only visual CSS affected)
- Data models or migrations
- Authentication logic

---

## 10. Out of Scope

- Dark mode (deferred — light-only for now)
- Custom illustrations or SVG icons beyond brand mark
- Animated widgets
- Custom empty states
- Second vertical preset styling
