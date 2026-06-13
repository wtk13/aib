<x-filament-panels::layout.base :livewire="$livewire">
    <div class="aib-auth-simple" style="min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;background:white;padding:24px 16px;">
        <div style="width:100%;max-width:400px;">

            {{-- Logo row --}}
            <a href="{{ url('/') }}" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;margin-bottom:24px;">
                <div style="width:28px;height:28px;background:#0d9488;border-radius:7px;display:flex;align-items:center;justify-content:center;color:white;font-size:13px;font-weight:800;line-height:1;flex-shrink:0;">✦</div>
                <span style="font-size:15px;font-weight:700;color:#0f172a;letter-spacing:-0.02em;">{{ filament()->getBrandName() }}</span>
            </a>

            {{-- Card --}}
            <div style="border:1px solid #e2e8f0;border-radius:12px;padding:28px;background:white;">
                {{ $slot }}
            </div>

            {{-- Footer tagline --}}
            <p style="margin-top:20px;font-size:11px;color:#94a3b8;text-align:center;line-height:1.6;">
                {{ __('auth.layout.tagline') }}
            </p>

        </div>
    </div>
</x-filament-panels::layout.base>
