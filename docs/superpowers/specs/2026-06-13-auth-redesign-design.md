# Auth Redesign — Login & Register

**Date:** 2026-06-13  
**Status:** Approved

## Problem

Current split-panel layout (teal brand panel left, form right) has critical contrast failures:
- Feature bullet icons (emoji in gray circles) invisible on teal background
- Feature text low-contrast on teal
- TBA logo (white ✦ on white card) hard to see
- ToggleButton for preset selection looks unstyled/broken
- Register form has large dead whitespace at bottom

## Chosen Direction

**Centered minimal — Stark/Tech**

White page background, logo above a bordered card, form inside. No brand panel. Same style as Linear, Vercel, Railway.

Both login and register share the same layout shell.

## Layout

```
[full-page white background, min-height: 100vh]

  [centered column, max-width: 400px, margin: auto]

    [logo row]          ← TBA icon + wordmark, centered
    [auth card]         ← bordered card, border-radius: 12px
      heading + sublink
      form fields
      submit button
    [footer tagline]    ← "Bezpłatnie · Bez karty · Dane w Polsce"
```

### Shared layout component

One new layout `auth-simple.blade.php` replaces both `auth-register-split.blade.php` and `auth-split.blade.php`. Both login and register pages reference it.

## Visual Spec

### Page

- Background: `white` (`#ffffff`)
- Vertical centering: flexbox, `min-height: 100vh`

### Logo row

- TBA icon: `28×28px`, `background: #0d9488`, `border-radius: 7px`, white ✦ centered
- Wordmark: `15px`, `font-weight: 700`, `color: #0f172a`, `letter-spacing: -0.02em`
- Row gap: `8px`, centered horizontally
- Margin-bottom to card: `24px`

### Auth card

- `border: 1px solid #e2e8f0`
- `border-radius: 12px`
- `padding: 28px`
- No box-shadow
- Background: white

### Card heading

- Title (`Zaloguj się` / `Załóż konto`): `17px`, `font-weight: 700`, `color: #0f172a`
- Sublink line (`Nie masz konta? Zarejestruj się`): `12px`, `color: #64748b`, link `color: #0d9488`, `font-weight: 500`
- Margin-bottom to fields: `20px`

### Form fields

- Label: `12px`, `font-weight: 500`, `color: #374151`, margin-bottom `5px`
- Required asterisk: `color: #ef4444`
- Input: `height: 36px`, `border: 1px solid #d1d5db`, `border-radius: 7px`, `background: white`
- Focus ring: `border-color: #0d9488`, `ring: 2px #0d9488/20`
- Gap between fields: `14px`

### Password row (register only)

- Two-column grid (`grid-template-columns: 1fr 1fr`, `gap: 10px`)
- "Hasło" + "Powtórz hasło" side by side

### Branża / preset selector (register only)

- Label: same as other fields
- Options rendered as chip-buttons (inline pill style), NOT Filament ToggleButtons
- Chip default: `border: 1px solid #e2e8f0`, `border-radius: 6px`, `padding: 5px 12px`, `font-size: 11px`, `color: #64748b`, `background: white`
- Chip selected: `border: 1.5px solid #0d9488`, `background: #f0fdfa`, `color: #0d9488`, `font-weight: 600`
- Implementation: CSS override in `theme.css` targeting existing Filament ToggleButtons classes (`.fi-fo-toggle-buttons`, `input[type=radio]` + label). No PHP change to the form field. The hidden radio inputs remain; labels get chip styling via CSS selectors. Avoids custom Blade view.

### Submit button

- Full width, `height: 38px`, `background: #0d9488`, `border-radius: 7px`
- Text: `13px`, `font-weight: 600`, white
- Login: "Zaloguj się" / Register: "Zarejestruj się za darmo"
- Margin-top from last field: `4px` (gap already handles spacing)

### Forgot password link (login only)

- Right-aligned in the password row label, `font-size: 11px`, `color: #0d9488`

### Remember me checkbox (login only)

- Keep Filament's default checkbox, just ensure label is `12px color: #64748b`

### Footer tagline

- Below card, `font-size: 11px`, `color: #94a3b8`, centered
- Login: "Bezpłatnie przez beta · Dane w Polsce"
- Register: "Bezpłatnie przez beta · Bez karty · Dane w Polsce (Hetzner)"
- Margin-top from card: `20px`

## Files to Change

| File | Action |
|------|--------|
| `resources/views/filament/components/layout/auth-simple.blade.php` | **Create** — shared layout for both pages |
| `resources/views/filament/components/layout/auth-register-split.blade.php` | **Delete** — replaced by auth-simple |
| `resources/views/filament/components/layout/auth-split.blade.php` | **Delete** — replaced by auth-simple |
| `resources/views/filament/pages/auth/register.blade.php` | **Edit** — render preset chips instead of ToggleButtons |
| `app/Filament/Pages/Auth/Register.php` | **Edit** — switch `$layout` to `auth-simple`, revert preset field to Radio (hidden, chip-styled) |
| `app/Filament/Pages/Auth/Login.php` | **Edit** — add `protected static string $layout = 'filament.components.layout.auth-simple'` |
| `resources/css/filament/app/theme.css` | **Edit** — add `.auth-chip` styles for the preset selector |

## Out of Scope

- Dark mode on auth pages
- Social login (Google/GitHub)
- Password strength indicator
- Any changes to the panel interior (sidebar, widgets, etc.)
