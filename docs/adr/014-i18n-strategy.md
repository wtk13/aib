# ADR-014: i18n Strategy — PL Primary, EN Parallel

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
- PL is launch locale; EN is Year-2 expansion. Both `lang/pl.json` and `lang/en.json` exist from day 1.
- Every user-facing string uses `__('key')` — no raw Polish string literals in app code.
- DB values, code identifiers, enum values, JSON keys: English only.
- Polish displayed via translation files.

## Rationale
Retrofitting i18n later costs a full sprint. Building the seam costs ~2h at sprint 0.
