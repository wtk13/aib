---
name: i18n — PL first, US next
description: Build i18n into the architecture from day 1; PL is launch locale, US/EN is the next expansion
type: feedback
---

**Rule:** Treat the application as multi-locale from day 1. PL is the launch locale (Year 1). US/EN is the next market (Year 2–3 per `product-plan.md` §9). Every user-facing string flows through Laravel translation files. No hard-coded Polish strings anywhere in code, components, or DB seed data.

**Why:** Retrofitting i18n after launch costs more than building it in. The product plan and SEO strategy both call out a US expansion in Year 2 (`seo-strategy.md` §5.6 reserves `/en/` URL prefix). Retrofitting means: (a) hunting every hard-coded Polish string in Blade/Livewire/Filament, (b) reshaping preset JSON to handle locale-aware labels, (c) URL-structure migration. Cheap now, expensive later.

**How to apply:**

- **Translation files:** `lang/pl.json` (primary, fully populated), `lang/en.json` (parallel, populated as features ship). PL is default locale; EN exists from S0 even if mostly empty.
- **Code accesses strings via:** `__('key')`, `@lang('key')`, `Lang::get()`. No raw Polish in Blade/Livewire/Filament resource labels.
- **Filament resource labels:** override `getLabel()` / `getPluralLabel()` to call `__()`. Don't hard-code Polish in the resource class.
- **Preset JSON:** field/service labels reference `label_key` translation keys, not literal Polish strings. Vocabulary blocks in presets are translation-key references.
- **Routes:** route segments are English (`/clients`, `/quotes`). Public landing under PL slugs is acceptable IF mapped to translated routes (`Route::get(__('routes.pricing'), ...)`) — but prefer English path + locale-aware page content first.
- **Year 2 EN expansion:** `/en/` URL prefix (per `seo-strategy.md` §5.6). Locale switcher in tenant settings. `hreflang="pl"` + `hreflang="en"` + `hreflang="x-default"` once EN ships.
- **Polish-locked items that DO NOT translate:** legal/technical Polish acronyms used as proper terms (NIP, REGON, GUS, RODO, KSeF). These render as-is in both locales.
