# TBA / Twój Biznes Asystent — SEO Strategy

> **Status:** v0.1 draft, 2026-04-25 (brand updated 2026-05-16). Owner: solo founder. Horizon: M0 → M18.
> **Domain:** `tbasystent.pl` (confirmed available 2026-05-16).
> **Companion docs:** `product-plan.md` §9 (Phase 3 SEO), `sprint-plan.md` S0, `homepage-research.md` §6, `competitor-twojafirma.md` §5.6.
> **Scope:** PL-only Year 1. US strategy explicitly out of scope until M18+.

---

## 1. TL;DR

SEO is **not** the channel that gets us to first 100 paying tenants — that's design-partner-led, FB-group-led, and wife's-network-led (see `product-plan.md` §9 GTM). SEO is the **compounding moat for months 9–24** that turns Wyceny from a niche cleaning tool into the default Polish answer for "program do firmy [branża]". First meaningful organic conversions land **M9–M12**; programmatic scale unlocks **M14–M18**. The three plays that actually matter, in order: (1) **win the "twojafirma.pro alternatywa" + cleaning-vertical comparison SERPs in M6–M9** by being the only properly-SSR'd PL competitor — twojafirma's hash-SPA is a free position-1 (`competitor-twojafirma.md` §5.6); (2) **own the "jak wycenić [usługa]" pricing-guide cluster** with a calculator-anchored pillar per vertical, because nobody in PL has done this with embedded tools and structured data; (3) **lay programmatic foundations in S0–S6** (URL structure, sitemap segmentation, schema, canonical rules) so that the M14 programmatic launch isn't a 6-week refactor. Everything else — link building, AEO, local — is supporting cast.

---

## 2. Strategic positioning vs intent landscape

Four intent clusters matter. Each has a different timeline, moat, and realistic ceiling.

### 2.1 Transactional: "program / aplikacja / CRM dla firmy [branża]"

- **Examples:** `program do firmy sprzątającej`, `aplikacja dla firmy sprzątającej`, `crm dla małej firmy usługowej`, `program do wyceny usług sprzątania`.
- **SERP today:** twojafirma.pro ranks for some, but their `/cennik` and `/funkcje` are 404 to Google (`competitor-twojafirma.md` §5.6) — the homepage carries everything, so they're vulnerable to a competitor with a properly indexed feature/pricing surface. Otherwise the SERP is junk: directory listings, Pipedrive (wrong intent), and 2017-era blog posts.
- **Realistic win:** **Top 3 by M9, position 1 by M12** for `program do firmy sprzątającej` and the 5–8 closest variants. This is the single most important SERP we exist to win.
- **Moat:** Vertical depth + working SSR'd `/funkcje`, `/cennik`, `/dla-firm-sprzatajacych` pages. As we ship Phase 2 verticals (remonty, fotografia, korepetycje) we replicate the moat per vertical.

### 2.2 Informational: "jak wycenić / ile kosztuje [usługa]"

- **Examples:** `jak wycenić sprzątanie mieszkania`, `ile kosztuje sprzątanie po remoncie`, `cennik sprzątania biura`, `stawka za sprzątanie 1m2`, `jak wycenić remont łazienki`, `jak wycenić sesję ślubną`.
- **SERP today:** Mixed. Cleaning-company sites with thin "cennik" pages, a couple of tabloid lifestyle posts, and forums (Wizaz, Forum Budowlane). No SaaS product has bothered to build authoritative pricing guides — this is wide open.
- **Realistic win:** Top 3 within 4–6 months of publishing a 2,500–4,000-word pillar with embedded calculator and FAQ schema. Multiple long-tails (`stawka za sprzątanie 1m2 warszawa`) achievable in 8–12 weeks.
- **Moat:** Original price data (we have it — every quote in the system is a data point), embedded calculator, regular updates with year in title (`Cennik sprzątania 2026`).

### 2.3 Competitor: "twojafirma.pro alternatywa / opinie"

- **Examples:** `twojafirma.pro alternatywa`, `twojafirma opinie`, `twojafirma vs`, `program do firmy sprzątającej opinie`.
- **SERP today:** Near-empty. A few G2-style aggregator stubs.
- **Realistic win:** Top 3 by M7. Low volume but **100% buyer intent** — these searches happen post-trial-frustration.
- **Moat:** None needed; first-mover wins. Build the comparison page, ship it the day the public landing goes live.
- **Hard rule:** No FUD. Honest comparison table. We will lose on "older, more reviews," win on "AI pricing, voice notes, modern UX, working pricing page."

### 2.4 Branded: "TBA" / "Twój Biznes Asystent"

- **Brand decision (final, 2026-05-16):** **Twój Biznes Asystent** @ `tbasystent.pl`. Panel short name: **TBA**. "Wyceny" fully retired as user-facing brand.
  - Earlier candidates (Wyceny, Kwotka, Stawka) retired for various reasons — see `product-plan.md` §3 for the full naming history.
- **SEO implication of "Twój Biznes Asystent":** Long descriptive name — branded queries will be low volume early. Abbreviation "TBA" has generic confusion risk. Mitigation: brand consistently as "TBA — CRM dla [branża]" in title tags and meta descriptions. The domain `tbasystent.pl` is unique enough to be trainable as a bigram for AEO/GEO (§8.4).
- **Pre-launch (M0–M6):** Defend the SERP. Register social handles (@tbasystent). Make sure `tbasystent.pl` ranks #1 for "Twój Biznes Asystent" before launch.
- **Domain stack:** Primary `tbasystent.pl`. Defensive purchases: `twojaasystent.pl`, `tbasystent.com` if available. Skip `wyceny.pl` — no longer the brand. `.pl` TLD signals local trust.
- **Advantage of `.pl` domain:** Stronger local trust signal than `.app` (previous plan). PL Year 1 users expect `.pl` for business tools. No trade-off needed.

---

## 3. Keyword strategy & cluster map

Eight clusters. P0 ships in the first 6 months of content production (M4–M10). P1 ships M10–M14. P2 is Year 2 programmatic / Phase 2 verticals.

### 3.1 Cluster: Cleaning — pricing & how-to (P0, informational)

| Keyword | Intent | Difficulty (qual.) | Notes |
|---|---|---|---|
| `jak wycenić sprzątanie mieszkania` | Informational, buyer-adjacent | Medium — forum-heavy SERP, no strong SaaS | Pillar page anchor |
| `ile kosztuje sprzątanie po remoncie` | Informational, high commercial | Medium-low | Strong long-tail, year-stamped |
| `cennik sprzątania biura` | Informational | Medium | Calculator opportunity |
| `stawka za sprzątanie 1m2` | Informational | Low — almost no good answers | Featured snippet target |
| `jak wycenić sprzątanie po budowie` | Informational | Low | Long-tail, buyer intent |
| `kalkulator wyceny sprzątania` | Tool-seeking | Low volume, high intent | Linkable asset, see §4.b |
| `wzór wyceny sprzątania` | Template-seeking | Low | PDF download lead magnet |

### 3.2 Cluster: Cleaning — software & CRM (P0, transactional)

| Keyword | Intent | Difficulty | Notes |
|---|---|---|---|
| `program do firmy sprzątającej` | Transactional | Medium — twojafirma weak | **Primary money keyword** |
| `aplikacja dla firmy sprzątającej` | Transactional | Medium | Mobile-first angle |
| `program do wyceny usług sprzątania` | Transactional | Low-medium | Differentiator: AI pricing |
| `crm dla firmy sprzątającej` | Transactional | Low | Defines category for us |
| `harmonogram sprzątania program` | Transactional | Low | Feature-led landing |
| `program do rozliczania sprzątaczek` | Transactional | Low | Niche, high intent |

### 3.3 Cluster: Cleaning — operations & ops content (P1, informational, top-of-funnel)

| Keyword | Intent | Difficulty | Notes |
|---|---|---|---|
| `jak prowadzić firmę sprzątającą` | Informational | High volume, low SaaS competition | Hub page |
| `umowa o sprzątanie wzór` | Template-seeking | Medium | Downloadable, lead magnet |
| `jak założyć firmę sprzątającą` | Informational | Medium | Top-of-funnel, weak buyer intent — accept and convert via newsletter |
| `kody pkd firma sprzątająca` | Informational | Low | Easy snippet win |
| `środki czystości do firmy sprzątającej` | Commercial (not ours) | — | **Skip.** We don't sell supplies. |

### 3.4 Cluster: Cross-vertical CRM (P1, transactional, defensive)

| Keyword | Intent | Difficulty | Notes |
|---|---|---|---|
| `crm dla małej firmy` | Transactional | High — Pipedrive owns it | Don't fight head-on; long-tail it |
| `crm dla małej firmy usługowej` | Transactional | Medium | Our actual ICP |
| `prosty crm po polsku` | Transactional | Medium | Differentiator: PL-native, voice |
| `crm z wycenami` | Transactional | Low — almost no competition | Define category |
| `crm dla jednoosobowej działalności` | Transactional | Low-medium | Solo-tier landing |

### 3.5 Cluster: Remonty (P1 — Phase 2 vertical, Q3 2026)

| Keyword | Intent | Difficulty | Notes |
|---|---|---|---|
| `program do firmy remontowej` | Transactional | Medium | Mirror cleaning playbook |
| `jak wycenić remont mieszkania` | Informational | Medium-high — established forums | Calculator essential |
| `kosztorys remontu wzór` | Template | Medium | Big lead magnet |
| `aplikacja do kosztorysów` | Transactional | Medium | Adjacent to specialist tools (Norma, WinBud) — don't compete on detail, compete on speed |
| `program dla brygady budowlanej` | Transactional | Low | Niche |

### 3.6 Cluster: Fotografia ślubna/eventowa (P1 — Phase 2 vertical)

| Keyword | Intent | Difficulty | Notes |
|---|---|---|---|
| `crm dla fotografa ślubnego` | Transactional | Low — Studio Ninja is EN-only | Free real estate |
| `jak wycenić sesję ślubną` | Informational | Low-medium | Pillar candidate |
| `umowa fotograf ślubny wzór` | Template | Medium | Lead magnet |
| `program do zarządzania zleceniami fotograficznymi` | Transactional | Low | Long-tail |
| `cennik fotografa ślubnego 2026` | Informational | Medium | Year-stamped pillar |

### 3.7 Cluster: Korepetycje & Przeprowadzki (P2 — Phase 2 verticals)

Defer keyword research to vertical-launch sprint. Mirror §3.5/§3.6 structure: one transactional CRM keyword + one informational pricing keyword + one template lead magnet. Resist the urge to publish placeholder content before the vertical preset is real.

### 3.8 Cluster: Local — `[branża] [miasto]` (P2 — programmatic, Year 2)

| Pattern | Intent | Difficulty | Notes |
|---|---|---|---|
| `firma sprzątająca [miasto]` | Local, buyer-side (not ours) | — | **Not our SERP.** We're not a marketplace. Skip. |
| `program do firmy sprzątającej [miasto]` | Transactional, low volume | Low | Programmatic candidate, but volume per page is tiny — only worth it once template is reused across verticals (see §6) |
| `cennik sprzątania [miasto]` | Informational | Low-medium | Programmatic with regional price data — strong differentiator |

**Stance:** Resist building "firma sprzątająca w [miasto]" pages. We're SaaS, not a directory. That intent belongs to our customers.

---

## 4. Content strategy & editorial pillars

Three architectural primitives, in this order of build:

### 4.1 Pillar + cluster (the spine)

Per vertical, one **pillar page** ("Wycena usług sprzątania — kompletny przewodnik 2026") at `/przewodnik/wycena-sprzatania`, 2,500–4,000 words, with:

- Embedded calculator (see 4.b)
- Tab navigation: Mieszkania / Biura / Po remoncie / Po budowie / Cyklicznie
- 8–12 internal links to cluster pages (`/przewodnik/wycena-sprzatania/po-remoncie`, etc.)
- FAQPage schema with 6–10 PAA-derived questions
- Author box with wife's name, photo, "8 lat prowadzenia firmy sprzątającej" (E-E-A-T core asset — see §7)
- "Ostatnia aktualizacja: [data]" with real freshness signal
- Single CTA: "Wypróbuj Kwotkę za darmo" — soft, no popup

Cluster pages: 1,200–1,800 words, focused on one long-tail, link back to pillar + sideways to siblings.

**Build order:** Pillar M5 (alongside landing). Three cluster pages M6–M7. Five more M8–M10.

### 4.2 Calculators as linkable assets

`/kalkulator/wycena-sprzatania` — interactive, no email gate, shareable via URL with params (`?m2=80&typ=mieszkanie&po-remoncie=1`). Output: price range + "Zobacz, jak Wyceny liczy to automatycznie z historii klienta" CTA.

Why this matters:

- PL has zero good public cleaning calculators. Owning this is a 2-year link magnet.
- Calculator URLs with params get shared in FB groups → backlinks + branded search lift.
- Reusable React/Livewire component → drop into remonty (`/kalkulator/kosztorys-remontu`), fotografia, korepetycje at marginal cost.

**Hard rule:** Every vertical pillar gets a calculator before getting a blog. The calculator IS the content.

### 4.3 Programmatic (the scaling layer — see §6)

Full plan in §6. Do not start before M12.

### 4.4 AI-assisted content without 2024-update penalties

`product-plan.md` §9 mentions "AI-generated pricing guides per branża, thousands of pages." That phrase is a **trap** if executed naively. Post-March 2024 Helpful Content + Core updates, raw AI output gets demoted. Rules:

1. **Every page must have a non-AI-derivable input.** For pricing guides: real quote data from our DB (anonymized aggregates: "median price for sprzątanie po remoncie in our system, Q1 2026: 14 PLN/m²"). Competitors cannot replicate this.
2. **Human edit pass required.** Wife edits cleaning content. Hire vertical-expert freelancers (1 per vertical) at €0.05/word for edit, not write. Budget: ~€150 per pillar.
3. **No "how to start a business" listicle slop.** If we can't add data, photos, or expert quotes, we don't publish.
4. **Author entity per vertical.** Real person, real LinkedIn, real bio. Cleaning = wife. Remonty = a hired remontowiec advisor (paid €200/month for review + quotes).
5. **No mass-publishing.** Cap content production at 3 pieces/week even when AI lets us do 30. Indexation pacing matters more than volume.
6. **Originality test:** every page must answer "what would only Wyceny know?" If the answer is "nothing," kill the page.

### 4.5 PL-specific angles competitors miss

- **VAT vs ryczałt vs JDG handling per vertical.** Nobody explains "jak wystawić fakturę za sprzątanie na ryczałcie" with a real worked example. We can.
- **PKD code mapping per vertical.** Three sentences each, evergreen.
- **Voice-note workflows for non-technical owners.** Show, don't tell — "obejrzyj jak Ania wycenia mieszkanie głosem w 30 sekund" with a real video. This is content twojafirma cannot fake.
- **Realistic price benchmarks by voivodeship.** Aggregated from our anonymized data once we have N>500 quotes (M9+).
- **"Branża X po polsku" tone.** Most SaaS PL copy reads like translated EN. Wife reviews everything for "this is how Ania actually talks."

---

## 5. Technical SEO foundations (Sprint 0 must-haves)

These are non-negotiable before the public landing ships in S5–S6. Cost of fixing later >> cost of doing right now.

### 5.1 SSR is the law

`sprint-plan.md` S0 already commits to Livewire full-page nav, no hash routing. **Reinforce with a CI check**: every public route must return 200 with full HTML body when fetched with `curl -A "Googlebot"`. Add a `tests/Feature/SeoSsrTest.php` that hits `/`, `/cennik`, `/funkcje`, `/dla-firm-sprzatajacych`, `/przewodnik/wycena-sprzatania` and asserts:

- HTTP 200
- `<title>` present and non-default
- `<meta name="description">` present, 120–160 chars
- `<h1>` present and non-empty
- At least one canonical tag
- No client-side-only content visible (assert key text from controller is in raw HTML)

This is how we avoid being twojafirma.pro (`competitor-twojafirma.md` §5.6).

### 5.2 URL structure & canonicals

- Apex: `tbasystent.pl` — marketing, indexable.
- App: `app.tbasystent.pl` — product, **`X-Robots-Tag: noindex, nofollow` on every response, no exceptions.** The app must never appear in SERPs.
- No trailing slashes. Force lowercase. 301 any variant.
- Canonical strategy: every public page emits self-referencing `<link rel="canonical">`. Calculator URLs with query params canonicalize to the param-less version.
- Pagination: `rel="next"/"prev"` deprecated; just use crawlable links + canonical-to-self per page.

### 5.3 Sitemap & robots

- `/sitemap.xml` is an index pointing at:
  - `/sitemap-pages.xml` — static pages
  - `/sitemap-przewodniki.xml` — pillar + cluster content
  - `/sitemap-kalkulatory.xml` — tools
  - `/sitemap-blog.xml` — blog (when we have one)
  - `/sitemap-programmatic.xml` — Year 2, segmented further by vertical
- Generate via Laravel command, regenerate on content publish, ping GSC.
- `robots.txt`: allow `/`, disallow `/admin`, `/livewire`, `/_debugbar`, `/api`, `/login`, `/register`. Reference sitemap.

### 5.4 Schema.org markup

Implement these on day 1, not later:

- **Organization** on all pages (sitewide footer-injected).
- **WebSite** with SearchAction on homepage.
- **SoftwareApplication** on `/` and `/funkcje` (operatingSystem: "Web", applicationCategory: "BusinessApplication", offers with price).
- **FAQPage** on every pillar/cluster page that has Q&A (most should).
- **HowTo** on `/przewodnik/*` where applicable (e.g., "Jak wycenić sprzątanie po remoncie w 5 krokach").
- **BreadcrumbList** sitewide.
- **Article** with author + datePublished + dateModified on blog content.
- **Skip:** LocalBusiness — we're not a local business, we're horizontal SaaS. Faking it for vertical hubs is spammy and Google catches it. The cleaning vertical hub is a **product page**, not a business location.

### 5.5 Core Web Vitals targets

- LCP < 2.0s (stricter than Google's 2.5s — buys headroom).
- INP < 150ms.
- CLS < 0.05.
- Tailwind purge on. daisyUI tree-shaken. Hero image as AVIF + WebP fallback, `loading="eager"` only above the fold. Self-host fonts. No third-party JS on landing except Plausible (not GA4 — Plausible is faster and GDPR-clean).

### 5.6 hreflang strategy (forward-compatible)

Year 1 is PL-only. **Don't ship hreflang yet** — empty/wrong hreflang is worse than none. But:

- Reserve URL prefix: `/en/` for future EN. Never use country codes (`/pl/` `/us/`) — keeps `tbasystent.pl` clean.
- When EN ships (M18+), every page emits `hreflang="pl"`, `hreflang="en"`, `hreflang="x-default"` (→ EN, since US is the target market then).

### 5.7 Indexing rules

- `tbasystent.pl` — fully indexable.
- `app.tbasystent.pl` — fully blocked (see 5.2).
- Demo / staging — `noindex` + HTTP basic auth + robots disallow. Triple-belt.
- Calculator embed iframe — noindex the iframe URL itself, index the page that hosts it.

---

## 6. Programmatic SEO play (Year 2 main bet)

Aligned with `product-plan.md` §9 Phase 3. **Do not start before M12.** Premature programmatic = thin pages = sitewide quality demotion. Order matters.

### 6.1 Page types (priority order)

1. **`/cennik/[usluga]` — pricing-by-service pages.** ~20–40 per vertical. E.g., `/cennik/sprzatanie-mieszkania`, `/cennik/sprzatanie-biura`, `/cennik/sprzatanie-po-remoncie`. Data source: our anonymized quote data + manual benchmarks. Each page: 800–1,200 words, real number ranges, calculator widget, FAQ schema, 5+ internal links.
2. **`/cennik/[usluga]/[wojewodztwo]` — regional pricing.** ~16 voivodeships × N services. Only ship a region page when we have N≥30 quotes from that region in our DB. Otherwise we're guessing and Google smells it.
3. **`/program-dla/[branza]` — vertical CRM landings.** One per vertical, hand-edited (not programmatic). Already in §3.2.
4. **`/przewodnik/[uslugi]` — informational hubs.** Hand-built pillars (§4.1), not programmatic.

### 6.2 Templates

Each programmatic page type has exactly one Blade template. Variables: title, h1, intro paragraph (AI-generated, human-edited), price data table (DB-driven), comparison block, FAQ (5–8 Qs, AI-generated from query expansion, human-reviewed), CTA, related pages.

### 6.3 Anti-thin-content guardrails

- **Minimum data threshold to publish:** N≥30 real quotes in scope, OR a wife-/expert-reviewed manual benchmark.
- **Content uniqueness check:** automated diff vs sibling pages. If body content >60% similar to a sibling, block publish.
- **Manual sample QA:** wife reviews 1 in 20 published pages, full read-through. Findings update the template.
- **Index pacing:** publish 20 pages/week max, even if 200 are queued. Watch GSC indexed-pages curve; if it flattens or "Discovered, not indexed" spikes, **stop publishing** and audit.
- **Kill switch:** if any programmatic URL pattern's organic CTR drops below 0.5% over 30 days post-impression-stability, mark for noindex.

### 6.4 Internal linking

Programmatic pages link **up** to pillar (`/przewodnik/wycena-sprzatania`), **sideways** to 4–6 siblings, and **down** to nothing. Pillars link **down** to programmatic. Never let programmatic become a closed loop.

### 6.5 Indexing throttle

Submit programmatic sitemaps in chunks of 500. Wait until ≥80% indexed before submitting next chunk. Use IndexNow for fast removal of pages we kill.

---

## 7. Link building & E-E-A-T (1-person team, 0 DA)

Realistic ladder. No paid links, no PBNs, no Fiverr "guest posts." Every tactic below works at our scale.

### 7.1 PL-specific tactics that actually work

1. **Wife's authentic story = the biggest E-E-A-T asset we have.** "Mąż-programista zbudował CRM, bo żona miała dość Excela." That story belongs on `/o-nas`, in every pillar's author box, and as the cold pitch to PL business journalists (Bizblog, Mamstartup, MyCompany, Forbes PL). Pitch one journalist per week from M5 onwards. Conversion rate is brutal (5–10%) but each landed piece is a DR 60+ link.
2. **Branża FB groups.** Identify the 3–5 active PL cleaning groups (e.g., "Firmy sprzątające — wymiana doświadczeń"). Wife participates **as herself, the cleaner**, not as Wyceny. Mentions Wyceny organically only when relevant. Same playbook for remonty (Forum Budowlane, FB), fotografia (FB groups + ŚlubAbc), korepetycje (Korki.pl forums).
3. **Reddit r/Polska, r/przedsiebiorcy, r/programistapl.** Genuine answers. Founder posts under real name. One launch post on r/przedsiebiorcy at M6 — soft, story-led, no link in title.
4. **Branżowe portale:** czystyswiat.pl, prochem.org, onet.biznes — pitch real data ("Aggregated 1,000 cleaning quotes — here's what Polish cleaners actually charge in 2026"). Data-PR works.
5. **Partnerships, not link swaps:** integration with Fakturownia (`product-plan.md` mentions API integration as Phase 2) → mutual mention as integration partner = legit link from a DR 70+ site. Same for iFirma, wFirma.
6. **Lead magnets that get embedded:** `Wzór umowy o sprzątanie 2026 (PDF)` — accountants and HR sites embed-link these. One of these per vertical = 5–10 cumulative high-quality referring domains over 12 months.
7. **Podcasty:** Mała Wielka Firma, Biznes w IT, MarketerPlus. Wife + founder duo is a fresh pitch angle (couple builds SaaS for spouse's business).

### 7.2 What to avoid

- **Paid link networks / "guest post packages" / Fiverr.** Will get flagged within 6–12 months and trigger manual action. Single biggest existential SEO risk for a young site.
- **PBNs.** Same, worse.
- **Mass HARO / Connectively spam.** Quality > quantity. One real journalist quote per month is fine.
- **Reciprocal "let's link to each other" with random SaaS.** Detected, devalued.
- **Parasite SEO (publishing on Medium/Substack to rank for our money keywords).** Risk to brand: Google could conflate our Medium post with Wyceny's authority and we end up competing with our own parasite. Low ROI for PL because Polish-language Medium has weak topical authority. **Skip it.**

### 7.3 E-E-A-T checklist per content piece

- [ ] Named author with bio, photo, real LinkedIn
- [ ] "Ostatnia aktualizacja" date with actual edit log
- [ ] Sources cited (GUS, branża reports, our own data with methodology note)
- [ ] Author qualifications visible (e.g., "8 lat w branży")
- [ ] Editorial review note ("zrecenzowane przez [ekspert] dnia [data]") on programmatic pages

---

## 8. AI search / AEO / GEO layer (the 2026 lever)

The hidden play. When Ania asks ChatGPT "jaki crm dla firmy sprzątającej w Polsce" in 2026–2027, Wyceny should be in the answer. Most PL competitors aren't thinking about this. We will.

### 8.1 Cite-worthy content patterns

LLMs cite content that is: **specific, structured, recent, and attributed**. Optimize for citation, not ranking.

- **Lead each pillar with a 2–3 sentence definitional answer.** "Średnia stawka za sprzątanie mieszkania w Polsce w 2026 roku wynosi 8–14 PLN/m². Cena zależy od trzech czynników: …" — that's the chunk LLMs will pull.
- **Use TL;DR boxes, definition tables, and named-entity-rich text.** "Wyceny to polski CRM z wycenami AI dla firm sprzątających" — explicit self-definition matters.
- **Numbered, scannable answers** to questions phrased the way users ask LLMs.
- **Date stamp aggressively.** "Stan na kwiecień 2026" in the first paragraph. LLMs prefer recent.

### 8.2 Structured data for AI surfaces

- FAQPage schema → directly consumed by SGE/Perplexity.
- HowTo schema → step extraction.
- Article schema with author + about + mentions → entity disambiguation. Crucial: define `about` and `mentions` to "firma sprzątająca", "wycena usług", "CRM" as `Thing` entities so Wyceny gets associated with the topic graph.

### 8.3 `llms.txt`

Ship `/llms.txt` at landing launch (M6). Format per the emerging convention:

```
# Wyceny
> Polski CRM z wycenami AI dla małych firm usługowych (sprzątanie, remonty, fotografia, korepetycje)

## Czym jest Wyceny
- AI sugeruje ceny na podstawie historii klienta
- Notatki głosowe (Whisper PL)
- Harmonogram tygodniowy, prace cykliczne, auto-dojazd

## Główne strony
- [Funkcje](https://tbasystent.pl/funkcje): pełna lista funkcji
- [Cennik](https://tbasystent.pl/cennik): plany 0/49/79/149 PLN
- [Dla firm sprzątających](https://tbasystent.pl/dla-firm-sprzatajacych): use case
- [Przewodnik wyceny sprzątania](https://tbasystent.pl/przewodnik/wycena-sprzatania): pillar

## Kontakt
- hello@tbasystent.pl
```

Plus a `/llms-full.txt` with the full content of the top 20 pages concatenated. Costs nothing, hedges everything.

### 8.4 Brand mention seeding

LLMs train on Reddit, Wikipedia, Stack Exchange, news. Therefore:

- One legit Wikipedia mention if/when we hit press threshold (don't force it).
- Authentic Reddit presence (see §7.1).
- Press hits with the phrase "Wyceny, polski CRM dla firm sprzątających" — that exact bigram gets trained into models.

### 8.5 Measurement

Quarterly: ask ChatGPT, Perplexity, Claude, Gemini, Bielik (PL model) the 10 target queries and log mentions. Track presence, sentiment, and accuracy. There's no GSC for AI search yet — manual tracking is fine at our scale.

---

## 9. Measurement & success criteria

### 9.1 Leading vs lagging

- **Leading indicator:** `Indexed pages × Avg position for top 50 target keywords`. Tracked weekly in GSC + Ahrefs (or Senuto for PL-specific data). Healthy curve = both numbers rising; if indexed pages rises but avg position drops, we have a thin-content problem.
- **Lagging indicator:** `Organic signups → activated tenants (≥1 quote sent in week 1)`. Tied directly to NSM (Weekly Active Quoting per Tenant — see `product-plan.md`).

### 9.2 Milestone targets

| Milestone | GSC indexed | Avg position top-50 | Organic sessions / mo | Organic signups / mo |
|---|---|---|---|---|
| M3 (S0–S6 done, landing live) | 15–30 | n/a (no rankings yet) | <100 | 0–5 (all branded) |
| M6 (public beta) | 40–80 | 30–50 | 200–500 | 5–15 |
| M9 | 80–150 | 15–25 | 800–1,500 | 20–40 |
| M12 | 200–400 | 10–18 | 3,000–6,000 | 60–120 |
| M18 (post-programmatic) | 2,000–5,000 | 8–15 | 15,000–35,000 | 200–400 |

These are **opinionated targets**, not guarantees. Adjust quarterly. If M9 actuals are <50% of target, audit content quality, not budget.

### 9.3 Stack

- **GSC**: source of truth for impressions, clicks, indexation. Daily check at scale, weekly otherwise.
- **GA4** OR **Plausible**: pick Plausible for Year 1 (faster, GDPR-clean, sufficient). Add GA4 only if we need event-stitching across paid + organic, which we won't until Year 2.
- **Senuto** (PL-native rank tracker, ~300 PLN/mo) > Ahrefs for Year 1. Better PL keyword data. Add Ahrefs at M9+ for backlinks.
- **Custom dashboard:** Looker Studio fed by GSC API. Build at M3.

### 9.4 Attribution to NSM

Organic signups are scored against NSM at week 4 and week 12 post-signup. If organic-acquired tenants have lower week-4 NSM than FB-acquired, that's a content/promise mismatch — the page that converted them is overpromising. Audit and fix.

---

## 10. Roadmap aligned to sprint plan

### S0 (now, ~2 weeks): foundations only

- [ ] Buy primary domain: **`tbasystent.pl`**. Defensive: `wyceny.pl` (if available, 301 → .app), `tbasystent.pl` (typo redirect). See §2.4.
- [ ] Set up GSC, Bing Webmaster Tools, Plausible.
- [ ] Reserve social handles (@tbasystent on X, IG, FB, LinkedIn, TikTok, YouTube).
- [ ] Add `tests/Feature/SeoSsrTest.php` per §5.1 to S0 acceptance.
- [ ] Define URL structure and canonical strategy (§5.2) in code conventions doc.

### S1–S4: pre-landing groundwork

- [ ] Schema.org helper trait/component (§5.4) built once, reused everywhere.
- [ ] Sitemap generator command (§5.3).
- [ ] Wife drafts pillar `Wycena sprzątania 2026` (3,000 words, no rush, edited).
- [ ] Calculator component v0 (cleaning, 4 inputs).

### S5–S6: landing + content launch

- [ ] Public landing live at `tbasystent.pl` with `/funkcje`, `/cennik`, `/dla-firm-sprzatajacych`, `/o-nas`, `/kontakt`. All SSR'd, all schema-marked, all tested per §5.1.
- [ ] Pillar `/przewodnik/wycena-sprzatania` live with calculator embed.
- [ ] `twojafirma alternatywa` comparison page live.
- [ ] `/llms.txt` shipped.
- [ ] Submit sitemap to GSC + Bing.

### M4–M6: launch window

- [ ] 3 cluster pages off the cleaning pillar.
- [ ] First 3 PR pitches sent (Bizblog, Mamstartup, MyCompany).
- [ ] Reddit r/przedsiebiorcy launch post.
- [ ] FB group seeding via wife.
- [ ] Newsletter started: monthly "Cennik usług sprzątających w Polsce — raport".

### M7–M12: cleaning vertical depth + Phase 2 vertical #1

- [ ] 1 cluster page/week off cleaning pillar.
- [ ] Phase 2 vertical pillar shipped (likely remonty per `product-plan.md` §9 — confirm at M7 retro).
- [ ] First Fakturownia/iFirma integration → press + link.
- [ ] First lead magnet (`Wzór umowy o sprzątanie 2026 PDF`).
- [ ] First 10 quality referring domains.

### M12–M18: programmatic launch

- [ ] §6 plan executed in full. Cleaning programmatic first (200–400 pages), remonty second.
- [ ] Phase 2 vertical #2–#3 pillars.
- [ ] AEO/GEO measurement quarterly (§8.5).
- [ ] First 50 quality referring domains.

### Year 2+: US + EN expansion (out of scope here)

Mentioned only to flag: do not start before M18. Do not let it leak into PL roadmap. See §11.

---

## 11. Risks, anti-patterns, what NOT to do

1. **AI content slop.** Mass-publishing un-edited AI pages = sitewide quality penalty. Mitigation: §4.4 rules, hard cap 3 pieces/week pre-programmatic.
2. **Parasite SEO eating brand.** Don't publish on Medium/Substack with our money keywords. We can't outrank ourselves with a parasite, but we can dilute our entity. Skip.
3. **Premature programmatic.** Shipping 1,000 thin programmatic pages at M6 instead of M12 = quality demotion that takes 6+ months to recover from. Hard rule: not before M12 and not before §6.3 gates pass.
4. **Repeating twojafirma.pro's hash-SPA mistake** (`competitor-twojafirma.md` §5.6). Mitigated by SSR test in CI (§5.1). Re-audit at M3.
5. **US-strategy creep.** Founder's eagerness will push to "let's also rank in EN now." Don't. Year 1 = PL only. EN keywords pollute our topical entity in Google's eyes. Wait until M18.
6. **LocalBusiness schema abuse.** Tempting for vertical hubs. Don't. Faking local signals on a SaaS product page is detected and demoted.
7. **Doorway pages.** A separate `/program-dla-firmy-sprzatajacej-warszawa` for every city without unique content = doorway pattern, manual action risk. Either ship region pages with real regional data (§6) or don't ship them.
8. **Letting `app.tbasystent.pl` get indexed.** Single config slip = duplicate content with marketing site, app pages competing in SERPs, login pages ranking. Triple-belt: noindex header + robots + auth.
9. **Over-rotating on Pipedrive-style "CRM" head terms.** We will lose. Stay long-tail, vertical-specific.
10. **Letting wife's authenticity get over-edited into corporate-speak.** Her voice is the moat. Editorial guideline: "if it doesn't sound like Ania, rewrite."
11. **Branded SERP squatting by competitors.** Once we get traction, expect twojafirma to bid on "Wyceny" in Ads. Plan: register Google Ads on our brand defensively at M6 (€20/mo cap).
12. **Chasing search volume without buyer intent.** "jak założyć firmę sprzątającą" gets clicks but converts at <1%. Acceptable as top-of-funnel but never the only thing we publish.

---

## 12. Top 5 actions for the next 30 days

| # | Action | Hours | Owner | Why now |
|---|---|---|---|---|
| 1 | Buy `tbasystent.pl` + defensive `wyceny.pl` (if free) + `tbasystent.pl`. Set up GSC, Bing Webmaster, Plausible. Reserve @tbasystent social handles. | 3h | Founder | Domain squatting risk grows daily; GSC needs 28 days of data before it's useful. |
| 2 | Write `tests/Feature/SeoSsrTest.php` (§5.1) and add to S0 definition-of-done. Document SSR + canonical conventions in repo `/docs/seo.md`. | 4h | Founder | Cheapest moment to enforce SSR is before any pages exist. Repeats twojafirma's mistake otherwise. |
| 3 | Wife drafts v0 of pillar `Wycena sprzątania 2026` — 2,500–3,000 words, raw, in her voice. No SEO optimization yet, just expertise dump. | 8h (her) + 2h (review) | Wife + founder | Content is the long-pole. Starting now means M5 publish, not M8. Her voice is the asset; capture before founder over-edits. |
| 4 | Build calculator component v0 (cleaning, 4 inputs: m², typ, po-remoncie, miasto). Single Livewire component, embeddable, query-param shareable. | 10h | Founder | Reusable across all verticals. The single biggest linkable asset we'll ever build. Worth doing before landing. |
| 5 | Draft 3-paragraph PR pitch ("Mąż-programista buduje CRM dla żony-sprzątaczki — i dla 30,000 polskich małych firm usługowych"). Identify 10 PL business journalists with email addresses. **Don't send yet** — send at M5 alongside landing. | 3h | Founder | Authentic founder story is a one-shot asset; need to be ready when launch window opens. Pre-built target list saves 6h at launch. |

**Total: ~30h founder + 8h wife.** Do not let this slip. Every week of S0 delay on items 1–2 compounds.

---

*Last updated: 2026-04-25. Next review: end of S2.*
