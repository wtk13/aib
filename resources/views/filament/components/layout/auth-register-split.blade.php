<x-filament-panels::layout.base :livewire="$livewire">
    <div style="display:flex;min-height:100vh;">

        {{-- Brand panel --}}
        <div class="aib-auth-brand-panel">
            <div style="position:absolute;top:-80px;right:-80px;width:240px;height:240px;background:rgba(255,255,255,0.06);border-radius:50%;pointer-events:none;"></div>
            <div style="position:absolute;bottom:-60px;left:-60px;width:200px;height:200px;background:rgba(255,255,255,0.06);border-radius:50%;pointer-events:none;"></div>

            <div style="position:relative;z-index:1;width:56px;height:56px;background:white;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;box-shadow:0 8px 24px rgba(0,0,0,0.15);margin-bottom:20px;">
                ✦
            </div>

            <div style="position:relative;z-index:1;font-size:28px;font-weight:700;color:white;letter-spacing:0.02em;margin-bottom:10px;">
                {{ filament()->getBrandName() }}
            </div>

            <div style="position:relative;z-index:1;font-size:13px;color:rgba(255,255,255,0.65);text-align:center;line-height:1.5;max-width:200px;margin-bottom:32px;">
                Bezpłatnie przez beta.<br>Bez karty. Dane w Polsce.
            </div>

            <div style="position:relative;z-index:1;display:flex;flex-direction:column;gap:10px;width:100%;">
                @foreach([
                    ['icon' => '⚡', 'text' => 'Gotowy w 2 minuty'],
                    ['icon' => '👤', 'text' => 'Klienci i historia zleceń'],
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
            <div style="width:100%;max-width:400px;">

                <div class="aib-auth-mobile-brand">
                    <div style="width:32px;height:32px;background:#0d9488;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;">✦</div>
                    <span style="font-size:18px;font-weight:700;color:#0f172a;">{{ filament()->getBrandName() }}</span>
                </div>

                {{ $slot }}

            </div>
        </div>

    </div>
</x-filament-panels::layout.base>
