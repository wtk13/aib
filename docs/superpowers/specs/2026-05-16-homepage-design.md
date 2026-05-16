# Homepage — TBA (tbasystent.pl) Design Spec

**Date:** 2026-05-16
**Status:** Approved for implementation

---

## Goal

A single-page SSR marketing homepage at `tbasystent.pl/` (route `/`) that converts Ania (35–50, female cleaning business owner) to beta signup within 5 seconds on mobile. Secondarily, does not alienate non-cleaning visitors — they see "wkrótce" for their industry.

---

## Architecture

**Tech:** Laravel Blade + Tailwind CSS + Alpine.js. No Livewire, no Vue — the page is static content with one small Alpine widget (branża switcher).

**Files:**
- `resources/views/layouts/public.blade.php` — public layout (head, nav, footer scripts). Separate from Filament's layout.
- `resources/views/home.blade.php` — homepage content, extends public layout
- `resources/css/app.css` — already has Tailwind; no new CSS file needed
- `routes/web.php` — `Route::view('/', 'home')` replacing the current welcome stub

**Alpine.js:** NOT in `package.json` — Filament loads its own Alpine.js instance but only within `/admin`. The public page needs Alpine.js installed separately:
```bash
docker compose run --rm node npm install alpinejs
```
Then add to `resources/js/app.js`:
```js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
```

**SEO:**
- `<title>TBA — Asystent dla firm usługowych | tbasystent.pl</title>`
- `<meta name="description" content="AI asystent dla małych firm usługowych. Klienci, wyceny, grafik — wszystko w jednym miejscu. Bezpłatnie przez beta.">`
- `<link rel="canonical" href="https://tbasystent.pl/">`
- `<meta name="robots" content="index, follow">` — homepage is indexable
- Admin (`/admin/*`) remains `noindex, nofollow` (existing `EnforceNoindex` middleware)
- `lang="pl"` on `<html>`

---

## Section Specs

### 1. Public Layout (`layouts/public.blade.php`)

Wraps all public pages. Includes:
- `<html lang="pl">`
- Vite assets: `@vite(['resources/css/app.css', 'resources/js/app.js'])`
- Google Fonts: Inter 400/600/700 (same as Filament)
- `@yield('head')` slot for per-page meta
- Sticky nav (see §2)
- `@yield('content')` slot
- No footer — final CTA band serves as page close

---

### 2. Sticky Nav

```
[✦ TBA]          [Jak to działa]  [Cennik]          [Wypróbuj →]
```

- **Background:** `bg-teal-600` (matches `#0d9488` brand teal)
- **Logo:** `✦ TBA` in white, bold, links to `/`
- **Nav links:** "Jak to działa" (anchor `#jak-to-dziala`), "Cennik" (anchor `#cennik`) — text-white/80, hidden on mobile (hamburger not needed at this page length)
- **CTA button:** "Wypróbuj →" — white bg, teal text, rounded-lg, links to `/admin/register`
- **Position:** `sticky top-0 z-50`

---

### 3. Hero Section

**Background:** `bg-gradient-to-br from-teal-600 to-teal-700`, white text.

**Layout (mobile-first, lg: two columns):**
- Left column: copy + CTAs
- Right column: AI suggestion mini-card mockup

**Copy:**
```
[AI DLA FIRM USŁUGOWYCH]               ← small caps badge, opacity-70

<h1>Twój biznes.
Twój asystent.</h1>

Klienci, wyceny, grafik — wszystko w jednym miejscu.
AI proponuje ceny, Ty decydujesz.

[Wypróbuj za darmo →]   [Jak to działa ↓]

Bezpłatnie przez beta · Bez karty · Dane w Polsce
```

**Primary CTA:** "Wypróbuj za darmo →" — white bg, teal text, font-bold, rounded-lg, links to `/admin/register`
**Secondary CTA:** "Jak to działa ↓" — white border, white text, rounded-lg, smooth scroll to `#jak-to-dziala`

**Right column — AI suggestion card mockup (white card on teal bg):**
```
✦ Sugestia AI — pani Kowalska
─────────────────────────────
Sprzątanie 90m²          380 zł
Dojazd 18 km              28 zł
─────────────────────────────
Razem                    408 zł   ← teal
```
- White card, rounded-xl, shadow-lg
- On mobile: card appears below the copy, scaled down

---

### 4. Honesty Strip ("Zbudowane razem z Anią")

**Background:** `bg-teal-50`, left border `border-l-4 border-teal-500`

**Layout:** Avatar + quote, full width

**Content:**
```
[A]  Ania, firma sprzątająca · Warszawa

     "Wyceniałam codziennie w Excelu.
      Teraz robię to w 2 minuty i z historią klienta."

     Buduję TBA razem z Anią od dnia pierwszego.
```

- **Avatar:** `[A]` teal circle if no real photo; swap for `<img>` if photo available — add `alt="Ania, właścicielka firmy sprzątającej"`
- **Name:** font-semibold
- **Quote:** italic, text-slate-600
- **Sub-line:** text-xs, text-slate-400

> **Implementation note:** If a real photo is provided, use `<img src="{{ asset('images/ania.jpg') }}" alt="...">` in a `w-12 h-12 rounded-full object-cover` container. Leave the [A] avatar as fallback for now.

---

### 5. Branża Switcher (`id="jak-to-dziala"`)

Alpine.js component. Default selected: `sprzatanie`.

**Heading:** "Dla jakiej branży?" — text-center, font-bold

**Chips (flex-wrap, centered):**
| Chip | `x-data` value | State when selected |
|------|---------------|---------------------|
| ✓ Sprzątanie | `sprzatanie` | bg-teal-600 text-white |
| Remonty | `remonty` | bg-slate-100 text-slate-600 |
| Fotografia | `fotografia` | bg-slate-100 text-slate-600 |
| Korepetycje | `korepetycje` | bg-slate-100 text-slate-600 |
| Inna branża | `inna` | bg-slate-100 text-slate-600 |

**Panel below chips (`x-show`, no transition needed):**

**When `sprzatanie` selected:**
- 3-bullet list of what TBA does for cleaning businesses:
  - "Zapamiętuje każdą klientkę — metraż, klucze, alergie, preferencje"
  - "AI proponuje ceny na podstawie historii i odległości"
  - "Grafik ekipy, cykliczne zlecenia, dojazd doliczony automatycznie"
- Small link: "Wypróbuj za darmo →" → `/admin/register`

**When any other chip selected:**
```
Preset dla [branży] jest w budowie.

Podstawa produktu (klienci, notatki, wyceny) działa dla każdej
firmy usługowej już teraz.
```
- Link: "Wypróbuj już teraz →" → `/admin/register`

No waitlist form. No email collection at this stage.

---

### 6. Value Props (`id="funkcje"`)

**3 columns on desktop, stacked on mobile.**

| Icon | Heading | Body |
|------|---------|------|
| 💡 | Wycenia za Ciebie | AI proponuje cenę z historii zleceń i kosztu dojazdu. Nie musisz pamiętać ile wzięłaś od pani Kowalskiej w lutym. |
| 🧠 | Pamięta klientów | Notatki głosowe z samochodu, zdjęcia, preferencje, alergie — wszystko w jednym miejscu. Zapytaj, odpowie. |
| 📅 | Porządkuje tydzień | Grafik ekipy, cykliczne zlecenia, dojazd doliczony automatycznie. Mniej Excela, więcej spokoju. |

- White cards, rounded-xl, shadow-sm, border border-slate-100
- Icon: 2rem, margin-bottom
- Heading: font-bold, text-slate-900
- Body: text-slate-500, text-sm

---

### 7. Feature Showcase — 3 Alternating Sections

All three sections show **mockups** of upcoming features with an honest "wkrótce" label. Layout: alternating image-left / image-right on desktop, stacked on mobile.

#### 7a. AI Wycena
**Mockup card:**
```
✦ AI WYCENA
Notatka: "90m² mieszkanie, kot, pierwsze sprzątanie generalne"
→ Sugestia: 380 zł + 28 zł dojazd
   Bo: 3 podobne zlecenia 340–420 zł, 18 km od bazy
[Użyj sugestii]  [Zmień]
```
**Copy:**
- Heading: "AI proponuje. Ty decydujesz."
- Body: "Na podstawie historii Twoich zleceń i odległości od domu. Zawsze możesz zmienić cenę — to sugestia, nie decyzja."
- Badge: `Wkrótce — Sprint 3` (text-xs, text-slate-400, italic)

#### 7b. Notatki Głosowe
**Mockup card:**
```
🎙️ ● Nagrywanie... 0:42
────────────────────────────────
"Pani Nowak — nowe mieszkanie, 75 metrów, dwa koty,
klucz pod wycieraczką, prosi o środki bezzapachowe..."
```
**Copy:**
- Heading: "Nagraj z samochodu. TBA przepisze."
- Body: "Whisper transkrybuje notatki po polsku. Trafiają na profil klientki automatycznie, gotowe do przeszukiwania."
- Badge: `Wkrótce — Sprint 5`

#### 7c. Chat o Kliencie
**Mockup card:**
```
Ty: Co obiecałam pani Kowalskiej na maj?

TBA: Obiecałaś generalne sprzątanie po malowaniu salonu
     [notatka z 14 marca]. Prosiła też żeby zabrać dywan
     do prania.
```
**Copy:**
- Heading: "Zapytaj normalnie. Odpowie z dowodami."
- Body: "Przeszukuje notatki i historię zleceń. Cytuje konkretne wpisy. Nigdy nie zgaduje."
- Badge: `Wkrótce — Sprint 6`

**Mockup styling:** `bg-teal-50 border border-teal-100 rounded-xl p-4` — same teal family as brand. Monospace font for "terminal/AI" feel in the chat mockup.

---

### 8. Pricing Section (`id="cennik"`)

**Heading:** "Cennik" — text-center, font-bold

**Layout:** Beta card (highlighted, teal) + 2 future tier cards (muted, opacity-60) side by side on desktop.

| | Beta | Starter | Solo |
|--|------|---------|------|
| Price | **0 zł** | 49 zł/mies | 79 zł/mies |
| Label | Dostępny teraz | od 2027 | od 2027 |
| CTA | Dołącz teraz | — | — |
| Style | bg-teal-600 text-white | bg-white opacity-60 | bg-white opacity-60 |

**Beta card features (bullet list):**
- Wszystkie funkcje bez limitu
- Wsparcie bezpośrednio od twórcy
- Kształtujesz produkt swoim feedbackiem
- Dane w Polsce (Hetzner)

**Sub-line under pricing grid:**
> "Pricing widoczny już teraz, bo ukryte ceny to nasza największa irytacja u konkurencji."

---

### 9. FAQ Section

**6 questions, accordion (`<details>`/`<summary>` — no JS needed):**

1. **Nie jestem techniczna, czy sobie poradzę?**
   Jeśli piszesz na WhatsAppie i robisz zakupy online, poradzisz sobie. Dodanie klientki to 3 pola i kliknięcie.

2. **Ile to kosztuje naprawdę?**
   W becie: 0 zł. Bez karty. Bez ukrytych opłat. Płatne plany od 2027 — widzisz je już teraz w cenniku.

3. **Czy dane moich klientek są bezpieczne?**
   Serwery w Polsce (Hetzner). Szyfrowanie AES-256. Zgodność z RODO. Klucze i kody do mieszkań są szyfrowane — nawet my ich nie widzimy.

4. **Co jeśli chcę wyjść?**
   Eksport wszystkich danych do CSV w jednym kliknięciu. Bez pytań. Twoje dane to Twoje dane.
   > **Impl. note:** CSV export not yet built. If not shipped by homepage launch, change answer to: "Napisz do nas — wyeksportujemy Twoje dane w ciągu 24 godzin."

5. **Co jeśli nie mam internetu w trasie?**
   TBA działa w przeglądarce i dobrze sprawuje się na telefonie. Aplikacja jest mobilna i responsywna — możesz wygodnie korzystać z ekranu telefonu w terenie.

6. **Prowadzę inną branżę niż sprzątanie — to dla mnie?**
   Podstawa (klienci, notatki, wyceny) działa dla każdej firmy usługowej. Szablony branżowe dla remontów, fotografii itp. są w budowie — możesz zacząć już teraz.

---

### 10. Final CTA Band

**Background:** `bg-gradient-to-br from-teal-600 to-teal-700`, centered, white text

```
Gotowa zacząć?

Dołącz do bety. Bezpłatnie. Bez karty.

[Wypróbuj za darmo →]

Bezpłatnie przez beta · Bez karty · Dane w Polsce
```

CTA links to `/admin/register`.

---

## Routing & SEO Checklist

- `Route::view('/', 'home')->name('home');` — replaces current `welcome` stub
- Homepage must pass `SeoSsrTest`: HTTP 200, `<title>` present, `<meta name="description">` 120–160 chars, `<h1>` present (hero headline), canonical tag, controller-rendered text visible in raw HTML (not JS-only)
- Admin routes (`/admin/*`) keep `noindex` via existing `EnforceNoindex` middleware
- `<html lang="pl">` set in public layout

---

## Testing

```php
it('homepage loads with correct SEO tags', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('<title>', escape: false);
    $response->assertSee('TBA', escape: false);
    $response->assertSee('tbasystent.pl', escape: false);
    $response->assertSee('Twój biznes', escape: false);
    $response->assertSee('canonical', escape: false);
});

it('homepage links to register', function () {
    $response = $this->get('/');
    $response->assertSee('/admin/register', escape: false);
});

it('homepage is indexable (no noindex header)', function () {
    $response = $this->get('/');
    $response->assertHeaderMissing('X-Robots-Tag');
});
```

---

## Out of Scope

- Waitlist email collection for non-cleaning industries (deferred — show "wkrótce" text only)
- UTM-based dynamic hero copy (deferred to M6+)
- Blog / content pages
- `/funkcje`, `/cennik` subpages (homepage covers the essentials)
- Cookie consent / analytics (deferred — no analytics cookies in beta per RODO note in sprint-plan)
- Real screenshots of AI features (not built yet — use designed mockups)
