# ADR-004: Custom Fields Storage — JSONB Column

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
`clients.custom_fields` and `jobs.custom_fields` are JSONB columns. Schema lives in `vertical_presets.custom_fields_schema`. Application-layer validation via `CustomFieldsSchemaValidator`.

## Rationale
One preset per tenant in M1–M3. Filament can render JSONB fields directly. GIN index supports `custom_fields->>'key' = 'value'` queries at our scale. Side-table EAV approach adds a join+pivot UX that Filament doesn't give for free.

## Consequences
Schema migration when a preset renames a field at M7+ requires a one-off Laravel command. Cross-tenant reporting needs JSONB extraction.
