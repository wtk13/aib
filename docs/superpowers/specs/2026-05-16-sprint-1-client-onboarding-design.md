# Sprint 1 — Client + Onboarding Design

**Date:** 2026-05-16
**Sprint:** 1 (Weeks 2–3)
**Stories:** S1.1, S1.2, S1.3, S1.4, S1.5, S1.6, S1.7, S1.8
**Source docs:** `sprint-plan.md §3`, `product-plan.md §5`, `architecture.md`

---

## Goal

Wife can register her own tenant, enter all her real clients with cleaning-specific custom fields (including encrypted access keys), search and filter them, and add text notes. NIP lookup autofills B2B client data from GUS. Address geocoding runs in the background for future commute calculations.

**Demo at end:** Wife enters 10 real clients including the 2 B2B ones (NIP autofilled). All clients visible in Filament, filterable by type and property type.

---

## Approach

Option A — Filament-first, hard-coded cleaning fields. Custom fields for the cleaning preset are named Filament form components (no dynamic renderer). Registration is a Filament guest page with a two-step Wizard. Everything in one UI system.

---

## Architecture

Four units added on top of the Sprint 0 skeleton:

| Unit | Responsibility |
|---|---|
| `Filament\Pages\Register` | Public two-step wizard: account → industry. Atomically creates Tenant + User, seeds preset, logs in. |
| `ClientResource` (Filament) | Full CRUD with hard-coded cleaning custom fields. Notes relation manager. Search + filters. |
| `GusNipLookupService` | Wraps GUS BIR1 API. Called via Filament form Action on NIP field. Fills name, address, REGON. |
| `GeocodingService` | Wraps Google Maps Geocoding API. Dispatched as queued job after address save. Stores lat/lng. |

---

## Stories

### S1.8 — Registration + Onboarding

**Route:** `/register` (public, guest-only) served by `App\Filament\Pages\Register`.

**Flow:**
1. **Step 1 — Account:** name (person or company name), email, password, password_confirmation
2. **Step 2 — Industry:** radio cards built from `VerticalPreset::all()` (currently one option: "Sprzątanie / Cleaning")

**On submit (DB transaction):**
- Generate ULID + slug (slugified name, unique)
- Create `Tenant` with `preset_id` pointing to selected vertical
- Create `User` with `tenant_id`, role=`owner`, hashed password
- Run `CleaningPresetSeeder` idempotently (already idempotent from Sprint 0)
- Log user in via `Auth::login()`
- Redirect to `/admin`

**Validation:**
- Email unique in `users` table (scoped globally — emails are globally unique across tenants)
- Slug uniqueness enforced at DB level; regenerate with suffix on collision
- Password min 8 chars

**Existing login** stays at `/admin/login`. Registration link added to login page footer.

---

### S1.1 — Client Filament Resource (generic fields)

**Migration:** `add_client_type_to_clients_table` — adds `client_type` enum `('person', 'company')` NOT NULL DEFAULT `'person'`.

**`ClientResource`** with pages: ListClients, CreateClient, EditClient, ViewClient.

Generic fields (all client types):
- `client_type` — select (Osoba fizyczna / Firma), shown first, drives conditional visibility
- `name` — text, required
- `phone` — text
- `email` — email
- Address section (inlined fields from `Address` belongsTo): `street`, `city`, `postal_code`; on save, creates or updates the related `Address` record and sets `clients.address_id`
- `nip` — text, 10 chars, visible only when `client_type = company`
- `regon` — text, 9 or 14 chars, visible only when `client_type = company`

---

### S1.2 — Custom Fields from Preset (hard-coded cleaning)

Stored in `custom_fields` JSONB column on `clients`.

Fields (all client types unless noted):
- `area_m2` — numeric, label "Powierzchnia (m²)"
- `property_type` — select, options from `preset->vocabulary()['property_types']`: mieszkanie / dom / biuro / lokal użytkowy
- `preferences` — textarea, label "Preferencje / uwagi"
- `allergies` — textarea, label "Alergie / przeciwwskazania"
- `access_notes` — textarea, label "Dostęp (domofon, piętro, pies...)"

Stored in `clients.custom_fields` as `['area_m2' => 65, 'property_type' => 'mieszkanie', ...]`.

`CustomFieldsSchemaValidator::validate()` (built in Sprint 0) called in `ClientResource` `mutateFormDataBeforeCreate/Save` hooks.

---

### S1.3 — Encrypted Storage for Sensitive Fields

Stored in `clients.access_keys_encrypted` (separate column, Laravel `encrypted` cast — already in schema and model).

Field:
- `access_keys` — textarea, label "Klucze / kody dostępu", hint "Nie jest widoczne w eksportach ani logach"

Form component reads/writes via `access_keys_encrypted` attribute name. Filament's `Textarea::make('access_keys_encrypted')` with dehydration handling.

---

### S1.4 — Notes per Client (text only)

`NoteRelationManager` on `ClientResource::getRelations()`.

Behavior:
- **Create:** textarea `content` (required), `user_id` auto-set to `Auth::id()`, `tenant_id` auto-set via `BelongsToTenant`
- **No edit** — notes are append-only (timeline semantic)
- **Delete:** allowed with confirmation
- **Display:** table sorted `created_at DESC`, columns: content (truncated to 120 chars), author name, relative timestamp ("2 godziny temu")
- `Note` model already exists from Sprint 0 migrations

---

### S1.5 — GUS NIP Autofill

**`App\Modules\Integrations\Gus\GusNipLookupService`**

```php
public function lookup(string $nip): ?array
// returns ['name' => ..., 'street' => ..., 'city' => ..., 'postal_code' => ..., 'regon' => ...]
// returns null on not found, logs warning on API error
```

- GUS BIR1 REST endpoint: `https://wyszukiwarkaregon.stat.gov.pl/api/`
- API key from `config('services.gus.api_key')` → `env('GUS_API_KEY')` (placeholder: `'placeholder'`)
- HTTP timeout: 5s. On timeout/error: return null, show Filament warning notification
- Cache response by NIP for 30 days (`Cache::remember("gus:nip:{$nip}", 2592000, ...)`)
- Rate limiting: GUS allows ~1000 req/day; cache makes this a non-issue

**Filament integration:** `Action::make('lookup_nip')` on the NIP field, label "Pobierz z GUS". Visible only when `client_type = company`. On success fills: `name`, `regon`, `address.street`, `address.city`, `address.postal_code`. Uses `$livewire->form->fill([...])`.

---

### S1.6 — Address Geocoding on Save

**`App\Modules\Integrations\Geocoding\GeocodingService`**

```php
public function geocode(Address $address): void
// Calls Google Maps Geocoding API, sets $address->lat and $address->lng, saves
```

- API key from `config('services.google_maps.api_key')` → `env('GOOGLE_MAPS_API_KEY')` (placeholder: `'placeholder'`)
- Input: concatenated `"{$street}, {$city}, Polska"`
- On API error or no result: log warning, leave lat/lng null — silent failure
- Cache forever by normalized address string (`Cache::forever("geocode:{$hash}")`)

**`GeocodeAddressJob`** — queued (`sync` in testing, `default` queue in production). Dispatched from `ClientResource` after create/update when any address field changed. Uses `Tenant::bypass()` since the job carries `tenant_id` context.

`addresses` table already has `lat DECIMAL(10,7)` and `lng DECIMAL(10,7)` columns from Sprint 0 migrations.

---

### S1.7 — Client List: Search + Filter

On `ClientResource` table:

**Search** (Filament `->searchable()`): `name`, `phone`, `email`

**Filters:**
- `client_type` — select filter (Osoba fizyczna / Firma)
- `property_type` — select filter built from vocabulary (mieszkanie / dom / biuro / lokal), filters on `custom_fields->property_type`

**Table columns:** name, phone, client_type badge, property_type (from custom_fields), area_m2, city (from address), created_at. Pagination 25/page.

---

## File Map

```
app/
  Filament/
    Pages/
      Register.php                          ← new
  Modules/
    Crm/
      Filament/
        Resources/
          ClientResource.php                ← new
          ClientResource/
            Pages/
              ListClients.php               ← new
              CreateClient.php              ← new
              EditClient.php                ← new
              ViewClient.php                ← new
            RelationManagers/
              NoteRelationManager.php       ← new
    Integrations/
      Geocoding/
        GeocodingService.php                ← new
        GeocodeAddressJob.php               ← new
      Gus/
        GusNipLookupService.php             ← new

database/migrations/
  ..._add_client_type_to_clients_table.php  ← new

tests/
  Feature/
    Auth/
      RegistrationTest.php                  ← new
    Crm/
      ClientResourceTest.php                ← new
    Integrations/
      GusNipLookupServiceTest.php           ← new
```

---

## Cross-Cutting Constraints

- All user-facing labels via `__('key')` — no hardcoded Polish strings in PHP/Blade
- `client_type` and `property_type` option labels in `lang/pl.json`
- `GeocodeAddressJob` must extend `TenantAwareJob` (Sprint 0 constraint)
- `GUS_API_KEY` and `GOOGLE_MAPS_API_KEY` added to `.env.example` with placeholder values
- Larastan level 6, Pint clean, existing 14 tests stay green

---

## Definition of Done

1. `/register` creates tenant + user + seeds cleaning preset; redirects to `/admin`
2. Client CRUD: generic fields + cleaning custom fields + encrypted access_keys
3. `client_type = company` conditionally shows NIP, REGON, GUS button
4. GUS button fills name + address + REGON (or shows warning on failure)
5. Notes timeline on client view page: append-only, deletable, newest-first
6. `GeocodeAddressJob` dispatched on client save (silent on placeholder key)
7. Table: search by name/phone/email, filter by client_type + property_type
8. `bin/test` green, `bin/stan` clean, `bin/pint --test` clean

---

## Deferred

- Dynamic preset field renderer (Option B) — revisit at Sprint 2 if second preset needed
- Branded PDF, photo attachments — Sprint 3+
- Production deploy (S0.2) — separate from this sprint
