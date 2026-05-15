---
name: No Polish in code or DB
description: All code identifiers and DB schema must be English; Polish lives only in user-facing UI strings (via i18n) and business prose
type: feedback
---

**Rule:** Code identifiers (class names, file names, model names, controller names, event/listener names, queue names, route names) AND database schema (table names, column names, JSON keys stored in DB, preset config keys) must be in **English**.

**Why:** PL is the first market but US is next (see `feedback_i18n_pl_first_us_next.md`). Polish identifiers in code/DB lock the codebase to Polish-speaking developers and make EN expansion a rename project. English code + Polish UI strings via i18n is the only pattern that scales to multi-locale.

**How to apply:**

- **Always English:** entity names (`Client` not `Klient`, `Job` not `Zlecenie`, `Quote` not `Wycena`, `Note` not `Notatka`), DB columns (`number` not `numer`, `valid_until` not `ważna_do`, `subtotal` not `suma_netto`), event/listener class names, route segments (`/quotes` not `/wyceny`), JSON keys in preset/config (`property_type` not `typ_lokalu`).
- **Polish stays in:** user-facing UI strings rendered via `__()` / `@lang` from `lang/pl.json`; presentation labels in preset JSON via `label_key` referencing translation keys; Polish acronyms that ARE the legal/technical thing (NIP, REGON, GUS, RODO, KSeF, JDG); business prose in non-code docs (`product-plan.md`, `homepage-research.md`).
- **Edge case — preset JSON in DB:** keys are English (`property_type`), display labels reference translation keys (`label_key: "presets.cleaning.fields.property_type"`), translation files hold the Polish `Typ lokalu` text.
- **Edge case — domain prose in technical docs:** entity names mentioned in story titles or architecture prose use the English name (`Client + Onboarding` not `Klient + Onboarding`). Polish remains in copy/UX/marketing prose where it describes user behavior.
