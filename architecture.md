# Architecture — Wyceny (`wyceny.app`)

> **Status:** v0.1, 2026-04-25. Companion to `sprint-plan.md` (S0–S6) and `product-plan.md`.
> **Scope:** the architecture that makes the 13-week MVP buildable in ~15–20h/week without painting the founder into a corner before M7 (second vertical preset) and M11+ (team mode).
> **Anti-scope:** does not redefine sprint stories or DoD. References to `sprint-plan.md S0.4`, `seo-strategy.md §5`, etc., are load-bearing.

---

## 1. TL;DR

- **Modular monolith.** One Laravel 11 codebase, internal split (`app/Modules/*`), one DB, one Horizon. Microservices would kill the founder's evenings; flat `app/` would kill the M7 second-vertical migration.
- **Three architectural pillars.** (1) **Tenant isolation by default** — every Eloquent model uses `BelongsToTenant`, every queue job carries `tenant_id`, every test scenario has two tenants. (2) **Preset engine as a seam, not a feature** — vocabulary, custom fields, service types, quote templates, PDF template all read from `vertical_presets`; cleaning is one row, M7's remonty is another row plus zero or one Blade. (3) **AI as a strictly optional suggestion layer** — every write path completes if AI returns 500. No AI-coupled DB transactions.
- **Biggest risk:** **multi-tenant scope leak via raw DB, queue jobs, broadcast channels.** Eloquent global scope is necessary, not sufficient. §4.4 names all three surfaces and mitigations.
- **One decision the founder should push back on:** **custom-field storage as JSONB columns** instead of a `custom_field_values` side table. Easier today, harder to validate cross-preset at M7. Picking JSONB; trade-off in §3.4.
- **Embeddings:** `pgvector` + `ivfflat` (not hnsw). At <10k vectors per tenant index choice doesn't matter; ivfflat has cheaper inserts. Reverse at M9+ if dataset >100k notes.
- **PDF:** `spatie/browsershot` (Forge-installed Chromium), not `dompdf`. Browsershot handles Polish typography, web fonts, CSS Grid; dompdf will fight us on diacritics within a sprint. Cost: ~600 MB Chromium binary.
- **Deployment:** single Laravel app. `wyceny.app` (public, SSR per `seo-strategy.md §5`) + `app.wyceny.app` (Filament, `noindex`) routed by subdomain middleware in same codebase. No Next.js front, no Webflow split.
- **Boring on purpose.** 8 Horizon queues, one Filament panel, one Livewire-FullCalendar bridge, one ADR per S0 story. We fix walls, we don't replatform.

---

## 2. Bounded Contexts / Modules

Codebase under `app/Modules/<Context>/` (PSR-4 `App\Modules\<Context>\`). Each module owns its Eloquent models, services, Filament resources, Livewire components. Modules talk via **public service classes** and **domain events**. **Hard rule:** no module imports another's `Internal/*` namespace.

| Module | Responsibility | Key entities | Public interface | NOT in this module |
|---|---|---|---|---|
| `Tenancy` | Tenant resolution, `BelongsToTenant`, global scope, isolation tests | `Tenant`, `User`, `TenantSetting` | `TenantResolver::fromSubdomain()`, `Tenant::current()`, `BelongsToTenant`, `TenantScope` | Billing, multi-user RBAC (M11+) |
| `Presets` | Vertical preset registry, schema validation, hydration | `VerticalPreset`, `Preset` (VO) | `PresetRegistry::for(Tenant)`, accessors for serviceTypes/customFieldsSchema/quoteTemplate/vocabulary | Field rendering (Filament owns) |
| `Crm` | Client + Job, the operational spine | `Client`, `Job`, `Address` | `ClientRepository`, events: `ClientCreated`, `ClientAddressChanged`, `JobCompleted` | Recurrence math (`Scheduling`), notes (`Notes`) |
| `Scheduling` | RRULE engine, occurrence materialization, calendar window queries | `RecurrenceRule`, `JobOccurrence`, `OccurrenceException` | `RecurrenceEngine::expand(RRule, window)`, `CalendarQuery::weekFor()` | Calendar UI (Filament/Livewire) |
| `Quoting` | Quote, items, status machine, numbering, PDF jobs, share links | `Quote`, `QuoteItem`, `QuoteStatusLog`, `QuoteShareToken` | `QuoteService::create()`, `QuoteNumberAllocator`, events: `QuoteCreated/Sent/Accepted` | PDF Blade templates (in `resources/views/pdf/`) |
| `Notes` | Text + voice notes, attachments, embedding rows | `Note`, `NoteAttachment`, `NoteEmbedding` | `NoteService::create()`, events: `NoteCreated/Transcribed/Embedded` | Whisper/Claude calls (in `AI`) |
| `Pricing` | Pricing suggestion service, context builder, accept/diff tracking | `PricingSuggestion`, `PricingSuggestionFeedback` | `PricingSuggestionService::suggest(QuoteDraft)`, `PricingContextBuilder` | Claude HTTP client (in `AI`) |
| `ClientChat` | Chat session, retrieval, prompt assembly, citations | `ChatSession`, `ChatMessage` | `ClientChatService::ask(Client, query)`, `RagRetriever::topK()` | Embedding generation (in `AI`) |
| `AI` | Provider abstraction, prompt loading, JSON schema retry, token logging, cost cap | `AIUsageLog`, `Prompt` | `AIProvider`, `ClaudeProvider`, `WhisperProvider`, `EmbeddingsProvider`, `OcrProvider`, `PromptRegistry` | Domain logic |
| `Integrations` | GUS REGON, Google Maps Distance + Geocoding, caches | `GeocodingCache`, `DistanceCache`, `GusLookupCache` | `GusClient::lookup(NIP)`, `Geocoder`, `DistanceCalculator` | Domain models |
| `Pdf` | Browsershot wrapper, PDF caching by content hash, signed URLs | `PdfRender` | `PdfRenderer::render()`, `SignedShareLink::for(Quote)` | What goes on the PDF (in `Quoting`) |
| `Public` | Marketing site (`wyceny.app`), pillars, calculator widget, SEO helpers | — | Routes only | App auth, tenant data |
| `Billing` | **STUB only M1–M3.** Folder exists, no code. M5 ships Stripe/Tpay. | — | `BillingStub::canCreateQuote(): true` | Anything real (per `sprint-plan.md` §10) |

**Cross-module communication, in preference order:** (1) domain events (default for "after X, do Y"); (2) public service class call (sync answer needed); (3) direct Eloquent relation (only within same aggregate root).

**[FOUNDER REVIEW]** This is more discipline than a 1-person team usually applies. Payoff is M7 + M11 without rewrite. Cost: ~5–10 min per feature. If end-of-S2 it feels like overhead with no payoff, collapse `Crm` + `Quoting` into one module — but keep `Tenancy`, `Presets`, `AI`, `Notes` separate.

---

## 3. Domain Model & ERD (text form)

All tenant-scoped tables have `tenant_id BIGINT NOT NULL` indexed, FK to `tenants.id` `ON DELETE CASCADE`. Global tables: `tenants`, `vertical_presets`, `migrations`, `failed_jobs`, `cache`.

**Tenant ID format: ULID.** `tenants.id` bigint pk for joins; `tenants.ulid char(26) unique` for external use (subdomain, signed URLs). Subdomain → `tenants.slug` (kebab). **[FOUNDER REVIEW]** UUID v7 also works; ULID picked for shorter URLs + lexicographic sort.

### 3.1 Tables (key fields only)

```
tenants(id, ulid, slug, firma_name, nip, regon, preset_id→vertical_presets, preset_version,
        origin_address_id, ai_monthly_cap_pln=50, ai_monthly_used_pln, is_vat_payer,
        default_vat_rate=23, fuel_rate_pln_per_km=1.80, timestamps)

users(id, tenant_id, email UNIQUE(tenant_id,email), password_hash, name, role='owner', timestamps)
  -- M11+: split to users + tenant_user pivot

vertical_presets(id, slug UNIQUE, name, version, vocabulary jsonb, custom_fields_schema jsonb,
                 service_types jsonb, quote_template jsonb, pdf_template_key, ai_hints jsonb,
                 is_active, timestamps)
  -- Global, not tenant-scoped; shared library.

clients(id, tenant_id, name, phone, email, nip, address_id, custom_fields jsonb,
        access_keys_encrypted text, soft_deletes, timestamps)
  -- INDEXES: (tenant_id), (tenant_id,name), GIN on custom_fields

addresses(id, tenant_id, line1, line2, postcode, city, country='PL',
          lat, lng, geocoded_at, timestamps)

geocoding_cache(id, tenant_id, normalized_address UNIQUE(tenant_id,normalized_address),
                lat, lng, provider='google', raw_response jsonb, created_at)

distance_cache(id, tenant_id, origin_address_id, destination_address_id,
               distance_meters, duration_seconds, raw_response jsonb, computed_at,
               UNIQUE(tenant_id,origin,dest))

jobs(id, tenant_id, client_id, service_type_key, custom_fields jsonb,
                recurrence_rule varchar(256) nullable,  -- RRULE subset, see §8
                starts_at timestamptz UTC, duration_minutes,
                assigned_to varchar(128), status enum(planned/done/cancelled),
                internal_notes text, soft_deletes, timestamps)
  -- INDEX: (tenant_id, starts_at), (tenant_id, client_id)

job_occurrences(id, tenant_id, job_id, occurrence_at timestamptz,
                status enum(planned/done/cancelled/skipped/rescheduled),
                rescheduled_to nullable, completed_at nullable, timestamps,
                UNIQUE(job_id, occurrence_at))
  -- Override rows for exceptions only; lazy expansion otherwise (§8.4)

quotes(id, tenant_id, client_id, job_id nullable, number varchar(32) UNIQUE(tenant_id,number),
                status enum(draft/sent/accepted/rejected/expired),
                issued_at, valid_until, subtotal, vat_rate, total, internal_note,
                sent_at, decided_at, expired_at,
                pdf_hash char(64), pdf_path,  -- content-addressed cache
                soft_deletes, timestamps)

quote_items(id, quote_id, tenant_id, position, description, unit enum(m2/h/piece/flat),
            quantity, rate, discount_pct=0, vat_pct, line_total, service_type_key,
            source enum(manual/preset/ai_suggestion/commute))

quote_status_log(id, quote_id, tenant_id, from_status, to_status,
                 transitioned_at, transitioned_by_user_id, meta jsonb)

quote_share_tokens(id, quote_id, tenant_id, token char(64) UNIQUE,
                   expires_at, accepted_at, accepted_ip, accepted_user_agent)

notes(id, tenant_id, client_id, body text, body_cleaned text,
      audio_path, audio_duration_seconds, status enum(ready/transcribing/failed),
      source enum(text/voice), created_by_user_id, soft_deletes, timestamps)

note_attachments(id, tenant_id, note_id, path, mime, bytes, created_at)

note_embeddings(id, tenant_id, note_id, embedding vector(1536),
                model varchar(64), created_at)
  -- ivfflat index on embedding vector_cosine_ops WITH (lists=100)

ai_usage_logs(id, tenant_id, user_id, feature enum(pricing/chat/transcription/transcript_cleanup/
              embedding/ocr), provider, model, prompt_version, input_tokens, output_tokens,
              cost_pln, latency_ms, status enum(ok/error/timeout/schema_miss),
              error_message, created_at)
  -- INDEX: (tenant_id, created_at)

pricing_suggestions(id, tenant_id, quote_id, suggested_total, breakdown jsonb,
                    reasoning text, confidence, prompt_version, ai_usage_log_id, created_at)

pricing_suggestion_feedback(id, suggestion_id, tenant_id,
                            decision enum(used_as_is/adjusted/ignored),
                            final_total, diff_pct, recorded_at)

chat_sessions(id, tenant_id, client_id, user_id, title, timestamps)
chat_messages(id, session_id, tenant_id, role enum(user/assistant), content,
              citations jsonb, ai_usage_log_id, created_at)

tenant_settings(tenant_id pk, origin_address_id, fuel_rate_pln_per_km,
                default_vat_rate, is_vat_payer,
                quote_number_pattern='{YYYY}/{MM}/{seq:003}',
                ai_monthly_cap_pln, ai_alerts_email,
                whisper_cleanup_enabled bool, pdf_branding jsonb, updated_at)

tenant_quote_counters(tenant_id, year, month, seq, UNIQUE(tenant_id,year,month))
audit_logs(id, tenant_id, user_id, action, model_type, model_id, before jsonb, after jsonb,
           ip, user_agent, created_at)  -- via Spatie Activitylog
```

### 3.2 Invariants

- Every tenant-scoped row: `tenant_id NOT NULL`, FK to `tenants.id`, indexed.
- `quotes.number`: unique within `tenant_id`. Allocated by `QuoteNumberAllocator` (per-tenant counter row, `lockForUpdate` — §10.1).
- `note_embeddings.tenant_id` always equals `notes.tenant_id` (denormalized; enforced by listener, audited by test).
- `quote_items.tenant_id` = parent `quotes.tenant_id` (denormalized for scope checks).
- `clients.access_keys_encrypted`: never logged, never returned over API in plain, decrypted only in Filament edit form for owner.
- All factories set `tenant_id` from `Tenant::current()`.
- Horizon jobs: receive `tenant_ulid` in payload, `tenancy()->switch()` in `handle()` (§4.4).

### 3.3 Custom-fields decision: JSONB on `clients`/`jobs`

**Decision: JSONB column.** `clients.custom_fields` and `jobs.custom_fields` are JSONB. Schema lives in `vertical_presets.custom_fields_schema`. Validated by `CustomFieldsValidator` against preset JSON schema on save.

**Why:**
- One preset per tenant in M1–M3. Cross-preset migration is rare, deliberate.
- Filament can render JSON-schema fields directly into JSONB. Side-table needs a join+pivot UI Filament doesn't give for free.
- Querying `WHERE custom_fields->>'property_type' = 'apartment'` with GIN index is fine for our scale (10–500 clients/tenant). Polish UI label "mieszkanie" maps to DB value `'apartment'` via translation files.
- Encrypted custom fields (`access_keys`) sit in their own column with separate encryption-at-rest semantics.

**Trade-offs accepted:**
- Schema migration when preset renames a field at M7+: one-off Laravel command per preset version. Manageable.
- Cross-tenant reporting needs JSONB extraction. Acceptable until M9+.
- No SQL-level field validation: bug writing wrong key isn't caught by Postgres. Mitigated by app-layer validator + Pest test per preset.

**[FOUNDER REVIEW]** Alternative is EAV (`custom_field_values(entity_type, entity_id, field_key, value_*)`). Cleaner audit, harder UX. If founder believes M7–M9 will reshape custom fields per-tenant, switch to EAV before S1. Otherwise JSONB.

### 3.4 Relationships (high-level)

- `Tenant` 1—N `User`, 1—N `Client`, 1—N `Job`, 1—N `Quote`, 1—N `Note`, 1—1 `TenantSetting`. `Tenant` N—1 `VerticalPreset`.
- `Client` 1—N `Job`/`Quote`/`Note`/`ChatSession`. `Client` N—1 `Address`.
- `Job` 1—N `JobOccurrence`. `Job` 1—N `Quote` (optional).
- `Quote` 1—N `QuoteItem`/`QuoteStatusLog`/`QuoteShareToken`.
- `Note` 1—N `NoteAttachment`, 1—1 `NoteEmbedding`.

---

## 4. Multi-tenancy Implementation

Decided in `sprint-plan.md S0.4`. This section spells out trait, resolver, leak surfaces, tests.

### 4.1 `BelongsToTenant` trait

```php
namespace App\Modules\Tenancy\Concerns;

trait BelongsToTenant {
    protected static function bootBelongsToTenant(): void {
        static::addGlobalScope(new TenantScope);
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = Tenant::currentId()
                    ?? throw new TenantNotResolvedException("Cannot create {$model::class} without tenant context");
            }
        });
    }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
```

Applied to: `Client`, `Job`, `JobOccurrence`, `Quote`, `QuoteItem`, `QuoteStatusLog`, `QuoteShareToken`, `Note`, `NoteAttachment`, `NoteEmbedding`, `Address`, `GeocodingCache`, `DistanceCache`, `AIUsageLog`, `PricingSuggestion`, `PricingSuggestionFeedback`, `ChatSession`, `ChatMessage`, `TenantSetting`.

### 4.2 `TenantScope` global scope

`apply()` adds `WHERE tenant_id = current()` if context bound. **Throws** `TenantContextMissingException` if no context AND not in console AND not in explicit `Tenant::bypass()` block. This is the safety belt — silent failures (returning all tenants' rows) would be worse than loud throws.

### 4.3 Subdomain → tenant resolution

```
Request → ResolveTenantFromSubdomain middleware (on app.wyceny.app group)
        → "ania.app.wyceny.app" → tenants.slug = 'ania' → bind Tenant::current() in container
        → fall through to ResolveTenantFromUser if subdomain didn't resolve
        → Filament + global scope use Tenant::current()
```

In M1–M3 every user has one tenant; user-fallback always works. M11+ multi-tenant users: subdomain authoritative.

**[FOUNDER REVIEW]** Subdomain UX: `ania.app.wyceny.app`. Wildcard SSL (Forge handles). Subdomain locked at signup; rename allowed in M5+ with 30-day redirect.

### 4.4 The three places multi-tenant scope leaks in Laravel

1. **Raw queries (`DB::select`, `whereRaw`).** Global scope doesn't apply. **Mitigation:** Larastan custom rule flags `DB::` outside `app/Modules/*/Internal/Repositories/*`. Repos own scoping for raw queries. Pest grep test fails CI on whitelist break.

2. **Queue jobs.** Job dispatched in tenant A's request runs later with no `Tenant::current()`. Throws (good) — but raw `DB::` calls or models missing `BelongsToTenant` would leak. **Mitigation:** every queueable job extends `App\Jobs\TenantAwareJob`:
   ```php
   abstract class TenantAwareJob implements ShouldQueue {
       public string $tenantUlid;
       public function __construct() { $this->tenantUlid = Tenant::current()->ulid; }
       public function handle(): void {
           Tenant::switchByUlid($this->tenantUlid);
           try { $this->execute(); } finally { Tenant::clear(); }
       }
       abstract protected function execute(): void;
   }
   ```
   Pest test: tenant A dispatches job touching a model; assert it can't see tenant B's data even with `withoutGlobalScopes()`.

3. **Broadcast channels / presence channels.** Channel name from input; user could subscribe to other tenant's channel. **Mitigation:** every channel auth callback resolves model via `Tenant::current()`-aware query. Default deny. M1–M3 ships zero broadcast channels; audit at M5+ when chat goes streaming.

### 4.5 Isolation test (S0.4 deliverable)

`tests/Feature/MultiTenant/TenantIsolationTest.php`: creates two tenants, switches between them, asserts row counts match expectations. **Plus a parametric test** that runs the above for every model with `BelongsToTenant`. CI on every PR.

---

## 5. Preset Engine Architecture

The seam that lets cleaning ship today and remonty ship M7 without code changes for fields, vocabulary, templates — only for genuinely new behavior.

### 5.1 `vertical_presets` schema

5 JSONB columns (`vocabulary`, `custom_fields_schema`, `service_types`, `quote_template`, `ai_hints`) + `pdf_template_key` (string). Validated against PHP-side JSON Schemas. Each preset is versioned; `Tenant.preset_version` pins a tenant to a version; we ship preset v2 without breaking existing tenants.

### 5.2 `custom_fields_schema` (cleaning example)

All keys, enum values, and `type` identifiers are English. Display strings reference translation keys (`label_key`) resolved at render time against `lang/{pl,en}.json` per `feedback_i18n_pl_first_us_next.md`. This makes the same preset row work for both locales without duplication.

```json
{ "client": [
    {"key":"area_m2","label_key":"presets.cleaning.fields.area_m2","type":"number","min":1,"max":1000},
    {"key":"property_type","label_key":"presets.cleaning.fields.property_type","type":"select",
     "options":["apartment","house","office","retail"],"required":true},
    {"key":"access_keys","label_key":"presets.cleaning.fields.access_keys","type":"encrypted_text"},
    {"key":"preferences","label_key":"presets.cleaning.fields.preferences","type":"textarea"},
    {"key":"allergies","label_key":"presets.cleaning.fields.allergies","type":"tags"},
    {"key":"access_notes","label_key":"presets.cleaning.fields.access_notes","type":"text"} ],
  "job": [{"key":"difficulty","label_key":"presets.cleaning.fields.difficulty","type":"select","options":["standard","hard"]}] }
```

Translation file `lang/pl.json` resolves these to Polish — e.g., `presets.cleaning.fields.property_type` → `"Typ lokalu"`, options `apartment`/`house`/`office`/`retail` → `"Mieszkanie"`/`"Dom"`/`"Biuro"`/`"Lokal"`. `lang/en.json` parallels with English copy. Filament reads this; `PresetFieldRenderer` maps `type` → Filament Form component, resolving labels via `__($labelKey)`. Encrypted fields write to dedicated encrypted column on the model, not JSONB.

### 5.3 `service_types`

```json
[{"key":"basic","label_key":"presets.cleaning.services.basic","default_unit":"m2","default_rate":4.0,"default_duration_min":120,"ai_hints":"regular weekly clean of furnished apartment"},
 {"key":"deep","label_key":"presets.cleaning.services.deep","default_unit":"m2","default_rate":6.5,"default_duration_min":240},
 {"key":"post_renovation","label_key":"presets.cleaning.services.post_renovation","default_unit":"m2","default_rate":9.0,"ai_hints":"post-renovation, dust + debris"},
 {"key":"windows","label_key":"presets.cleaning.services.windows","default_unit":"piece","default_rate":25.0},
 {"key":"upholstery","label_key":"presets.cleaning.services.upholstery","default_unit":"piece","default_rate":80.0}]
```

`default_rate` is in tenant's currency (PLN for PL tenants, USD for US tenants per `tenant_settings.currency`). Rate per locale is **not** stored in the preset — the preset is currency-agnostic; tenant settings carry the unit.

### 5.4 `quote_template`

```json
{"default_items":[{"service_type_key":"basic","unit":"m2","qty_from":"client.custom_fields.area_m2"}],
 "auto_lines":["commute"], "vat_default":8,
 "rate_modifier_rules":[{"if":"client.custom_fields.property_type == 'office'","rate_multiplier":1.15}]}
```

`QuoteService::createFromPreset(Client, ServiceTypeKey)` evaluates these in PHP. M7 may need DSL bump if remodeling preset wants per-room pricing.

### 5.5 `vocabulary` & `pdf_template_key`

`vocabulary` provides locale-aware label keys (`client_singular`, `job_plural`, etc.) used by Filament resource labels via `__()` reading from current tenant's preset. Each vocabulary key resolves through `lang/{pl,en}.json` so the same preset row works for both locales (per `feedback_i18n_pl_first_us_next.md`). M7 cleaning→remodeling preset overrides `service_default_label_key` from `presets.cleaning.default` → `presets.remodeling.default`; the translation files do the rest. `pdf_template_key='cleaning_v1'` → `resources/views/pdf/cleaning_v1.blade.php`. M7 ships `'remodeling_v1'` — this is **the one piece of the preset that requires code for a new vertical**, by design (PDF layouts have visual taste).

### 5.6 M7 cost matrix

**No code change:** new custom fields, service types, vocabulary, default rates, AI prompt hints — all DB rows. **Code change required:** new PDF Blade (taste-driven); new custom-field types beyond supported set (today: number, text, textarea, select, tags, encrypted_text); genuinely different domain logic (remonty wants per-room line items + materials list = new module, not preset tweak); calculator widget for SEO landing per `seo-strategy.md §4.2`.

### 5.7 Registry & seeding

`PresetRegistry::for(Tenant)` returns immutable `Preset` VO with typed accessors, cached per tenant for 1h, busted by `VerticalPresetUpdated` listener. `database/seeders/CleaningPresetSeeder.php` is idempotent (`updateOrCreate` on slug). Auto-run in CI test setup.

---

## 6. AI Service Abstraction

### 6.1 Interface

```php
interface AIProvider {
    public function complete(PromptRequest $req): AIResponse;          // strict-JSON, retry-once on schema miss
    public function transcribe(AudioFile $a, string $lang): TranscriptionResponse;
    public function embed(string $text): EmbeddingResponse;
    public function vision(ImageFile $img, string $prompt): AIResponse;
}
```

Implementations: `ClaudeProvider`, `OpenAIWhisperProvider`, `OpenAIEmbeddingProvider`. Bound in `AIServiceProvider`. Narrow interface (4 methods) — not a Hexagonal Architecture cathedral.

### 6.2 Consumers

- `Pricing\PricingSuggestionService::suggest(QuoteDraft)` → `ClaudeProvider->complete()` with `prompt_version=v1`, `output_schema=PricingSuggestionSchema`.
- `ClientChat\ClientChatService::ask()` → `RagRetriever::topK()` then `ClaudeProvider->complete()`.
- `WhisperTranscriptionJob` → `OpenAIWhisperProvider->transcribe()`.
- `OcrService::extract()` → `ClaudeProvider->vision()`. Stub in M1–M3 per anti-list.
- `EmbedNoteJob` → `OpenAIEmbeddingProvider->embed()`.

### 6.3 Prompt versioning

Prompts at `app/Prompts/<feature>/<name>.<v>.md`:
```
app/Prompts/Pricing/suggest.v1.md
app/Prompts/ClientChat/answer.v1.md
app/Prompts/Notes/cleanup_transcript.v1.md
```
`PromptRegistry::load('Pricing/suggest','v1')` reads file, parses front-matter for output schema, returns `Prompt` VO. Active version per feature in `config/ai.php`. Logged on every call (`ai_usage_logs.prompt_version`) so quality changes can be attributed.

### 6.4 Strict JSON + retry-once

`ClaudeProvider->complete()` sends prompt with output schema in system message → validates response against schema → on miss, re-sends with error context → on second miss, throws `AIResponseSchemaException`. Caller decides fallback. Logs `schema_miss` to `ai_usage_logs`.

### 6.5 Token + cost logging

Every call writes `ai_usage_logs`. `cost_pln` from per-model rate table in `config/ai.php` (Claude Sonnet $3/$15 per Mtok, Whisper $0.006/min, embeddings $0.02/Mtok input). Static USD→PLN conversion (4.0), updated quarterly — we are not building FX.

### 6.6 Per-tenant monthly cap

`tenants.ai_monthly_cap_pln` (default 50). `AICostGuard::canSpend(Tenant, estimatedCostPln)` throws `AICapExceededException` at hard cap; queues founder + tenant alert at 80%. Reset by daily cron at 00:00 UTC on the 1st.

### 6.7 Fallback (the hard rule)

Per `product-plan.md` §10 + `sprint-plan.md` Op Principles: **every write path works if AI is down.**
- `PricingSuggestionService::suggest()` wrapped in try/catch; UI shows "Sugestia niedostępna." Quote create/save independent.
- `NoteService::create()` for voice: `status='transcribing'` set; transcription job retries with backoff; user can manually edit transcript anytime. Note creation never blocks on Whisper.
- `ClientChat::ask()`: Claude throws → `nie udało się odpowiedzieć`. No DB transaction tied to AI.
- `EmbedNoteJob`: fully async; chat retrieval falls back to recency if no embeddings yet.

### 6.8 RAG context assembly for client chat

```
Input: Tenant, Client, query
1. SQL hard filter: notes WHERE tenant_id=X AND client_id=Y
2. Recency: AND created_at > NOW() - INTERVAL '12 months' (configurable; default 12mo per S6 risk)
3. Embed query → query_vec
4. SELECT note_id, body, score = 1 - (embedding <=> query_vec)
   FROM note_embeddings JOIN notes USING(note_id)
   WHERE [filters 1+2] ORDER BY embedding <=> query_vec LIMIT 8
5. Augment with last 5 zlecenia + last 3 wyceny (structured rows, not embedded — no need)
6. Assemble: system guardrail + XML-tagged context + user query
7. Claude → {answer, citations:[{note_id, snippet}]}
8. Render answer with clickable citations
```

**Embedding rerank only.** Hard filter is SQL. We never trust the vector to scope.

---

## 7. Event-Driven Seams

Laravel events + queued listeners. All events dispatched `afterCommit` (rolled-back create doesn't trigger phantom geocode).

| Event | Module | Listener(s) | Queue | Retry / backoff |
|---|---|---|---|---|
| `ClientCreated` | Crm | `GeocodeClientAddress`, `LogTenantUsage` | `geocoding`, `default` | 3× / 30,60,120s |
| `ClientAddressChanged` | Crm | `GeocodeClientAddress`, `RecomputeDistanceFromOrigin`, `InvalidateAIContextCache` | `geocoding`, `default` | 3× |
| `JobCompleted` | Crm | `LogQuotingMetrics`, `MaybeSuggestRecurring` (M5+) | `default` | 3× |
| `QuoteCreated` | Quoting | `LogTenantUsage`, `IndexQuoteForChat` (M5+) | `default` | 3× |
| `QuoteSent` / `QuoteAccepted` | Quoting | `RecordStatusTransition`, `LogConversionMetric` | `default` | 3× |
| `NoteCreated` (text) | Notes | `EmbedNote` | `embeddings` | 5× exp |
| `NoteCreated` (voice) | Notes | `TranscribeNote` → `MaybeCleanupTranscript` → `EmbedNote` | `transcription`/`default`/`embeddings` | 3× / exp |
| `NoteTranscribed` | Notes | `MaybeCleanupTranscript`, `EmbedNote` | `default`, `embeddings` | 3× |
| `NoteEmbedded` | Notes | `InvalidateAIContextCache` | `default` | 3× |
| `QuoteDraftSaved` | Quoting | `MaybeRequestPricingSuggestion` (first-save, no existing suggestion) | `ai-pricing` | 1× (no retry — UI fallback) |
| `PdfRequested` | Quoting | `RenderQuotePdf` | `pdf-generation` | 3× / 60s |
| `AICostLogged` | AI | `MaybeNotifyCapApproaching` (80%, 100%) | `notifications` | 3× |

---

## 8. Recurrence Engine (`Scheduling`)

`sprint-plan.md S2.2`: 60%+ of cleaning clients are recurring. Critical, easy to over-engineer.

### 8.1 RRULE subset (the contract)

We support exactly:
- `FREQ=WEEKLY;BYDAY=<DAY>` (every Monday)
- `FREQ=WEEKLY;INTERVAL=2;BYDAY=<DAY>` (biweekly)
- `FREQ=MONTHLY;BYMONTHDAY=<N>` (every N-th of month)
- Optional `UNTIL=` and `COUNT=` for end-date.

Rejected: BYSETPOS, BYWEEKNO, multiple BYDAYs, "first Monday of month," every-other-Tuesday-but-not-July. Tenants asking for these enter as one-offs + copy-paste. **[FOUNDER REVIEW]** Cleaning is regular; remonty (M7) often has odd cadences. Revisit then.

### 8.2 Library: hand-rolled, not `simshaun/recurr`

`simshaun/recurr` does full RFC 5545. We need 3 patterns. Hand-rolled `RecurrenceEngine::expand(rule, windowStart, windowEnd)` is ~150 LOC, testable, no dep. **[FOUNDER REVIEW]** If S2 estimate slips, swap to `simshaun/recurr` — 2h refactor, same API surface.

### 8.3 Storage

- `jobs.recurrence_rule` is canonical RRULE.
- `jobs.starts_at` is first occurrence (DTSTART implicit).
- `job_occurrences` rows exist only for **exceptions and completions** (single-occurrence reschedule, skip, mark done). Unmodified expansion computed on read.

### 8.4 Materialization: lazy

**Decision: lazy.** `CalendarQuery::weekFor(tenant, date)`:
1. SELECT one-off jobs in window (`starts_at IN window AND recurrence_rule IS NULL`).
2. SELECT recurring jobs whose [DTSTART, UNTIL] overlaps window; expand each in PHP.
3. Apply override rows from `job_occurrences` (skip, reschedule, complete).

**Why not eager (materialize 26 weeks ahead):**
- DB bloat: 60 recurring × 52 = 3,120 rows/tenant/year. Pointless.
- Backfill on rule edit is bug-prone.
- Lazy is <50ms for week view at our scale. Reverse if S6 measurement shows >800ms.

**Write paths creating override rows:** drag-drop single occurrence (`rescheduled_to`); mark done (`status='done'`, `completed_at`); skip (`status='skipped'`); series edit updates `jobs`, leaves past `job_occurrences` untouched.

### 8.5 Timezone

- All `timestamptz` cols store UTC.
- Render in `Europe/Warsaw` via `FormatsPolish::dateTime()` helper.
- One Pest test crosses DST (`2026-03-29 02:00 Europe/Warsaw`) — assert no occurrence dropped or duplicated.

---

## 9. Calendar / Scheduling Architecture

- **FullCalendar v6** (vanilla JS via Vite) embedded in Livewire component.
- Livewire emits visible window `[startUtc, endUtc]`; fetches occurrences via `CalendarQuery`; renders to FullCalendar event objects.
- Drag-drop emits Livewire action `rescheduleOccurrence(jobId, occurrenceUtc, newUtc)`; server validates, writes `job_occurrences` row, returns success.
- Optimistic UI: FullCalendar updates immediately; on Livewire error, revert with `cal.refetchEvents()`.

### 9.1 Mobile (PWA-lite)

- `manifest.webmanifest` + service worker (workbox via Vite plugin); caches static assets, no offline writes.
- "Add to home screen" tested on wife's iPhone (Safari) per S2.7.
- Mobile renders as FullCalendar list view below 768px.

### 9.2 Performance budget

Weekly view < 800ms (S6 DoD). Achieved by: single SQL UNION (one-offs + recurring), `RecurrenceEngine` in-memory PHP (~10ms for 60 jobs/7 days), no N+1 (eager-load `klient.address`, denormalized `service_type`). Breach response: nightly-regenerated materialized view for next 8 weeks. Don't solve until measured.

---

## 10. Quote / PDF Subsystem

### 10.1 Quote numbering

Pattern `{YYYY}/{MM}/{seq:003}` per tenant, configurable via `tenant_settings.quote_number_pattern`. Allocator uses per-tenant counter row in `tenant_quote_counters` with `lockForUpdate` inside a transaction. Skip Postgres sequences (would be 100s of objects).

### 10.2 PDF: spatie/browsershot

**Decision: `spatie/browsershot`.** Headless Chrome via Puppeteer. Forge installs Chromium once.

**Why over dompdf:**
- Polish typography: Browsershot renders Inter/Roboto with diacritics; dompdf needs custom font registration and bungles ligatures.
- CSS Grid + Flexbox: dompdf is CSS 2.1.
- Wife's eventual branding (M4) involves CSS. Browsershot accepts any HTML/CSS.
- Cost: ~600 MB Chromium; ~1–2s render/PDF (acceptable since queued).

**Trade-off:** Forge env complexity (Chromium install + version pinning) and memory footprint. **[FOUNDER REVIEW]** If S3 deploy hits Chromium issues, fall back to `mpdf` (better Polish than dompdf).

### 10.3 Queueing

`PdfRequested` event → `RenderQuotePdf` job on `pdf-generation` queue (single worker, 60s timeout). Output at `storage/app/quotes/{tenant_ulid}/{quote_number_slug}-{hash}.pdf`. `quotes.pdf_path/pdf_hash` updated.

### 10.4 Caching by content hash

`pdf_hash = sha256(canonical_quote_payload + preset_version + tenant_settings.pdf_branding)`. Unchanged content returns cached file. Bust on quote edit.

### 10.5 Signed-URL share links

`quote_share_tokens.token` is 64-char random (not predictable from quote_id). URL: `https://wyceny.app/q/{token}` (locale-neutral path). Server-side rate limit (10 req/min/token, 100/h/IP). Public route renders SSR Blade (`resources/views/public/shared-quote.blade.php`), `noindex,nofollow`. Accept button (label `__('quote.share.accept')` = "Akceptuj" in pl, "Accept" in en) POSTs to `/q/{token}/accept`, records IP+UA in `quote_status_log`, transitions to `accepted`.

---

## 11. Voice Notes Pipeline

```
Mobile/Web: MediaRecorder → audio/webm or audio/mp4
   ↓ POST /api/notes/upload (multipart)
Server: NoteService::createVoice() → store to B2 via Filesystem disk 'b2'
   → note row {status:'transcribing', source:'voice', audio_path}
   → dispatch TranscribeNoteJob on `transcription` queue
TranscribeNoteJob: OpenAIWhisperProvider->transcribe(audio,'pl')
   → update note.body → fire NoteTranscribed
MaybeCleanupTranscriptJob (only if body>200 chars AND tenant.whisper_cleanup_enabled):
   → ClaudeProvider cleanup → update note.body_cleaned
EmbedNoteJob: embed(body_cleaned ?? body) → upsert note_embedding
```

### 11.1 Retry policy (`sprint-plan.md S4.2`)

- `TranscribeNoteJob`: tries=3, backoff=[30s,60s,120s]. Final fail → `note.status='failed'`; user re-triggers from UI.
- `EmbedNoteJob`: tries=5, exp backoff. Note usable without embedding.
- Idempotent: re-running checks `note.status` and skips.

### 11.2 Mobile mic permissions (iOS Safari quirks per S4.7)

MediaRecorder requires user gesture. iOS ≤14 doesn't support webm; record `audio/mp4` (AAC) — Whisper accepts both. Permission denial: show "Włącz mikrofon w Ustawienia > Safari" with screenshot. Test on wife's actual phone before claiming done.

---

## 12. Embeddings & RAG (S6)

### 12.1 Storage

`note_embeddings` with `embedding vector(1536)` (OpenAI `text-embedding-3-small` dimension).

### 12.2 Index choice: ivfflat

**Decision: ivfflat.** `CREATE INDEX ON note_embeddings USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100);`.

**Why ivfflat over hnsw:**
- We re-embed on note edit → many inserts/updates. ivfflat handles inserts cheaper (no graph rebuild).
- Query latency at <10k notes is identical (sub-ms either way).
- ivfflat needs periodic `REINDEX` after large insert volumes — schedulable (monthly cron starting M5).
- hnsw is right answer at >100k vectors/tenant when query latency dominates. M9+ before that matters.

**[FOUNDER REVIEW]** If pgvector benchmarks show >50ms p95 on chat retrieval, swap to hnsw. Index swap is one migration, no app code change.

### 12.3 Backfill

`php artisan notes:embed-all {--tenant=}`. Idempotent: skips already-embedded notes. Throttled to 100 notes/min for OpenAI rate limits.

### 12.4 Query

```sql
SELECT n.id, n.body, n.created_at, 1 - (e.embedding <=> :q) AS score
FROM notes n JOIN note_embeddings e ON e.note_id = n.id
WHERE n.tenant_id = :tenant_id AND n.client_id = :client_id
  AND n.created_at > NOW() - INTERVAL '12 months' AND n.deleted_at IS NULL
ORDER BY e.embedding <=> :q LIMIT 8;
```

Hard filter (`tenant_id`, `client_id`, recency) is **before** `ORDER BY` — Postgres uses `(tenant_id, client_id)` btree first, then sorts candidates by vector distance. Correct and important.

### 12.5 Embedding versioning

`note_embeddings.model` records embedding model. Upgrade path: `notes:re-embed --model=...` migration in background; switch active model in config. Old embeddings valid until purged.

---

## 13. Integrations Layer

### 13.1 GUS REGON (NIP autofill)

- `GusClient::lookup(NIP): GusResult|null`. Cached per NIP for 30 days (`gus_lookup_cache`).
- Rate limit: GUS allows ~1 req/sec. Wrap with `Redis::throttle('gus',1,1)`. Treated as enhancement: form proceeds without autofill if GUS down/limited.
- Queue-as-retry: failed lookups queue `GusLookupRetryJob` that fills klient when it succeeds (Filament toast).

### 13.2 Google Maps

- `Geocoder::geocode(Address)`. Cached forever per `geocoding_cache.normalized_address`.
- `DistanceCalculator::between(origin, destination)`. Cached forever per `(origin_address_id, destination_address_id)`.
- Recompute only on `ClientAddressChanged` or `TenantOriginChanged`.
- Free tier covers thousands of req/mo; alert at 80% of monthly billing budget.

### 13.3 Payment processor (deferred to M5)

**Tpay:** PL-native, BLIK + Przelewy24, lower friction for PL SMBs (matches `competitor-twojafirma.md` §5). **Stripe:** better DX, recurring billing UX, US-ready for M18+. Decision deferred to M5. Stub: `Billing\BillingStub::canCreateQuote(): true`.

---

## 14. Security & RODO

### 14.1 Encryption at rest

**Decision: Laravel `encrypted` cast on dedicated columns.** `clients.access_keys_encrypted` is `'encrypted'`. Same for future `access_code`, `alarm_pin`.

**Why not application-layer envelope encryption (per-tenant DEK + master KEK):**
- M1–M3 has one tenant. Envelope encryption is overkill until N≥10 paying tenants and a real compliance ask.
- Laravel `encrypted` (AES-256-CBC via APP_KEY) is sufficient for "DB dump doesn't leak keys" (S1.3 ACK criterion).
- Revisit M5+: when first paying customer asks "what if APP_KEY leaks?" add envelope (per-tenant DEK in `tenants.dek_encrypted` + KEK in Vault/KMS).

**[FOUNDER REVIEW]** Founder may want envelope earlier for marketing ("end-to-end encrypted" — though that's misleading; it's at-rest).

### 14.2 Keys & secrets

- `APP_KEY` in Forge env, never in repo. Rotated annually (Laravel supports `previous_keys`). Backup in Bitwarden.
- Secrets: Forge env (`ANTHROPIC_API_KEY`, `OPENAI_API_KEY`, `GOOGLE_MAPS_API_KEY`, `GUS_API_KEY`, `B2_*`). Founder personal: Bitwarden. CI: GitHub Actions secrets.

### 14.3 RBAC

M1–M3: single `owner` role per tenant. M11+: `owner/member/viewer` via Spatie laravel-permission. Out of scope.

### 14.4 Audit log

`audit_logs(id, tenant_id, user_id, action, model_type, model_id, before, after, ip, ua, created_at)` via Spatie Activitylog. Used for: M5 customer support ("kto skasował klienta?"), M11 GDPR DSAR.

### 14.5 DPA & data residency

All data on Hetzner Falkenstein (EU). Whisper/OpenAI/Claude are processors named in DPA + sub-processor list. DPA template based on EDPB SCCs, signed before inviting firma #2 (M4). Personal data minimization: don't log klient names in error logs / Sentry breadcrumbs — use IDs only.

---

## 15. Observability & Ops

### 15.1 Logging

Structured JSON via Monolog JsonFormatter. Every entry: `tenant_id`, `user_id`, `request_id`, `feature`, `level`, `message`, `context`. Forge → daily-rotated file; Sentry catches errors.

### 15.2 Metrics (day 1)

- **AI cost / tenant / month** — query `ai_usage_logs`, render in Filament admin.
- **Queue depth** per Horizon queue — Horizon dashboard.
- **p95 quote-create latency** — Sentry performance.
- **Weekly Active Quoting / tenant** (North Star) — `quotes` × week, cron-rolled into `metrics_weekly`.
- **Error rate** per route — Sentry.

### 15.3 Error tracking: Sentry yes

Free tier through M6. SDK in Laravel + JS. `tenant_id`, `user_id` set as scope per request.

### 15.4 Uptime: BetterStack

60s ping on `/up`. Slack/Discord webhook on failure. **[FOUNDER REVIEW]** If founder doesn't run Slack, swap to email + push.

### 15.5 Backups

- Postgres: daily `pg_dump` to Backblaze B2 (Frankfurt), retained 30 days. Forge backup task at 03:30 UTC.
- Encrypted with `gpg --symmetric`, passphrase in Bitwarden — separate from APP_KEY.
- Restore drill: end of S3 (per `sprint-plan.md` §11). Founder restores latest to scratch DB, verifies row counts. **Blocks S4 if it fails.**

---

## 16. Background Jobs / Queue Topology

Eight Horizon queues, separated to isolate failure modes and tune concurrency.

| Queue | Concurrency | Timeout | Tries | Backoff | Purpose |
|---|---|---|---|---|---|
| `default` | 5 | 60s | 3 | exp | Catch-all: events, listeners, small jobs |
| `ai-pricing` | 2 | 30s | 1 | — | Pricing suggestion (no retry — UI fallback) |
| `transcription` | 2 | 300s | 3 | 30,60,120s | Whisper jobs (large audio) |
| `embeddings` | 3 | 30s | 5 | exp | OpenAI embeddings (rate-limit-tolerant) |
| `pdf-generation` | 1 | 60s | 3 | 60s | Browsershot (single worker, memory-heavy) |
| `geocoding` | 1 | 30s | 3 | 60s | Google Maps API (rate-limit-bound) |
| `notifications` | 5 | 15s | 5 | 30s | Email, Discord webhooks, AI cap alerts |
| `chat-rag` | 3 | 60s | 1 | — | Synchronous user-facing AI chat (no retry — UI error) |

**Rationale for separate queues:** `pdf-generation` single-worker (Browsershot memory-hungry; runaway shouldn't starve others). `transcription` long timeout (5 min — Whisper slow on long audio; isolate timeout pile-up from `default`). `ai-pricing`/`chat-rag` no retries (stale AI worse than no AI). `embeddings` 5× retries (OpenAI 429s; embeddings can be eventually consistent).

Horizon balancing: `auto`, min 1 / max 10 processes, scaling by queue length. 256 MB memory per supervisor.

---

## 17. Public Landing vs App Split

**Decision: single Laravel app, route-grouped by host.** Not two codebases.

### 17.1 Routing

```php
// Public marketing — wyceny.app
Route::middleware('domain.public')->domain(config('app.public_domain'))
     ->group(__DIR__.'/public.php');     // homepage, /funkcje, /cennik, /przewodnik/*, /kalkulator/*

// App — *.app.wyceny.app
Route::middleware(['domain.app','auth','resolve.tenant'])
     ->domain('{subdomain}.'.config('app.app_domain'))
     ->group(__DIR__.'/app.php');        // Filament + share tokens
```

Filament panel registered at app domain only. Public domain has zero Filament.

### 17.2 SSR enforcement (per `seo-strategy.md §5.1`, `sprint-plan.md S0.11`)

All public routes return server-rendered HTML; Livewire allowed for interactivity but **first paint** contains SEO-critical text (h1, meta, body).

`tests/Feature/SeoSsrTest.php`:
```php
it('public homepage is fully SSR', function () {
    $r = $this->withHeaders(['User-Agent'=>'Googlebot'])->get('/');
    $r->assertOk();
    $html = $r->getContent();
    expect($html)->toMatch('/<title>[^<]{10,}<\/title>/')
        ->toMatch('/<meta name="description" content="[^"]{120,160}"/')
        ->toContain('rel="canonical"')
        ->toContain('Wyceny — CRM');  // controller-rendered text, proves not Livewire-only
});
```

### 17.3 `noindex` on app subdomain

Middleware `EnforceNoindex` on `domain.app` group sets `X-Robots-Tag: noindex, nofollow` plus `<meta name="robots" content="noindex, nofollow">` in Filament layout.

### 17.4 Why single app

Shared models, auth, Eloquent. One deploy, one CI, one log. Public uses SSR Blade + minimal Livewire; app uses Filament. Different stacks within one Laravel — totally normal. Splitting (Next.js + Laravel API) is N+1 weeks of work for nothing M1–M3 needs.

**[FOUNDER REVIEW]** If marketing copy ever needs CMS-style editing by non-dev, ship Filament Pages resource for content blocks. Don't reach for Webflow/Sanity until M6+.

---

## 18. Risks & Non-Goals

### 18.1 Top 5 architectural risks

1. **Multi-tenant scope leak.** Eloquent global scope necessary, not sufficient. **Mitigation:** §4.4 — Larastan rule, `TenantAwareJob` base, parametric isolation tests in CI per model.
2. **AI cost runaway.** Bug or spam tenant racks Claude bills overnight. **Mitigation:** per-tenant monthly cap + 80% alert + hard-fail on exceed (§6.6). Daily cron query: total >200 PLN/day = founder pager.
3. **Embedding drift.** Notes edited → stale embeddings → chat retrieves wrong. **Mitigation:** `NoteEmbedding` regenerated on `NoteUpdated`; idempotent embed job; `notes:re-embed` for batch fixups.
4. **Recurrence-engine swamp.** RFC 5545 is a tar pit. **Mitigation:** explicit RRULE allowlist (§8.1), validator rejects others, lazy expansion, one DST test. Reject "every 3rd Thursday" requests; revisit M7.
5. **Premature `AIProvider` abstraction.** We abstracted Claude but only ever call Claude in M1–M3. **Mitigation:** narrow interface (4 methods), not Hexagonal. The goal isn't swap — it's making prompts swappable. Accept ~50 LOC overhead.

### 18.2 Non-goals (M1–M3)

Microservices. GraphQL. Event sourcing. Message bus beyond Redis/Horizon (no Kafka, NATS, RabbitMQ). Native mobile (PWA covers per `sprint-plan.md` §10).

### 18.3 Risks not in existing docs

- **Hetzner Falkenstein single-region.** Falkenstein outage = whole product down. **Mitigation:** B2 backups in Frankfurt (different provider, region). Restore-to-different-region runbook by M4. Acceptable for ~5–50 tenants; revisit M11.
- **Browsershot Chromium pinning.** Auto-updates can break PDF rendering silently. **Mitigation:** pin Chromium in `composer.json` post-install hook; render "golden PDF" in CI, visual-diff.
- **Polish-typography PDF QA.** Diacritics, Polish quotes (`„x"`), `zł` formatting — wife notices in 0.3s. **Mitigation:** `tests/Feature/PdfPolishTypographyTest.php` renders fixed PDF, hashes, fails CI on unexpected change.

---

## 19. ADR List (S0 deliverables)

| # | ADR | Unblocks |
|---|---|---|
| ADR-001 | Modular monolith over microservices | All sprints |
| ADR-002 | Multi-tenancy: single DB + `tenant_id` global scope | S0.4, S1+ |
| ADR-003 | Tenant ID format: ULID + slug | S0.4 |
| ADR-004 | Custom-fields storage: JSONB column on entities | S1.2 |
| ADR-005 | Preset engine schema shape (5-key JSONB) | S0.6, S1, M7 |
| ADR-006 | AIProvider interface scope and prompt versioning | S5, S6 |
| ADR-007 | Recurrence engine: hand-rolled, RRULE subset, lazy materialization | S2 |
| ADR-008 | PDF rendering: spatie/browsershot over dompdf | S3 |
| ADR-009 | pgvector index: ivfflat over hnsw at MVP scale | S6 |
| ADR-010 | Public + app split: single Laravel app, route-grouped by host | S0.11, S5 |
| ADR-011 | Encryption at rest: Laravel `encrypted` cast on sensitive columns | S1.3 |
| ADR-012 | Horizon queue topology (8 queues, per-queue tuning) | S2, S4, S5, S6 |
| ADR-013 | Dev environment: Docker-only, no local installations | S0.1, S0.9, S0.10 |
| ADR-014 | i18n strategy: PL primary, EN parallel, all UI via `__()`, code/DB English-only | All sprints |
| ADR-015 | No Polish identifiers in code/DB (entity names, columns, JSON keys, route segments) | S0.4, S0.6, S0.8 |

---

## 20. Implementation Order Map

### 20.1 S0 stories → architectural artifacts

| Story | Artifacts |
|---|---|
| S0.1 Repo + Laravel skeleton | `composer.json`, `app/Modules/` layout, PSR-4 autoload, Pest scaffolding |
| S0.2 Hetzner + Forge | `forge.yml` deploy script, `config/` env matrix, `/up` health endpoint |
| S0.3 Postgres + pgvector | Migration `enable_pgvector.php`, `tests/Feature/PgvectorSmokeTest.php` |
| S0.4 Multi-tenancy | `Tenancy\Concerns\BelongsToTenant`, `Tenancy\TenantScope`, `Tenancy\Middleware\ResolveTenantFromSubdomain`, `App\Jobs\TenantAwareJob`, `tests/Feature/MultiTenant/TenantIsolationTest.php`, ADR-002, ADR-003 |
| S0.5 Auth | Filament login, `users` migration, tenant-aware password reset |
| S0.6 Preset schema | `vertical_presets` migration, JSON Schemas in `Presets\Schemas\`, ADR-005 |
| S0.7 Preset registry | `Presets\PresetRegistry`, `Presets\Preset` VO, cache layer, `Tenant::preset()`, parametric test |
| S0.8 Migrations | All migrations from §3.1, `BelongsToTenant` traits applied, factories+seeders, `CleaningPresetSeeder` |
| S0.9 Dev env (Docker, mandatory) | `docker-compose.yml` (postgres+pgvector, redis, mailhog, app php-fpm, nginx, horizon, browsershot/chromium sidecar, optional vite/node), `Dockerfile`s, `bin/` wrapper scripts, `Makefile`, ADR-013. **Herd/Valet/local PHP off the table.** |
| S0.10 CI | `.github/workflows/ci.yml`: Pint + Larastan lvl 6 + Pest + isolation tests |
| S0.11 SEO foundations | `tests/Feature/SeoSsrTest.php`, `Middleware\EnforceNoindex`, `routes/public.php` skeleton, `View\Components\SchemaOrg\*`, `/docs/seo.md`, ADR-010 |

### 20.2 S1–S6 — one architectural unit per sprint

| Sprint | One unit added |
|---|---|
| S1 | **Custom-fields rendering pipeline.** `Presets\PresetFieldRenderer` reads preset JSON → emits Filament Form components. `Crm\Validators\CustomFieldsValidator`. First real consumer of `Tenant::preset()`. |
| S2 | **Recurrence engine + calendar bridge.** `Scheduling\RecurrenceEngine`, `Scheduling\CalendarQuery`, FullCalendar Livewire bridge, drag-drop persistence + `job_occurrences` table. |
| S3 | **Quote rendering pipeline.** `Quoting\QuoteService`, `QuoteNumberAllocator`, `Pdf\PdfRenderer` (Browsershot wrapper), `pdf-generation` queue, signed-share-token public route. |
| S4 | **Voice→text pipeline + integrations.** `Notes\NoteService::createVoice()`, `AI\Whisper\WhisperTranscriptionJob`, `Integrations\Geocoder` + `DistanceCalculator` with caches, commute auto-line in `QuoteService`. |
| S5 | **`PricingSuggestionService` + prompt versioning.** `AI\AIProvider` + `ClaudeProvider`, `Pricing\PricingSuggestionService`, `Pricing\PricingContextBuilder`, `app/Prompts/Pricing/suggest.v1.md`, `ai_usage_logs` writes, per-tenant cap guard, fallback UX. |
| S6 | **Embeddings + RAG.** `note_embeddings` + ivfflat index, `AI\Embeddings\EmbedNoteJob` + backfill command, `ClientChat\RagRetriever`, `ClientChat\ClientChatService`, citations rendering, hard-filter SQL guardrails. |

End state at S6: every architectural seam is wired and exercised by wife's production tenant. M7 second-vertical work requires only: a new preset row, a new PDF Blade, possibly a new custom-field type. No module boundaries move. No table schemas change.

---

## 21. Internationalization (i18n)

Per `feedback_i18n_pl_first_us_next.md` and `feedback_no_polish_in_code.md` — PL is launch locale, US/EN is the next market. Build the i18n seam from S0.

### 21.1 Translation files

```
lang/
  pl.json   ← primary, fully populated, source of truth for copy
  en.json   ← parallel, populated as features ship; mostly empty until M18
  pl/
    presets/cleaning.php     ← preset-specific translations
    pdf/cleaning_v1.php      ← PDF template strings
    validation.php           ← Laravel default
  en/   ← parallel structure
```

Default locale: `pl`. Fallback locale: `en` (so missing PL keys at least render English, not raw key). Set in `config/app.php` from `tenant_settings.locale` once multi-locale is live; until M18 every tenant is `pl`.

### 21.2 Translation rules (the contract)

- **Every user-facing string flows through `__('key')`, `@lang('key')`, or `Lang::get()`.** No raw Polish string literals in Blade, Livewire components, Filament resources, validation messages, mail templates, or PDF Blades. Linter to enforce: a Pest test that greps `resources/views/**/*.blade.php` and `app/**/*.php` for non-ASCII characters in string literals (excluding test fixtures and `lang/`).
- **Filament resource labels via override:**
  ```php
  public static function getLabel(): string { return __('clients.singular'); }
  public static function getPluralLabel(): string { return __('clients.plural'); }
  ```
- **Preset JSON uses `label_key` (translation keys), not literal strings.** See §5.2.
- **Enum values in code/DB are English** (`apartment`, `office`, `commute`, `basic`, `deep`, `post_renovation`). UI rendering pulls Polish display via `__('enums.cleaning.property_type.apartment')`.
- **Polish acronyms render as-is** (NIP, REGON, GUS, RODO, KSeF) — they're proper nouns in both locales.
- **Currency, dates, numbers** use Laravel's `Number::currency()` and `Carbon::translatedFormat()` with locale from `app()->getLocale()`. PL: `1 234,56 zł`, `25 kwiecień 2026`. EN: `$1,234.56`, `April 25, 2026`.

### 21.3 Tenant-level locale (Year-2 readiness)

`tenant_settings.locale` exists from S0 (`enum('pl','en')`, default `'pl'`). `SetLocaleMiddleware` reads it and calls `app()->setLocale()` early in the request lifecycle. Until M18 it's effectively a constant; the column existing means M18 is config, not migration.

### 21.4 URL strategy (per `seo-strategy.md` §5.6)

- Year 1: `wyceny.app/*` — implicit `pl`, no prefix.
- Year 2: `/en/*` prefix added. PL stays at root. `hreflang` tags emitted on every public page.
- App routes (`/clients`, `/quotes`, `/jobs`) are English regardless of locale — UI is locale-aware, paths are not (this is the right call for SaaS apps; it's how Stripe, Linear, Notion all do it).
- Public landing pages may use translated slugs (`/cennik` ↔ `/en/pricing`) — handled via Laravel route binding from translation file at boot.

### 21.5 What this constrains in S0

- `lang/pl.json` and `lang/en.json` exist on day 1, even mostly empty.
- `tenant_settings` migration includes `locale` column from S0.8.
- `SetLocaleMiddleware` registered in `app/Http/Kernel.php` from S0.5.
- Pest test forbids non-ASCII characters in string literals in app code (excluding `lang/`, fixtures).

---

## 22. Dev Environment — Docker Only

Per `feedback_docker_only.md` — every developer runs the project through Docker. No local Postgres, no local PHP, no local Node, no Herd, no Valet. CI runs the same Dockerfile.

### 22.1 `docker-compose.yml` (S0.9)

Services:

| Service | Image / build | Port | Purpose |
|---|---|---|---|
| `app` | `./docker/app/Dockerfile` (php:8.3-fpm-alpine + extensions) | — | Laravel app, Horizon, artisan |
| `nginx` | `nginx:alpine` | 80 | Reverse proxy → app |
| `postgres` | `pgvector/pgvector:pg16` | 5432 | DB with pgvector pre-installed |
| `redis` | `redis:7-alpine` | 6379 | Cache, queue, session |
| `mailhog` | `mailhog/mailhog` | 8025 | Local email testing |
| `chromium` | `browserless/chrome` | 3000 | Browsershot PDF rendering (sidecar, pinned version) |
| `node` | `node:20-alpine` | 5173 | Vite dev server (only when running `npm run dev`) |

All services on a single bridge network. App container has `./` volume-mounted for hot reload. Postgres data and Redis state on named volumes (persist across `down`/`up`).

### 22.2 Pinned versions

```
PHP 8.3.x (specific patch in Dockerfile FROM)
Postgres 16 + pgvector 0.7.x (image tag)
Redis 7.2.x
Node 20.x
Chromium pinned (browserless tag, NOT `:latest`) — per architecture risk note about silent breakage
```

Versions live in `docker/versions.env`, sourced by all Dockerfiles. Single bump = consistent across all containers.

### 22.3 Wrapper scripts (low typing burden)

```bash
bin/art         # docker compose exec app php artisan "$@"
bin/composer    # docker compose exec app composer "$@"
bin/test        # docker compose exec app vendor/bin/pest "$@"
bin/pint        # docker compose exec app vendor/bin/pint "$@"
bin/stan        # docker compose exec app vendor/bin/phpstan analyse
bin/npm         # docker compose run --rm node npm "$@"
bin/db          # docker compose exec postgres psql -U postgres
make up         # docker compose up -d
make down       # docker compose down
make fresh      # down + remove volumes + up + migrate:fresh + seed
make logs       # docker compose logs -f
```

Anything more typed than that becomes friction; these are the daily drivers.

### 22.4 CI (S0.10)

GitHub Actions pulls the same Dockerfile, runs Pest + Pint + Larastan inside the app container against a postgres+redis service container. **No `shivammathur/setup-php` action.** No host PHP. Forces dev-CI-prod parity at every push.

### 22.5 Production parity

Forge box runs Docker containers from the same registry-built image that CI tests. Hetzner managed Postgres only used if pgvector confirmed; otherwise self-managed Postgres container on the Forge box (per `sprint-plan.md` S0 risk note). The trade is: managed-Postgres operational simplicity vs. dev/prod schema parity. The latter wins.

### 22.6 What this rules out

- Herd, Valet, Laragon, MAMP — explicitly off the table.
- `brew install postgresql` for "just one quick test" — never.
- "Works on my machine because I have Node 18" — impossible by construction.
- IDE-bundled PHP for code-style checks — Pint runs in container, IDE reads result.
