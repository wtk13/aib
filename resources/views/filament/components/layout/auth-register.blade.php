<x-filament-panels::layout.base :livewire="$livewire">
    <div style="min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;background-color:#f8fafc;padding:24px;">

        {{-- Brand mark --}}
        <a href="/" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;margin-bottom:32px;">
            <div style="width:36px;height:36px;background:#0d9488;border-radius:9px;display:flex;align-items:center;justify-content:center;color:white;font-size:18px;font-weight:700;">✦</div>
            <span style="font-size:20px;font-weight:700;color:#0f172a;letter-spacing:-0.01em;">TBA</span>
        </a>

        {{-- Form card --}}
        <div style="width:100%;max-width:520px;">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        <p style="margin-top:24px;font-size:12px;color:#94a3b8;">
            Bezpłatnie przez beta · Dane w Polsce (Hetzner) · Bez karty
        </p>

    </div>
</x-filament-panels::layout.base>
