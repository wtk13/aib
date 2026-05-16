# ADR-016: AI-First Product Architecture

**Date:** 2026-05-16
**Status:** Active

---

## Context

The application was initially framed as a quoting and CRM tool ("Wyceny") for a cleaning business. As of Sprint 2, the product is repositioned as a personal AI business assistant ("Twój Asystent") — one that proactively surfaces insights, proposes pricing, and reduces the cognitive overhead of running a service business solo.

This ADR captures the architectural principles that follow from that positioning. It does not supersede technical ADRs 001–015, which remain in effect.

---

## Decisions

### 1. Dashboard is the home screen

The Filament default resource-list landing page is replaced by a purpose-built dashboard. The dashboard is the primary UI surface — not an optional extras area. It answers "what do I need to know right now?" without requiring navigation.

The dashboard renders data from the operational layer (jobs, clients) and, from Sprint 3 onward, AI-generated insights (alerts, pricing signals).

### 2. Notes are AI input

`Note.body` is free text entered after client interactions. Notes are stored alongside vector embeddings in `note_embeddings` (pgvector column type). The AI reads notes to understand client context when generating pricing suggestions. The embedding pipeline runs asynchronously via a queued job after note save.

### 3. Pricing is grounded in history

AI pricing suggestions (`pricing_suggestions` table) are generated from:
- Client custom fields: `area_m2`, `property_type`
- Requested service type
- Commute distance from home base to client
- Historical job data: prices and durations from similar completed jobs
- Preset `ai_hints.pricing_factors` and `ai_hints.cold_start_note`

The `pricing_suggestion_feedback` table captures whether the user accepted, rejected, or adjusted each suggestion — closing the learning loop.

### 4. Commute is a first-class pricing input

Every job price must account for the round-trip commute cost. The data model supports this end-to-end:

- `tenant_settings.origin_address_id` → FK to `addresses` (home base, geocoded)
- `tenant_settings.fuel_rate_pln_per_km` → cost per km (configurable, default 1.80 PLN)
- `distance_caches` table → pre-computed distances between address pairs (never re-fetched once computed)
- `quote_template.auto_lines: ['commute']` in the cleaning preset → commute is automatically a quote line item

Commute cost = `distance_km × 2 × fuel_rate_pln_per_km` (round trip).

### 5. Preset = domain knowledge for AI

The `vertical_presets` record for a given tenant is the AI's domain vocabulary:
- `service_types` → which services exist, their default rates and durations
- `ai_hints.pricing_factors` → which client/job fields matter for pricing
- `ai_hints.cold_start_note` → how to behave before job history exists
- `quote_template.rate_modifier_rules` → rule-based adjustments (e.g., office surcharge)

The AI reads the preset before generating any suggestion. Swapping presets generalizes the AI to a different trade.

### 6. AI usage is cost-controlled per tenant

- `tenant_settings.ai_monthly_cap_pln` → hard cap on AI API spend per tenant per calendar month
- `ai_usage_logs` → every AI API call is logged with cost in PLN
- When spend approaches the cap, an alert fires to `tenant_settings.ai_alerts_email`
- When the cap is reached, AI features degrade gracefully (show preset defaults instead of learned suggestions)

---

## Consequences

- The dashboard (Sprint 2) must be built before AI features (Sprint 3) — it is the primary surface where AI insights will appear.
- AI pricing features depend on real job history — the assistant delivers more value after weeks of real usage than on day one.
- Every new feature that touches pricing must account for commute cost.
- New service verticals require a new preset record + seeder — not a code change.
