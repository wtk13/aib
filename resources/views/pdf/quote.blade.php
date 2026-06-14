<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; background: #fff; }
  .page { padding: 32px 36px; }
  .header { display: flex; justify-content: space-between; margin-bottom: 28px; border-bottom: 2px solid #14b8a6; padding-bottom: 16px; }
  .firma-name { font-size: 16px; font-weight: bold; color: #14b8a6; }
  .quote-title { font-size: 20px; font-weight: bold; color: #111; margin-bottom: 4px; }
  .quote-meta { font-size: 10px; color: #666; }
  .parties { display: flex; gap: 40px; margin-bottom: 24px; }
  .party-block { flex: 1; }
  .party-label { font-size: 9px; text-transform: uppercase; color: #888; letter-spacing: 0.5px; margin-bottom: 4px; }
  .party-name { font-size: 12px; font-weight: bold; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  thead tr { background: #f0fdf4; }
  th { padding: 8px 6px; text-align: left; font-size: 10px; border-bottom: 1px solid #e2e8f0; }
  td { padding: 7px 6px; font-size: 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
  .text-right { text-align: right; }
  .totals { display: flex; justify-content: flex-end; margin-top: 8px; }
  .totals-box { width: 220px; }
  .totals-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 11px; }
  .totals-row.total-row { border-top: 2px solid #14b8a6; padding-top: 6px; font-size: 13px; font-weight: bold; color: #14b8a6; }
  .footer { margin-top: 40px; font-size: 9px; color: #999; text-align: center; }
  .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; }
  .status-draft { background: #f3f4f6; color: #6b7280; }
  .status-sent { background: #fef3c7; color: #92400e; }
  .status-accepted { background: #d1fae5; color: #065f46; }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <div>
      <div class="firma-name">TBA</div>
      <div style="font-size:10px;color:#666;margin-top:4px">Twój Biznes Asystent</div>
      @if($tenant?->originAddress)
        <div style="font-size:10px;color:#555;margin-top:2px">
          {{ $tenant->originAddress->line1 }}, {{ $tenant->originAddress->city }}
        </div>
      @endif
    </div>
    <div style="text-align:right">
      <div class="quote-title">WYCENA {{ $quote->number }}</div>
      <div class="quote-meta">Data: {{ $quote->issued_at?->format('d.m.Y') }}</div>
      @if($quote->valid_until)
        <div class="quote-meta">Ważna do: {{ $quote->valid_until->format('d.m.Y') }}</div>
      @endif
    </div>
  </div>

  <div class="parties">
    <div class="party-block">
      <div class="party-label">Wystawca</div>
      <div class="party-name">{{ $tenantName }}</div>
    </div>
    <div class="party-block">
      <div class="party-label">Klient</div>
      <div class="party-name">{{ $quote->client?->name }}</div>
      @if($quote->client?->email)
        <div style="font-size:10px;color:#555">{{ $quote->client->email }}</div>
      @endif
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:40%">Opis</th>
        <th class="text-right">J.m.</th>
        <th class="text-right">Ilość</th>
        <th class="text-right">Cena</th>
        <th class="text-right">Rabat</th>
        <th class="text-right">VAT</th>
        <th class="text-right">Wartość</th>
      </tr>
    </thead>
    <tbody>
      @foreach($quote->items as $item)
      <tr>
        <td>{{ $item->description }}</td>
        <td class="text-right">{{ $unitLabels[$item->unit] ?? $item->unit }}</td>
        <td class="text-right">{{ number_format((float)$item->quantity, 2, ',', '') }}</td>
        <td class="text-right">{{ number_format((float)$item->rate, 2, ',', ' ') }} zł</td>
        <td class="text-right">{{ $item->discount_pct > 0 ? $item->discount_pct.'%' : '—' }}</td>
        <td class="text-right">{{ $item->vat_pct }}%</td>
        <td class="text-right">{{ number_format((float)$item->line_total, 2, ',', ' ') }} zł</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="totals">
    <div class="totals-box">
      <div class="totals-row">
        <span>Netto</span>
        <span>{{ number_format((float)$quote->subtotal, 2, ',', ' ') }} zł</span>
      </div>
      <div class="totals-row">
        <span>VAT {{ $quote->vat_rate }}%</span>
        <span>{{ number_format((float)($quote->total - $quote->subtotal), 2, ',', ' ') }} zł</span>
      </div>
      <div class="totals-row total-row">
        <span>RAZEM</span>
        <span>{{ number_format((float)$quote->total, 2, ',', ' ') }} zł</span>
      </div>
    </div>
  </div>

  @if($quote->internal_note)
    <div style="margin-top:20px;padding:10px;background:#f8fafc;border-left:3px solid #14b8a6;font-size:10px">
      {{ $quote->internal_note }}
    </div>
  @endif

  <div class="footer">
    Wycena wygenerowana przez TBA · tbasystent.pl · {{ now()->format('d.m.Y H:i') }}
  </div>
</div>
</body>
</html>
