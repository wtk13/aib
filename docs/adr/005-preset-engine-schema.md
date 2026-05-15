# ADR-005: Preset Engine Schema Shape — 5-Key JSONB

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Each `vertical_presets` row has five JSONB columns: `vocabulary`, `custom_fields_schema`, `service_types`, `quote_template`, `ai_hints`. Plus `pdf_template_key` (string). All label strings are translation keys resolved via `lang/{locale}.json` at render time — no Polish strings in the preset row.

## Rationale
Adding a new cleaning field or service type is a DB update + seeder change — no code deploy. The one exception is a new PDF Blade when a genuinely new visual layout is needed for a new vertical (by design: PDF layout has taste).
