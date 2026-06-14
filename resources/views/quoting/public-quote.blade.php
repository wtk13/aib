<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wycena {{ $quote->number }}</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; color: #111; margin: 0; padding: 0; }
  .container { max-width: 680px; margin: 0 auto; padding: 24px 16px; }
  .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 28px; margin-bottom: 16px; }
  .brand { color: #14b8a6; font-weight: 700; font-size: 18px; }
  h1 { font-size: 22px; font-weight: 800; margin: 8px 0 4px; }
  .meta { font-size: 13px; color: #6b7280; }
  table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
  th { padding: 8px; text-align: right; background: #f0fdf4; font-size: 11px; text-transform: uppercase; color: #6b7280; }
  th:first-child { text-align: left; }
  td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
  td:not(:first-child) { text-align: right; }
  .total-row { font-size: 16px; font-weight: 800; color: #14b8a6; }
  .accept-btn { display: block; width: 100%; padding: 14px; background: #14b8a6; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; text-align: center; margin-top: 16px; }
  .accepted-banner { background: #d1fae5; color: #065f46; padding: 16px; border-radius: 8px; text-align: center; font-weight: 600; }
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <div class="brand">T.B.A</div>
    <h1>Wycena {{ $quote->number }}</h1>
    <div class="meta">Data: {{ $quote->issued_at?->format('d.m.Y') }}
      @if($quote->valid_until) · Ważna do: {{ $quote->valid_until->format('d.m.Y') }} @endif
    </div>
    <div style="margin-top:12px;font-size:14px">
      <strong>Klient:</strong> {{ $quote->client?->name }}
    </div>

    <table>
      <thead><tr>
        <th>Opis</th><th>Ilość</th><th>Cena</th><th>Wartość</th>
      </tr></thead>
      <tbody>
        @foreach($quote->items as $item)
        <tr>
          <td>{{ $item->description }}</td>
          <td>{{ number_format((float)$item->quantity, 2, ',', '') }} {{ $unitLabels[$item->unit] ?? $item->unit }}</td>
          <td>{{ number_format((float)$item->rate, 2, ',', ' ') }} zł</td>
          <td>{{ number_format((float)$item->line_total, 2, ',', ' ') }} zł</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right;font-weight:600;padding-top:12px">Netto:</td>
          <td style="text-align:right;padding-top:12px">{{ number_format((float)$quote->subtotal, 2, ',', ' ') }} zł</td>
        </tr>
        <tr>
          <td colspan="3" style="text-align:right;font-weight:600">VAT {{ $quote->vat_rate }}%:</td>
          <td style="text-align:right">{{ number_format((float)($quote->total - $quote->subtotal), 2, ',', ' ') }} zł</td>
        </tr>
        <tr class="total-row">
          <td colspan="3" style="text-align:right;padding-top:8px;border-top:2px solid #14b8a6">RAZEM:</td>
          <td style="text-align:right;padding-top:8px;border-top:2px solid #14b8a6">{{ number_format((float)$quote->total, 2, ',', ' ') }} zł</td>
        </tr>
      </tfoot>
    </table>
  </div>

  @if(session('accepted') || $shareToken->accepted_at)
    <div class="accepted-banner">Wycena przyjęta. Dziękujemy!</div>
  @elseif($quote->status === 'sent')
    <div class="card" style="text-align:center">
      <p style="font-size:14px;color:#6b7280;margin-bottom:12px">Czy akceptujesz tę wycenę?</p>
      <form method="POST" action="{{ route('quote.public.accept', $token) }}">
        @csrf
        <button class="accept-btn" type="submit">Akceptuję wycenę</button>
      </form>
    </div>
  @else
    <div style="text-align:center;font-size:13px;color:#6b7280;padding:8px">
      Status wyceny: {{ __('quote.status.' . $quote->status) }}
    </div>
  @endif
</div>
</body>
</html>
