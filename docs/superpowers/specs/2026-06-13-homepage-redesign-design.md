# Homepage Redesign — Dark & Confident Design Spec

**Date:** 2026-06-13
**Status:** Approved
**Supersedes:** `2026-05-16-homepage-design.md`

---

## Goal

Replace the current generic teal-gradient AI-looking homepage with a dark, human, AI-confident landing page that converts Ania (35–50, właścicielka firmy sprzątającej, telefon, między zleceniami) to beta signup. Differentiate from Polish competitors (Fakturownia, ifirma, Sugester) who all use white+blue with feature-list headlines.

**Success metric:** Visitor hits page → clicks "Wypróbuj za darmo" or "Załóż konto". Mobile-first, Lighthouse ≥ 90.

---

## Research Context

Competitor analysis (June 2026) found:
- Polish tools use feature-list H1s ("Prosty program do faktur i KSeF") — low bar
- No Polish competitor uses warm/dark visual identity
- No one owns AI-native positioning in the PL service SMB space
- International leaders (Jobber, HoneyBook, Dubsado) use outcome-first headlines + precise social proof + real customer photography
- Key gap: "between jobs, on your phone" framing is unowned in Poland

---

## Visual Direction: Dark & Confident

**Palette:**
- Background: `#0d0d14` (near-black, blue-tinted)
- Surface: `#161622`, `#1a1a2e`
- Accent: `#4ade80` (bright green — stands out on dark, signals "go", tech-credible)
- Text: `#ffffff`, `rgba(255,255,255,0.55)`, `rgba(255,255,255,0.35)`
- Border: `rgba(255,255,255,0.07)`, `rgba(255,255,255,0.12)`

**Typography:** System stack (`-apple-system, BlinkMacSystemFont, 'Inter', sans-serif`), font-weight 900 for headlines, tight letter-spacing (-1px to -2px on large headings).

**Inspiration:** Linear, Resend, Raycast — dark SaaS done confidently. Not cold/corporate; warm through copy and green accent, not through palette.

---

## Architecture

**Tech:** Laravel Blade + Tailwind CSS + Alpine.js. No Livewire on the public page.

**Files to modify/create:**
- `resources/views/home.blade.php` — full replace (current content is old teal design)
- `resources/css/filament/app/theme.css` — no changes (Filament-only)
- `resources/views/layouts/public.blade.php` — verify exists; create if not (public layout separate from Filament)
- `routes/web.php` — already `Route::view('/', 'home')`, no change needed

**Alpine.js:** Already available via `resources/js/app.js` (loaded by current homepage). Keep existing setup.

---

## Page Structure

### 1. Navigation (sticky, blur backdrop)
- Left: Logo "TBA" (wordmark)
- Center: Links — Funkcje / Branże / Cennik (no Blog link — no blog route exists yet)
- Right: "Zacznij za darmo" CTA button (green background)
- Background: `rgba(13,13,20,0.85)` + `backdrop-filter: blur(12px)` on scroll
- Border-bottom: `1px solid rgba(255,255,255,0.06)`

### 2. Hero Section
**Headline (H1):** "Prowadzisz firmę między zleceniami."
- "między zleceniami" is the key phrase — evokes the real context (phone, in the car, between jobs)
- Font: 62px / weight 900 / letter-spacing -2px

**Subheadline:** "TBA to AI asystent, który robi papierkową robotę za Ciebie — wyceny, maile, plan dnia. Wszystko z telefonu, w minutę."
- 18px / rgba(255,255,255,0.55)

**Badge above H1:** "Beta — bezpłatne do końca 2026" with green dot indicator

**CTAs:**
- Primary: "Wypróbuj za darmo — 0 zł" (green button, #4ade80, dark text)
- Secondary: "Zobacz jak działa" (ghost border + play icon)

**Trust line below CTAs:** "Bez karty kredytowej · Gotowe w 3 minuty · Działa na telefonie"

**Hero visual:** App mockup (browser frame) showing the TBA dashboard:
- Sidebar with nav items
- AI suggestion card: "Klient Marek Wiśniewski czeka na wycenę... Sugeruję: 420–480 zł"
- Stats row: Zlecenia / Przychód / Ocena
- This demonstrates the product immediately without requiring user imagination

**Background:** Radial gradients — green glow from top center, purple tint from right.

### 3. Social Proof Strip
- Label: "Zaufali nam właściciele firm z całej Polski"
- Row of industry pill-badges: 🧹 Czyste Wnętrza / 🔧 Remo-Fix Kraków / 📸 Foto Nowak / 📚 Korepetycje Marek / 🌿 Ogród Pro
- Muted styling (rgba background + border) — placeholder names until real customers exist
- Update with real customer names before launch

### 4. Features Section (6 cards)
Section label: "Jak działa"
Title: "AI robi papierologię. Ty robisz zlecenia."

Cards (3-column grid):
1. **Wyceny w minutę** — głosowo lub tekstem, AI przygotuje i wyśle
2. **Plan dnia bez chaosu** — wszystkie zlecenia w jednym miejscu
3. **Odpowiedzi na maile** — AI sugeruje, Ty zatwierdzasz jednym kliknięciem
4. **Raport miesiąca** — przychody i powracający klienci bez Excela
5. **Przypomnienia dla klientów** — automatyczne SMS/e-mail przed wizytą
6. **Wszystko z telefonu** — iOS i Android, bez laptopa

Card style: `rgba(255,255,255,0.02)` background, `1px solid rgba(255,255,255,0.07)` border, green hover state.

### 5. Testimonials (3 cards)
Section title: "Co mówią właścicielki firm sprzątających"
- 3 testimonials with name, company, 5-star rating, specific outcome
- **IMPORTANT:** Replace placeholder testimonials (Ania K., Marek W., Kasia N.) with real quotes from Ania (design partner) before launch. Do not ship with fictional testimonials.
- Card style: `#161622` background, subtle border

### 6. Pricing Section
**Beta plan (featured):**
- 0 zł / miesiąc
- Full access through end of 2026
- No credit card required
- CTA: "Zacznij teraz — za darmo"

**Pro plan (ghosted, future):**
- 99 zł / miesiąc (od 2027)
- "Dołącz do listy oczekujących"
- This primes users that value exists; don't commit to exact price yet if uncertain

### 7. Final CTA Band
Headline: "Zacznij zarządzać firmą jak masz czas."
- "jak masz czas" = "when you have time" in Polish, also reads as "properly" — double meaning
- Green radial glow from bottom
- Large primary CTA button
- Repeat trust line

### 8. Footer
- Logo left
- Copyright + domain center
- Prywatność / Regulamin links right

---

## Copy Principles

1. **Talk to Ania, not to "użytkownicy"** — "prowadzisz firmę", "Twoje zlecenia", second-person singular
2. **Konkretne korzyści, nie funkcje** — "wycena w minutę" not "moduł wycen"
3. **Polskie realia** — "między zleceniami", "plan dnia", "bez karteczek i Excela"
4. **AI bez strachu** — AI is the tool, Ania is in control ("AI sugeruje, Ty zatwierdzasz")
5. **Beta energy** — "bezpłatne do końca 2026" creates urgency without fake countdown timers

---

## Tailwind Implementation Notes

The current `home.blade.php` uses Tailwind classes. The new design uses custom CSS for complex properties (radial gradients, backdrop-filter, precise rgba values) that are verbose in Tailwind. Use a `<style>` block in the Blade template or a dedicated `resources/css/home.css` compiled via Vite.

**Recommended approach:** `<style>` block in `home.blade.php` for hero/complex sections, Tailwind classes for simple layout/spacing throughout.

Do NOT modify `resources/css/filament/app/theme.css` — that is Filament-only.

---

## Responsive Behavior

- Mobile-first: single column, stacked layout
- Nav: collapse links on mobile, keep logo + CTA
- Hero H1: 36px on mobile (62px desktop)
- Features: 1-column on mobile, 2-column tablet, 3-column desktop
- App mockup: scale down or hide sidebar on mobile (show only main panel)
- Testimonials: 1-column mobile, 3-column desktop
- Pricing: stacked 1-column mobile, 2-column desktop

---

## What NOT to Include

- Industry switcher (Alpine.js widget from old design) — remove; not needed for MVP
- Testimonial quote strip from old design — replaced by proper testimonial cards
- "AI Wycena / Notatki Głosowe / Chat" feature sections from old design — collapsed into 6-card grid
- FAQ accordion — defer to later sprint; not needed for initial conversion
- Any dark mode toggle — page IS dark mode by design

---

## Pre-Launch Checklist

Before shipping to production:
- [ ] Replace fictional testimonials with real quotes (minimum: Ania's quote)
- [ ] Replace placeholder social proof company names with real customer names (or remove strip)
- [ ] Set accurate Pro plan pricing (or remove pricing section if not decided)
- [ ] Add real OG image for social sharing
- [ ] Verify Lighthouse mobile score ≥ 90
- [ ] Confirm Alpine.js loads correctly on `/` without Filament interference
