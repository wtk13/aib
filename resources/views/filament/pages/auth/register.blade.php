<div>
    <div style="margin-bottom:20px;">
        <h2 style="font-size:17px;font-weight:700;color:#0f172a;margin:0 0 4px 0;">{{ $this->getHeading() }}</h2>
        @if (filament()->hasLogin())
            <p style="font-size:12px;color:#64748b;margin:0;">
                {{ __('filament-panels::pages/auth/register.actions.login.before') }}
                {{ $this->loginAction }}
            </p>
        @endif
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="register">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

    @if (! $this instanceof \Filament\Tables\Contracts\HasTable)
        <x-filament-actions::modals />
    @endif
</div>
