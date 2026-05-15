# Sprint Plan — Wyceny (M1–M3)

> **Horizon:** Sprint 0 (1 week) + Sprints 1–6 (12 weeks of 2-week cadence) = ~13 calendar weeks.
> **Definition of success at end:** żona uses the app as her primary operations tool for her cleaning company, replacing Excel + calendar + WhatsApp for quoting, client memory, and scheduling.
> **Working assumption:** solo dev, part-time, ~15–20 focused hrs/week (evenings + weekend blocks). Plan is sized to that, not to a full-time startup.

---

## 0. Operating Principles (read first, then never again)

1. **Wife-first, not firm-generic.** Every sprint demo is "does żona's Monday morning get easier?" If not, the sprint failed regardless of story points.
2. **Preset system is architecture, not a feature.** Build the seam from M1. But populate ONE preset (`cleaning`) only. Do not build a second preset in these 12 weeks — that is M7 territory.
3. **Boring tech wins.** Filament CRUD + Livewire for everything except the two AI hero flows. No SPA, no custom Vue, no mobile app.
4. **AI is a suggestion layer, never a blocker.** If Claude/Whisper is down, the CRM still works. No hard dependencies in write paths.
5. **Ship to production from Sprint 1.** Deploy wife's real tenant. No staging-only "it works on my machine" demos.
6. **RICE scores are rough.** Reach=1 (N=1 user) for M1–M3, so we effectively collapse to Impact × Confidence / Effort. See prioritization section.
7. **No documentation debt.** Decisions go in `/docs/adr/*.md`. Everything else lives in code and Filament.

---

## 1. Prioritization — MoSCoW on MVP Features

RICE is noise at N=1. MoSCoW against the hard question: **"can żona stop using Excel without this by end of M3?"**

### MUST — required for daily replacement of Excel + WhatsApp + Calendar

| Feature | Why | Sprint |
|---|---|---|
| Auth + single-tenant bootstrap | Trivially required | 0 |
| Multi-tenant data model (tenant scoping on every query) | Changing later = rewrite | 0 |
| Preset engine v1 (JSON schema, registry, cleaning preset populated) | Architectural seam | 0–1 |
| Client CRUD with custom fields from preset | Core entity | 1 |
| Job CRUD (jednorazowe + cykliczne) | Core entity | 2 |
| Quote with line items + PDF export | Hero artifact — what wife sends to customers | 3 |
| Kalendarz tygodniowy z drag & drop | Replaces Google Calendar | 2 |
| Notatki tekstowe per klient | Replaces the "w głowie" layer | 1 |
| Notatki głosowe → Whisper PL | Killer mobile-web feature, wife records in car | 4 |
| AI wycena v1 (suggestion based on history + preset) | Hero #1 — reason to use over Excel | 5 |
| AI chat o kliencie (RAG on notes + history) | Hero #2 — reason NOT to go back | 6 |
| GUS NIP autofill (onboarding) | Wife's onboarding + every new B2B klient | 1 |
| Dojazd + paliwo (Distance Matrix) w wycenie | Cleaning-specific cost line she currently eyeballs | 4 |
| Recurring jobs first-class (RRULE) | 60%+ of her clients are recurring | 2 |
| Production deploy + backups | Can't lose her real data | 0 |

### SHOULD — high value, will slip to M4 if we're behind

| Feature | Why deferred |
|---|---|
| Photo attachments per zlecenie | Useful but she uses phone gallery today fine |
| Global semantic search (pgvector) | Nice moat, but chat-about-klient covers 80% of the need |
| Client risk flags (AI on notes) | Cold-start problem — needs 2 months of data anyway |
| Branded PDF (logo, colors) | Plain PDF is enough for M3, branding in M4 |

### COULD — only if a sprint comes in early (it won't)

| Feature | Reality |
|---|---|
| Tags/segments | Manual filter via custom field works |
| Dashboard (revenue, win rate) | Excel SUM is fine until M4 |
| Auto follow-up reminders | Whatsapp reminders work for her today |

### WON'T (this 12-week window) — see anti-list §6

Photo OCR of WhatsApp screenshots, payments, invoicing, team mode, route optimization, waloryzacja cen, second vertical preset, klient portal, email/SMS campaigns, native mobile, billing/Stripe.

---

## 2. Sprint 0 — Foundations (1 week, ~15 hrs)

**Goal:** Empty repo → deployable skeleton with multi-tenancy, preset engine scaffold, and wife's tenant provisioned. No user-facing features. All future rework-risk is paid down here.

**Demo:** Log in at `app.wyceny.app` (or staging subdomain) as wife's tenant. Empty Filament panel shows `Klienci`, `Zlecenia`, `Wyceny` resources scoped to her tenant_id. Second seeded tenant proves isolation.

### Stories

| # | Story | Acceptance criteria |
|---|---|---|
| S0.1 | Repo + Laravel 11 skeleton | Laravel 11, PHP 8.3, Filament v3, Livewire 3, Pest, Pint, Larastan lvl 6. `composer install && php artisan serve` works. |
| S0.2 | Hetzner + Forge production deploy | `main` branch auto-deploys. HTTPS live. Postgres 16 + Redis running. Daily DB backup to S3/B2. |
| S0.3 | Postgres + pgvector extension | `CREATE EXTENSION vector;` in migration. Smoke test: insert + cosine query on dummy table. |
| S0.4 | Multi-tenancy strategy chosen + implemented | ADR written: single DB, `tenant_id` column + global scope via Eloquent trait. `BelongsToTenant` trait applied. Filament middleware resolves tenant from subdomain OR user. |
| S0.5 | Auth (Filament Breezy or native) | Email/password login. No registration yet — tenants seeded manually. Wife's account + test account exist. |
| S0.6 | Preset engine v1 — schema design | Migration for `vertical_presets` table (slug, name, custom_fields_schema JSONB, service_types JSONB, quote_template JSONB, vocabulary JSONB, pdf_template_key). ADR on schema shape. |
| S0.7 | Preset registry code seam | `App\Presets\PresetRegistry` loads from DB, caches per-tenant. `Tenant::preset()` returns hydrated preset object. Test: change preset slug → custom fields change. |
| S0.8 | Data model ERD + migrations (bare tables, no UI) | Tables: tenants, users, vertical_presets, clients, jobs, quotes, quote_items, notes, note_attachments. All FK-constrained. All have tenant_id. Seeders produce wife's tenant + 3 fake clients. |
| S0.9 | Dev env — Docker (**mandatory**, per `feedback_docker_only.md` + `architecture.md` §22) | `docker compose up -d` gives full stack: postgres+pgvector, redis, mailhog, app (php-fpm), nginx, horizon worker, browserless/chromium sidecar (PDF), optional node sidecar (vite). Pinned versions in `docker/versions.env`. Wrapper scripts in `bin/` (`bin/art`, `bin/composer`, `bin/test`, etc.) + `Makefile` (`make up`, `make fresh`, `make logs`). **Herd / Valet / local PHP installs are off the table.** |
| S0.10 | CI: GitHub Actions | Pint + Larastan + Pest on every PR. Red = no merge. |
| S0.11 | SEO foundations (per `seo-strategy.md` §5 + §10) | Domain `wyceny.app` bought + `wyceny.pl` (if available) parked as 301. GSC + Bing Webmaster + Plausible set up. Social handles reserved (@wycenyapp). `tests/Feature/SeoSsrTest.php` scaffolded — when first public route exists, it asserts: HTTP 200, non-default `<title>`, `<meta description>` 120–160 chars, `<h1>` present, canonical tag present, raw HTML contains controller-rendered text (not Livewire-only). Convention doc `/docs/seo.md` defines URL structure (`wyceny.app` indexable, `app.wyceny.app` `noindex`), canonical strategy, schema.org helper trait. |

### Sprint 0 risks

- **pgvector on Hetzner managed Postgres** — may require self-managed Postgres on a VPS. Mitigation: spike in first 2 hrs; if managed Postgres doesn't support it, run Postgres in Docker on same box.
- **Tenant scoping bugs leak data** — mitigation: write an integration test in S0.4 that creates two tenants and asserts tenant B cannot see tenant A's clients.
- **Preset schema over-engineering** — mitigation: only model what cleaning needs. Adding fields later is cheap; removing isn't.

---

## 3. Sprint 1 — Client + Onboarding (Weeks 2–3)

**Theme:** "Żona może przenieść listę klientów z Excela do Kwotki."
**Goal:** Wife can create her tenant, import/enter all her real clients with cleaning-specific custom fields, and see them in a list.

**Demo at end:** Wife, on her laptop, enters 10 real klientów including keys/codes/allergies. Uses NIP lookup to autofill the 2 B2B ones. All clients visible in Filament, filterable.

### Stories

| # | Story | Acceptance |
|---|---|---|
| S1.1 | Client Filament resource — generic fields | Name, phone, email, address, created_at. CRUD via Filament. |
| S1.2 | Custom fields rendered from preset | Cleaning preset adds: m², typ lokalu (select), klucze/kod (encrypted), preferencje, alergie, dostęp (np. pies). Field visibility/required-ness comes from preset JSON. |
| S1.3 | Encrypted storage for sensitive fields | `klucze/kod` uses Laravel's encrypted cast. DB dump doesn't expose them. |
| S1.4 | Notatki per klient (text only) | Timeline view of notes on klient page. Add/edit/delete. Timestamp + author. |
| S1.5 | GUS REGON NIP autofill | Input NIP on klient form → hit GUS API → autofill nazwa, adres, REGON. Graceful fallback if GUS down. Rate limit handled. |
| S1.6 | Address geocoding on save | On klient save, geocode address via Google Maps → store lat/lng. Used later for dojazd. |
| S1.7 | Client list: search, filter by custom field | Search by name/phone. Filter by typ lokalu, by tag (if we add). Pagination 25/page. |
| S1.8 | Tenant onboarding flow | First-login wizard: firma name, NIP (optional), selects vertical preset ("sprzątanie" is only option). Creates tenant + seeds preset. |

### Sprint 1 risks

- **GUS API reliability** — known to be flaky + rate-limited. Mitigation: cache responses 30 days, treat as enhancement not requirement, queue as retry job.
- **Custom fields UX in Filament** — Filament custom fields from JSON schema is non-trivial. Mitigation: hard-code field renderer for cleaning preset first, generalize only if Sprint 2 needs it.
- **Address geocoding cost creep** — mitigation: only geocode on address change, cache forever per normalized address.

---

## 4. Sprint 2 — Zlecenia + Kalendarz (Weeks 4–5)

**Theme:** "Żona widzi tydzień pracy ekipy w jednym miejscu."
**Goal:** Jobs (jednorazowe + cykliczne) can be created against a klient and appear on a weekly calendar. Drag & drop reschedules.

**Demo at end:** Wife creates next week's schedule for her 3 cleaners. Two recurring clients auto-appear. She drags one to a different day.

### Stories

| # | Story | Acceptance |
|---|---|---|
| S2.1 | Job model + Filament resource | Fields: `client_id`, `service_type_key` (from preset `service_types`), `starts_at`, `duration_minutes`, `assigned_to` (free-text cleaner name for now), `status` (planned/done/cancelled), `internal_notes`. UI labels rendered via `__()` (PL by default). |
| S2.2 | Recurrence first-class | Job has `recurrence_rule` (RFC 5545 RRULE subset: weekly, biweekly, monthly). Materialized occurrences generated on save + on window-view. Exceptions (one-off skip) supported. |
| S2.3 | Service types from preset | Typ_zlecenia dropdown comes from preset: podstawowe / generalne / po remoncie / okna / pranie. Each has default duration + base price. |
| S2.4 | Weekly calendar view (Livewire) | Mon–Sun grid. Events colored per cleaner. Click event → edit zlecenie modal. Current week default, prev/next nav. |
| S2.5 | Drag & drop reschedule | Drag event to new slot → updates datetime. Optimistic UI, rolls back on 500. Only moves single occurrence, not whole series. |
| S2.6 | "Następne zlecenie" on klient page | Client detail shows next upcoming zlecenie + last completed one. |
| S2.7 | Mobile-responsive calendar (PWA-lite) | Calendar is usable on wife's phone (she checks it in the car). Touch scroll works. Add to home screen works. |

### Sprint 2 risks

- **Recurring events logic is a swamp** — mitigation: limit to weekly/biweekly/monthly, no custom intervals, no "every 3rd Tue" stuff. Use `simshaun/recurr` or equivalent battle-tested lib.
- **Drag & drop UX on Livewire** — Livewire + FullCalendar.js or Alpine drag handler. Mitigation: use FullCalendar with a thin Livewire bridge; it's the standard.
- **Timezone bugs** — store UTC, render Europe/Warsaw, test DST change. Write one test.

---

## 5. Sprint 3 — Quote + PDF (Weeks 6–7)

**Theme:** "Żona wysyła klientowi PDF wyceny z Kwotki, nie z Excela."
**Goal:** Create a wycena from a klient/zlecenie, add line items per preset template, export PDF, track status (sent/accepted/rejected).

**Demo at end:** Wife makes a real wycena for a real prospect. PDF looks professional. She sends it via WhatsApp. Client accepts. Status flips in Wyceny.

### Stories

| # | Story | Acceptance |
|---|---|---|
| S3.1 | Quote model + Filament resource | Fields: `client_id`, `job_id` (optional), `number` (auto), `issued_at`, `status` (draft/sent/accepted/rejected/expired), `valid_until`, `subtotal`, `vat_rate`, `total`, `internal_note`. UI labels rendered via `__()` from `lang/pl.json`. |
| S3.2 | Quote items | Line items: `description`, `unit` (`m2`/`h`/`piece`/`flat`), `quantity`, `rate`, `discount_pct`, `vat_pct`, `line_total`. Add/remove/reorder. Totals auto-calc. |
| S3.3 | Quote template from preset | Cleaning preset default: one line per service type, `unit='m2'`, `rate` = preset default, multiplier per type. "New quote from job" prefills from preset. |
| S3.4 | PDF export (plain, not branded) | `dompdf` or `spatie/browsershot`. A4, firma header (name/NIP/address), klient block, items table, totals, signature line, footer with "ważna do". Polish currency/number format. |
| S3.5 | Quote numbering scheme | Per-tenant sequential: `2026/04/001`. Configurable pattern later. |
| S3.6 | Quote status transitions | Draft → Sent (captures `sent_at`), Sent → Accepted/Rejected (captures `decided_at`), any → Expired (cron daily based on `valid_until`). |
| S3.7 | Share wycena link (public read-only) | Signed URL renders HTML view of wycena. Client can view w/o login. "Accept" button records acceptance. (Simple version — no full portal.) |
| S3.8 | VAT config per tenant | Tenant settings: default VAT %, is-VAT-payer. PDF respects setting. |

### Sprint 3 risks

- **PDF rendering performance** — mitigation: queue via Horizon, not request-time. Cache rendered PDFs by content hash.
- **Polish number/date formatting** — mitigation: one `FormatsPolish` helper, test it.
- **VAT edge cases** (mixed rates, RR VAT for cleaning) — mitigation: fixed 23%/8%/zw dropdown, don't build invoice-grade tax logic. This is a wycena, not faktura.
- **Wife's formatting demands** ("dodaj logo, zmień czcionkę") — mitigation: plain template now, branding in M4. Say no nicely.

---

## 6. Sprint 4 — Notatki głosowe + Dojazd (Weeks 8–9)

**Theme:** "Żona dyktuje notatkę z samochodu, dojazd liczy się sam."
**Goal:** Voice notes transcribed via Whisper; wycena automatically includes dojazd cost based on Google Distance Matrix.

**Demo at end:** Wife, after visiting a prospect, records a 2-minute voice note on her phone. Transcription appears on klient's timeline within 30s. Next wycena for that klient auto-includes `Dojazd: 14km × 1.80 PLN = 25.20 PLN`.

### Stories

| # | Story | Acceptance |
|---|---|---|
| S4.1 | Voice note upload (web + mobile) | Record button on klient page uses MediaRecorder API. Uploads audio to S3/B2. Creates `note` with `status=transcribing`. |
| S4.2 | Whisper transcription job | Horizon queue job calls OpenAI Whisper, language hint `pl`. Stores transcript on note. Updates status. Retries 3× with backoff. |
| S4.3 | Transcript cleanup via Claude (optional step) | If transcript > 200 chars, run through Claude with prompt "clean up filler words, preserve meaning, Polish". Toggle per-tenant. |
| S4.4 | Note timeline with audio playback | Timeline shows transcript + inline `<audio>` for original. Edit transcript manually (Whisper makes mistakes). |
| S4.5 | Firma origin address (settings) | Tenant settings: origin address (wife's office/home). Geocoded once. |
| S4.6 | Distance calc klient → origin | On klient save, compute one-way distance + duration via Google Distance Matrix. Cache result. Recompute if klient address changes. |
| S4.7 | Commute line item auto-added to quote | When creating quote for client with computed distance, auto-insert line with `source='commute'`. Display label via `__('quote.line.commute')` (PL: "Dojazd (w obie strony)"). Calc: km × fuel_rate × 2. Fuel rate in tenant settings (default 1.80 PLN/km). User can remove it. |
| S4.8 | Note search (plain SQL LIKE for now) | Search box on klient page searches notes by transcript. pgvector semantic search deferred. |

### Sprint 4 risks

- **Whisper PL quality on Polish regional accents / background noise** — mitigation: spike in first day of sprint with 5 real recordings from wife. If quality < 80% usable, add manual edit as first-class (already in S4.4) and move on. Do not rat-hole on fine-tuning.
- **OpenAI/Whisper cost surprises** — mitigation: per-tenant monthly cap. Alert at 80%. $0.006/min = wife's expected 30 min/month = trivial, but monitor.
- **Google Maps billing** — mitigation: cache distance results forever per (origin, destination) pair. Free tier covers thousands of requests.
- **Mobile microphone permissions on iOS Safari** — known pain. Mitigation: test on wife's actual phone before claiming done.

---

## 7. Sprint 5 — AI Quote v1 (Weeks 10–11)

**Theme:** "Wyceny mówi: tej klientce wyceniaj 280 PLN bo tak wyceniałaś ostatnie 3 razy + mieszkanie 65m² + dojazd."
**Goal:** Hero AI feature #1 — pricing suggestion based on klient history + preset + dojazd + zakres.

**Demo at end:** Wife starts a new wycena for an existing klient. Before she enters prices, Wyceny shows: "Sugerowana cena: 290 PLN. Bo: podobne 3 wyceny 260–310 PLN, mieszkanie 65m² (stawka preset 4 PLN/m²), dojazd 28 PLN." She clicks "użyj" or adjusts.

### Stories

| # | Story | Acceptance |
|---|---|---|
| S5.1 | Pricing context builder | `Pricing\PricingContextBuilder` assembles: client's past N quotes, preset base rates, commute line, `service_type_key`, client custom fields (`area_m2`, `property_type`). Structured JSON input for LLM. |
| S5.2 | Claude pricing prompt + service | `PricingSuggestionService` calls Claude with strict JSON output schema: `{suggested_total, breakdown[], reasoning, confidence}`. Prompt in a versioned file. |
| S5.3 | Suggestion UI on new wycena | "Sugerowana cena" panel on wycena create form. Shows amount + human-readable breakdown + reasoning. "Użyj sugestii" button prefills line items. Always editable. Disclaimer: "Sugestia AI, nie decyzja." |
| S5.4 | Fallback when history is thin (cold start) | If < 2 past wyceny for klient: use preset-level benchmarks + typ_zlecenia defaults + custom fields. Note in UI: "bazuje na preset, nie historii." |
| S5.5 | AI usage logging + cost tracking | Every Claude call logs tenant, tokens, cost, latency. Admin dashboard row per month. |
| S5.6 | Accept/reject tracking on suggestion | When user submits wycena, diff vs. suggestion stored. Powers "AI adoption rate" metric later. |
| S5.7 | Claude model fallback | If Claude API errors/timeouts, wycena creation proceeds without suggestion. Never blocks. |

### Sprint 5 risks

- **Suggestion quality is "meh"** — the real risk. Mitigation: accept it. v1 just has to be "not obviously wrong" for wife. The compounding value comes from M4–M6 of her data, not from prompt cleverness. Resist the urge to iterate on the prompt for 2 weeks.
- **Claude pricing at scale** — not relevant at N=1. Note for later: with ~20 suggestions/mo per firma × 5k input tokens × Sonnet = well under 8 PLN/user target.
- **Hallucinated line items** — mitigation: strict JSON schema validation; breakdown items must reference preset service_types. Reject + retry once on schema miss.
- **Prompt versioning discipline** — mitigation: prompts in `/app/Prompts/*.md` under version control. ADR on how to evaluate prompt changes.

---

## 8. Sprint 6 — AI Chat o Kliencie + Polish (Weeks 12–13)

**Theme:** "Żona pyta 'co ostatnio u pani Kowalskiej?' i dostaje odpowiedź w 3 sekundy."
**Goal:** Hero AI feature #2 — semantic Q&A over a klient's notes + zlecenia + wyceny. Plus final polish for daily use.

**Demo at end:** Wife asks: "co obiecałam pani Kowalskiej na maj?" Wyceny answers with citations to specific notes. Wife closes Excel permanently.

### Stories

| # | Story | Acceptance |
|---|---|---|
| S6.1 | Embeddings generation on note save | On note create/update, queue job computes embedding (OpenAI text-embedding-3-small). Stores as `vector(1536)` in notes table. |
| S6.2 | Backfill embeddings for existing notes | One-off command `php artisan notes:embed-all`. Idempotent. |
| S6.3 | Client-scoped chat UI | Chat panel on klient detail page. Message input + message log. Livewire-based. |
| S6.4 | RAG retrieval | Given a query: embed it, cosine-search top-K notes (K=8) for this klient via pgvector, plus include last 5 zlecenia + last 3 wyceny as structured context. |
| S6.5 | Claude answer with citations | Prompt forces citation format: "Zapowiedziałaś majowe sprzątanie generalne [note #123]." UI renders citations as links to the note. |
| S6.6 | Guardrails | System prompt: "Answer only from provided context. If not in context, say 'nie widzę tego w notatkach.'" Test: ask about unrelated klient → refuses. |
| S6.7 | M3 polish list (time-boxed, 2 days max) | Top 10 wife-reported papercuts from daily use in Sprints 4–5. Triaged ruthlessly. Anything > 2 hrs moves to M4. |
| S6.8 | M3 demo day: wife uses Wyceny for a full real workweek | No Excel. No separate calendar. Log failures. This IS the acceptance test for MVP. |

### Sprint 6 risks

- **pgvector performance at tiny scale is fine, but index choice matters later** — mitigation: use `ivfflat` or `hnsw` index from start; dummy config, don't tune. At < 10k notes it doesn't matter anyway.
- **RAG brings back irrelevant notes** — mitigation: always include recency filter (last 12 months) + klient scope as hard SQL filter, embeddings are rerank only.
- **Chat becomes a support channel for missing features** ("dodaj fakturę VAT!") — mitigation: guardrail prompt explicitly says chat only answers questions about the klient's data. No general CRM help.
- **Wife hits a "hate day"** in demo week — plan for it. Have a kitchen-table retro. One "hate day" out of 5 is acceptable.

---

## 9. Definition of Done — MVP (end of Sprint 6)

Measurable, wife-verifiable:

1. **Replacement:** For 5 consecutive business days, wife uses Wyceny (web + PWA on phone) and does NOT open Excel or her paper notebook. Google Calendar read-only for external events only.
2. **Data volume in prod:** ≥ 30 real klientów, ≥ 20 real zlecenia (mix of one-off + recurring), ≥ 10 real wyceny (of which ≥ 3 accepted), ≥ 15 notatki (of which ≥ 5 voice-transcribed).
3. **AI adoption:** AI wycena suggestion shown on ≥ 8 of her last 10 wyceny. She accepted or adjusted-within-15% on ≥ 5 of those. Chat o kliencie used ≥ 10 times total.
4. **Reliability:** Zero data-loss incidents. Zero tenant isolation bugs. < 3 sev-2 bugs open. Daily backups verified (test restore once).
5. **Speed:** First-time wycena creation < 2 min. Weekly calendar load < 800ms.
6. **North Star baseline:** Weekly Active Quoting = 3+ wyceny/week for wife's tenant, 4 weeks running.
7. **Cold start handled:** Even with thin data, AI suggestion does not embarrass (manual sanity check on last 10 suggestions).
8. **No show-stopper for inviting firma #2:** Onboarding a second fake tenant end-to-end takes < 30 min of founder time, no code changes.

If any of 1, 2, 4, or 8 fails → MVP is not done. Fix before M4 expansion.

---

## 10. ANTI-LIST — What NOT to Build in Weeks 1–13

**Rule:** If you catch yourself starting any of these, stop and write a line in `/docs/deferred.md` with date + temptation. Revisit at M4 planning.

| Tempting thing | Why you want to build it | Why you won't (yet) | When |
|---|---|---|---|
| Photo attachments (before/after) | "It's universally useful" | Phone gallery already works for wife. Storage + UI + thumb generation is a week. | M4 |
| WhatsApp screenshot OCR | "Killer feature!!" | Killer feature for marketing, not for wife's daily flow (she's already got you reading her WhatsApp). Claude Vision + pipeline + UI = 2 sprints. | M4–M5 |
| Second vertical preset (remonty/fotograf) | "Prove horizontality!" | Horizontality is proven by the *architecture* existing, not by two presets existing. One preset populated + seam present is sufficient. Second preset = M7. | M7 |
| Team mode / multiple users per tenant | "Wife has cleaners" | Cleaners don't use the app. Wife uses app, dispatches cleaners via WhatsApp. When a real second-user request comes in from a paying firm, build. | M11+ |
| Invoicing / Fakturownia integration | "Natural next step after wycena" | Wife uses accounting office. Integration with Fakturownia is real work and real maintenance. Quote PDF is enough. | M7–M8 |
| Online payments | "Modern!" | Wife gets paid by transfer. No friction to solve. Stripe/Przelewy24 = compliance + UX = a sprint. | M5 (billing), M9+ (klient payments) |
| Client portal | "Looks pro" | Signed share link for wycena covers 90% of the need. Portal = auth + UX for low-engagement users. | M9 |
| Email/SMS campaigns | "Retention!" | N=1 + wife knows her clients personally. Premature. | M5+ |
| Native mobile app | "Faster UX" | PWA covers it. Native = 3x the work forever. | Maybe never. |
| Dashboard with charts | "Feels like a product!" | Wife doesn't need KPIs — she IS the business. Build when second firm asks. | M5–M6 |
| Route optimization | "Cleaning visits in a day" | Wife's cleaners already know the routes. Needs 5+ clients/day/cleaner to matter. | M7+ |
| Waloryzacja cen cykliczna | "1-click inflation" | Valuable but seasonal (once/year). She can bulk-edit in Filament. | M5 |
| Client risk flags via AI | "Cool moat" | Needs 3+ months of data to be non-trivial. Cold-start losers. | M5 |
| Fine-tuned per-tenant pricing model | "Technical moat!" | Fine-tuning before you have data is cargo cult. Prompt + history RAG is sufficient until M10. | M11+ |
| Global SEO landing pages per branża | "Content machine!" | Zero traffic value at M3 when you have 1 user. | M6+ |
| Billing / Stripe subscriptions | "Monetize!" | Wife is free. No other users until M4 beta. Build when you need to charge. | M5 |
| Prompt engineering deep-dives | "Make AI smarter!" | Data > prompts. 1 more month of wife's real wyceny beats 2 weeks of prompt tuning. | Ongoing, light touch |
| Rewriting multi-tenancy because it "feels off" | "Architecture matters" | If S0.4 has a passing isolation test, it's fine. Resist. | Never in M1–M3 |
| Documentation website | "Onboard other firms!" | No other firms yet. Write it at M4 beta kickoff. | M4 |

---

## 11. Cross-Cutting Risks (not sprint-specific)

| Risk | Mitigation |
|---|---|
| **Solo dev burnout** | Hard cap 20h/week. One day/week fully off. Wife as product owner also means she can veto scope when you're tired. |
| **Wife-tester N=1 bias** | Log every "dodaj X" request in `/docs/wife-requests.md` with date. Don't build immediately. At M4 kickoff, review list against 5-firm beta feedback. |
| **Family conflict from "no"** | Frame as "teraz nie, bo X, w [month] tak." Never as "to zły pomysł." |
| **RODO** | Encryption at rest for sensitive custom fields (S1.3). DPA template ready by M4 before inviting firma #2. No analytics cookies until launch. |
| **AI liability** | "Sugestia AI, nie decyzja" label on every AI output (S5.3, S6.5). ToS draft by M5. |
| **Vendor lock-in / costs** | Abstract Claude behind `AIProvider` interface. Log all token usage. Test with one OpenAI-compatible call once to prove portability. Don't actually switch. |
| **Hetzner downtime / Forge issues** | Daily offsite backup. Recovery drill at end of Sprint 3. |
| **Scope creep from "one more sprint"** | This document is the contract. If feature X isn't in here, it's not in M1–M3. Append-only `/docs/deferred.md`. |

---

## 12. Ceremony Minimum (solo dev, don't LARP agile)

- **Sprint kickoff (30 min, Sunday before):** pick stories from this plan, confirm capacity, write sprint goal at top of a GH project.
- **Mid-sprint check (15 min, Wednesday of week 1):** cut scope if behind. No heroics.
- **Demo to wife (Friday of week 2):** show what shipped. Capture 3 things she'd change. Don't promise when.
- **Retro (20 min, solo, journal):** what blocked. What to cut next sprint. One process change, not five.
- **No daily standup. You're one person.** Journal 3 lines in morning: today's one thing, blocker, yesterday's done.

---

## 13. Summary Cadence

| Sprint | Weeks | Theme | Demoable |
|---|---|---|---|
| S0 | 1 | Foundations | Empty but isolated multi-tenant Filament app deployed |
| S1 | 2–3 | Client + Onboarding | Wife's 10 real klientów in system, NIP autofill working |
| S2 | 4–5 | Zlecenia + Kalendarz | Weekly schedule for 3 cleaners, recurring jobs work |
| S3 | 6–7 | Quote + PDF | Real wycena sent as PDF, klient accepts via link |
| S4 | 8–9 | Voice notes + Dojazd | Voice note transcribed, dojazd auto-priced |
| S5 | 10–11 | AI Quote | Suggestion panel on new wycena, wife uses it |
| S6 | 12–13 | AI Chat + Polish | Q&A over klient's history, 5-day Excel-free work week |

**End state:** żona uses Wyceny daily. Quote takes 2 min instead of 15. Historia klienta in 3 seconds. Ready to onboard firma #2 in M4.
