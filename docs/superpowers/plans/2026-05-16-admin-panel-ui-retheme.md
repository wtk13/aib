# Admin Panel UI Retheme Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Retheme the Filament PHP admin panel with a dark teal sidebar, Inter font, teal accent cards, and a split-panel login page — using only official Filament extension points, no vendor file modifications.

**Architecture:** All styling goes through two extension points: (1) `AppPanelProvider.php` for Filament's built-in config API (color, font, brand, sidebar), and (2) a custom Vite-compiled CSS file loaded via `->viteTheme()` for structural overrides (sidebar background, card accents). The login page is a custom Livewire page class overriding only the view.

**Tech Stack:** Filament v3, Laravel Vite, Inter (Google Fonts via BunnyFontProvider default), Tailwind (app build for login view classes in theme CSS)

---

## File Map

| Action | Path | Responsibility |
|---|---|---|
| Modify | `app/Providers/Filament/AppPanelProvider.php` | Color, font, brandLogo, collapsible sidebar, viteTheme, custom login |
| Create | `resources/css/filament/app/theme.css` | Sidebar bg override, nav item colors, card top-border accents, auth layout classes |
| Modify | `vite.config.js` | Add theme.css to Vite inputs so it compiles |
| Create | `app/Filament/Pages/Auth/Login.php` | Custom login page — extends base, overrides view only |
| Create | `resources/views/filament/pages/auth/login.blade.php` | Split-panel login layout (brand left, form right) |
| Modify | `tests/Feature/Auth/FilamentAuthTest.php` | Point Livewire test at new Login class |

---

## Task 1: Panel base config — color, font, brand, collapsible sidebar

**Files:**
- Modify: `app/Providers/Filament/AppPanelProvider.php`

- [ ] **Step 1: Update AppPanelProvider**

Replace the `panel()` method body in `app/Providers/Filament/AppPanelProvider.php`. Add `use Illuminate\Support\HtmlString;` to the imports. The full updated method:

```php
use Filament\FontProviders\GoogleFontProvider;
use Illuminate\Support\HtmlString;
```

Replace the existing `->colors(['primary' => Color::Blue])` line and `->login()` call with:

```php
->colors(['primary' => Color::hex('#14b8a6')])
->font('Inter', provider: GoogleFontProvider::class)
->brandName('Wyceny')
->brandLogo(new HtmlString(
    '<div style="display:flex;align-items:center;gap:8px;">'
    . '<div style="display:flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:6px;background:white;color:#0d9488;font-weight:700;font-size:14px;line-height:1;">✦</div>'
    . '<span style="font-size:14px;font-weight:700;color:white;letter-spacing:0.02em;">Wyceny</span>'
    . '</div>'
))
->brandLogoHeight('2rem')
->sidebarCollapsibleOnDesktop()
->login()
```

Full updated `AppPanelProvider.php` after the change:

```php
<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\TenantSettingsPage;
use App\Http\Middleware\EnforceNoindex;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Scheduling\Filament\Resources\JobResource;
use App\Modules\Tenancy\Middleware\ResolveTenantFromSubdomain;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('/admin')
            ->login()
            ->registration(Register::class)
            ->colors(['primary' => Color::hex('#14b8a6')])
            ->font('Inter', provider: GoogleFontProvider::class)
            ->brandName('Wyceny')
            ->brandLogo(new HtmlString(
                '<div style="display:flex;align-items:center;gap:8px;">'
                . '<div style="display:flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:6px;background:white;color:#0d9488;font-weight:700;font-size:14px;line-height:1;">✦</div>'
                . '<span style="font-size:14px;font-weight:700;color:white;letter-spacing:0.02em;">Wyceny</span>'
                . '</div>'
            ))
            ->brandLogoHeight('2rem')
            ->sidebarCollapsibleOnDesktop()
            ->pages([Dashboard::class, TenantSettingsPage::class])
            ->resources([
                ClientResource::class,
                JobResource::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                EnforceNoindex::class,
                ResolveTenantFromSubdomain::class,
                SetLocaleMiddleware::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
```

- [ ] **Step 2: Verify no syntax errors**

```bash
php artisan config:clear && php artisan route:list --path=admin | head -10
```

Expected: routes listed without exception.

- [ ] **Step 3: Commit**

```bash
git add app/Providers/Filament/AppPanelProvider.php
git commit -m "feat(ui): teal color, Inter font, brand logo, collapsible sidebar"
```

---

## Task 2: Custom theme CSS — sidebar dark bg + card accents

**Files:**
- Create: `resources/css/filament/app/theme.css`
- Modify: `vite.config.js`
- Modify: `app/Providers/Filament/AppPanelProvider.php` (add `->viteTheme()`)

- [ ] **Step 1: Create directory and theme CSS file**

Create `resources/css/filament/app/theme.css`:

```css
/* =============================================
   Wyceny — Filament Panel Theme
   Teal structural retheme
   ============================================= */

/* ── Sidebar background ── */
.fi-sidebar,
.fi-main-sidebar .fi-sidebar,
.fi-sidebar-header {
    background-color: #0d9488 !important;
}

.fi-sidebar-header {
    --tw-ring-color: transparent !important;
    box-shadow: none !important;
    border-bottom-color: rgba(255, 255, 255, 0.15) !important;
}

/* ── Sidebar nav items — default ── */
.fi-sidebar-item-button {
    color: rgba(255, 255, 255, 0.75) !important;
}

.fi-sidebar-item-button:hover {
    background-color: rgba(255, 255, 255, 0.10) !important;
    color: rgba(255, 255, 255, 1) !important;
}

/* ── Sidebar nav items — active ── */
.fi-sidebar-item.fi-active .fi-sidebar-item-button {
    background-color: rgba(255, 255, 255, 0.18) !important;
    color: rgba(255, 255, 255, 1) !important;
}

/* ── Sidebar icons ── */
.fi-sidebar-item-button svg {
    color: rgba(255, 255, 255, 0.75) !important;
}

.fi-sidebar-item.fi-active .fi-sidebar-item-button svg {
    color: rgba(255, 255, 255, 1) !important;
}

/* ── Sidebar group labels ── */
.fi-sidebar-group-label {
    color: rgba(255, 255, 255, 0.45) !important;
}

/* ── Brand / logo area ── */
.fi-brand-name {
    color: rgba(255, 255, 255, 1) !important;
}

/* ── Collapse / expand buttons ── */
.fi-sidebar-collapse-btn,
.fi-sidebar-expand-btn {
    color: rgba(255, 255, 255, 0.75) !important;
}

.fi-sidebar-collapse-btn:hover,
.fi-sidebar-expand-btn:hover {
    background-color: rgba(255, 255, 255, 0.10) !important;
    color: rgba(255, 255, 255, 1) !important;
}

/* ── Stats widget cards — teal top accent ── */
.fi-wi-stats-overview-stat {
    border-top: 3px solid #14b8a6 !important;
    border-radius: 8px !important;
}

/* ── Table & chart widget wrappers — teal top accent ── */
.fi-wi-table,
.fi-wi-chart {
    border-top: 3px solid #14b8a6;
    border-radius: 8px;
    overflow: hidden;
}

/* ── Auth split layout — responsive classes ── */
/* Brand panel: hidden on mobile, flex on lg+ */
.wyceny-auth-brand-panel {
    display: none;
    width: 42%;
    background-color: #0d9488;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 48px;
    position: relative;
    overflow: hidden;
}

@media (min-width: 1024px) {
    .wyceny-auth-brand-panel {
        display: flex;
    }
}

/* Mobile brand: shown only below lg */
.wyceny-auth-mobile-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 28px;
}

@media (min-width: 1024px) {
    .wyceny-auth-mobile-brand {
        display: none;
    }
}
```

- [ ] **Step 2: Add theme.css to vite.config.js inputs**

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/app/theme.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
```

- [ ] **Step 3: Register viteTheme in AppPanelProvider**

Add `->viteTheme('resources/css/filament/app/theme.css')` to the panel chain in `app/Providers/Filament/AppPanelProvider.php`, right after `->brandLogoHeight('2rem')`:

```php
->brandLogoHeight('2rem')
->viteTheme('resources/css/filament/app/theme.css')
->sidebarCollapsibleOnDesktop()
```

- [ ] **Step 4: Build assets and verify no compile errors**

```bash
npm run build 2>&1 | tail -20
```

Expected: build succeeds, output includes `resources/css/filament/app/theme.css → public/build/assets/theme-*.css`.

- [ ] **Step 5: Commit**

```bash
git add resources/css/filament/app/theme.css vite.config.js app/Providers/Filament/AppPanelProvider.php
git commit -m "feat(ui): filament theme CSS — teal sidebar, card accents, auth layout classes"
```

---

## Task 3: Custom split-panel login page

**Files:**
- Create: `app/Filament/Pages/Auth/Login.php`
- Create: `resources/views/filament/pages/auth/login.blade.php`
- Modify: `app/Providers/Filament/AppPanelProvider.php`
- Modify: `tests/Feature/Auth/FilamentAuthTest.php`

- [ ] **Step 1: Write the failing smoke test**

In `tests/Feature/Auth/FilamentAuthTest.php`, add this test at the bottom (before the closing `}`):

```php
it('login page renders with split layout brand panel', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee('wyceny-auth-brand-panel', escape: false);
    $response->assertSee('Wyceny');
    $response->assertSee('CRM dla małych firm usługowych');
});
```

- [ ] **Step 2: Run the test to verify it fails**

```bash
php artisan test tests/Feature/Auth/FilamentAuthTest.php --filter="login page renders with split layout"
```

Expected: FAILED — `wyceny-auth-brand-panel` not found in the default Filament login view.

- [ ] **Step 3: Create the custom Login page class**

Create `app/Filament/Pages/Auth/Login.php`:

```php
<?php

namespace App\Filament\Pages\Auth;

class Login extends \Filament\Pages\Auth\Login
{
    protected static string $view = 'filament.pages.auth.login';
}
```

- [ ] **Step 4: Create the login Blade view**

Create `resources/views/filament/pages/auth/login.blade.php`:

```blade
<x-filament-panels::layout.base>
    <div style="display:flex;min-height:100vh;">

        {{-- Brand panel: hidden on mobile, visible on lg+ via theme CSS class --}}
        <div class="wyceny-auth-brand-panel">
            {{-- Decorative circles --}}
            <div style="position:absolute;top:-80px;right:-80px;width:240px;height:240px;background:rgba(255,255,255,0.06);border-radius:50%;pointer-events:none;"></div>
            <div style="position:absolute;bottom:-60px;left:-60px;width:200px;height:200px;background:rgba(255,255,255,0.06);border-radius:50%;pointer-events:none;"></div>

            {{-- Logo mark --}}
            <div style="position:relative;z-index:1;width:56px;height:56px;background:white;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;box-shadow:0 8px 24px rgba(0,0,0,0.15);margin-bottom:20px;">
                ✦
            </div>

            {{-- Brand name --}}
            <div style="position:relative;z-index:1;font-size:28px;font-weight:700;color:white;letter-spacing:0.02em;margin-bottom:10px;">
                Wyceny
            </div>

            {{-- Tagline --}}
            <div style="position:relative;z-index:1;font-size:13px;color:rgba(255,255,255,0.65);text-align:center;line-height:1.5;max-width:200px;margin-bottom:32px;">
                CRM dla małych firm usługowych
            </div>

            {{-- Feature bullets --}}
            <div style="position:relative;z-index:1;display:flex;flex-direction:column;gap:10px;width:100%;">
                @foreach([
                    ['icon' => '👤', 'text' => 'Klienci i historia zleceń'],
                    ['icon' => '📋', 'text' => 'Grafik i planowanie wizyt'],
                    ['icon' => '✦', 'text' => 'Wyceny wspomagane AI'],
                ] as $feature)
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,0.75);">
                        <div style="width:24px;height:24px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;">
                            {{ $feature['icon'] }}
                        </div>
                        {{ $feature['text'] }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Form panel --}}
        <div style="flex:1;display:flex;align-items:center;justify-content:center;background-color:#f8fafc;padding:32px;">
            <div style="width:100%;max-width:360px;">

                {{-- Mobile brand mark (theme CSS hides this on lg+) --}}
                <div class="wyceny-auth-mobile-brand">
                    <div style="width:32px;height:32px;background:#0d9488;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;">✦</div>
                    <span style="font-size:18px;font-weight:700;color:#0f172a;">Wyceny</span>
                </div>

                <h1 style="font-size:20px;font-weight:700;color:#0f172a;margin-bottom:4px;">
                    {{ __('filament-panels::pages/auth/login.heading') }}
                </h1>
                <p style="font-size:13px;color:#64748b;margin-bottom:28px;">
                    Wróć do swojego panelu
                </p>

                <x-filament-panels::form id="form" wire:submit="authenticate">
                    {{ $this->form }}

                    <x-filament-panels::form.actions
                        :actions="$this->getCachedFormActions()"
                        :full-width="$this->hasFullWidthFormActions()"
                    />
                </x-filament-panels::form>

                @if (filament()->hasRegistration())
                    <p style="text-align:center;margin-top:20px;font-size:13px;color:#64748b;">
                        Nie masz konta?
                        <a href="{{ filament()->getRegistrationUrl() }}" style="color:#0d9488;font-weight:600;text-decoration:none;">
                            Zarejestruj się
                        </a>
                    </p>
                @endif

            </div>
        </div>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::layout.base>
```

- [ ] **Step 5: Register the custom login class in AppPanelProvider**

In `app/Providers/Filament/AppPanelProvider.php`, add the import and update `->login()`:

```php
use App\Filament\Pages\Auth\Login;
```

Change `->login()` to:

```php
->login(Login::class)
```

- [ ] **Step 6: Update the existing Livewire login test to use the new class**

In `tests/Feature/Auth/FilamentAuthTest.php`, change:

```php
use Filament\Pages\Auth\Login;
```

to:

```php
use App\Filament\Pages\Auth\Login;
```

- [ ] **Step 7: Run all auth tests**

```bash
php artisan test tests/Feature/Auth/FilamentAuthTest.php
```

Expected: all tests PASS, including the new split layout smoke test.

- [ ] **Step 8: Commit**

```bash
git add app/Filament/Pages/Auth/Login.php \
        resources/views/filament/pages/auth/login.blade.php \
        app/Providers/Filament/AppPanelProvider.php \
        tests/Feature/Auth/FilamentAuthTest.php
git commit -m "feat(ui): custom split-panel login page with teal brand panel"
```

---

## Task 4: Build, smoke test, verify visually

**Files:** none created/modified — verification only

- [ ] **Step 1: Full asset build**

```bash
npm run build
```

Expected: exits 0, no warnings about missing files.

- [ ] **Step 2: Clear all caches**

```bash
php artisan optimize:clear
```

- [ ] **Step 3: Run full test suite to catch regressions**

```bash
php artisan test
```

Expected: all existing tests pass.

- [ ] **Step 4: Visual verification**

Start the dev server and open these URLs in a browser:

```bash
php artisan serve
```

Check:
- `http://localhost:8000/admin/login` — split layout visible: teal brand panel left, form right
- `http://localhost:8000/admin/login` on narrow viewport (< 1024px) — brand panel hidden, mobile brand mark shown
- After logging in, `http://localhost:8000/admin` — teal sidebar, "Wyceny" brand, teal card accents on dashboard widgets
- Sidebar collapse button works on desktop

- [ ] **Step 5: Final commit if any visual tweaks were needed**

```bash
git add -p
git commit -m "fix(ui): visual polish after browser verification"
```

---

## Out of Scope (deferred)

- **"Good morning" greeting on mobile dashboard** (spec §8) — requires a Livewire topbar widget, which is a functional change outside this plan's scope ("No functional changes to resources, widgets, or data models"). Implement as a follow-up dashboard widget task.
- Dark mode, custom illustrations, animated widgets, custom empty states (spec §10).
