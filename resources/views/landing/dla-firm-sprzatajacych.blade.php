@extends('layouts.public')

@section('head')
<title>Program dla firmy sprzątającej — zarządzanie zleceniami i wyceny AI | TBA</title>
<meta name="description" content="TBA to program do zarządzania zleceniami sprzątania. Wyceny w minutę, harmonogram, przypomnienia dla klientów. Działa z telefonu. 30 dni za darmo.">
<link rel="canonical" href="https://tbasystent.pl/dla-firm-sprzatajacych">
<meta property="og:title" content="Program dla firmy sprzątającej — TBA">
<meta property="og:description" content="Zarządzaj zleceniami sprzątania, wystawiaj wyceny metrażowe i planuj harmonogram tygodnia — wszystko z telefonu.">
<meta property="og:image" content="https://tbasystent.pl/og-image.png">
<meta property="og:url" content="https://tbasystent.pl/dla-firm-sprzatajacych">
<meta name="robots" content="index, follow">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "TBA — program dla firmy sprzątającej",
  "description": "Program do zarządzania zleceniami sprzątania. Wyceny AI, harmonogram, CRM dla firm sprzątających.",
  "url": "https://tbasystent.pl/dla-firm-sprzatajacych",
  "isPartOf": { "@id": "https://tbasystent.pl/#software" },
  "breadcrumb": {
    "@type": "BreadcrumbList",
    "itemListElement": [
      { "@type": "ListItem", "position": 1, "name": "Strona główna", "item": "https://tbasystent.pl/" },
      { "@type": "ListItem", "position": 2, "name": "Dla firm sprzątających", "item": "https://tbasystent.pl/dla-firm-sprzatajacych" }
    ]
  }
}
</script>
<style>
.lp-hero {
    padding: 80px 48px 64px;
    text-align: center;
    background:
        radial-gradient(ellipse 70% 50% at 50% 0%, rgba(74,222,128,0.08) 0%, transparent 70%),
        #0d0d14;
}
.lp-hero h1 { font-size: 52px; font-weight: 900; letter-spacing: -1.5px; line-height: 1.1; margin-bottom: 20px; }
.lp-hero h1 em { font-style: normal; color: #4ade80; }
.lp-hero-sub { font-size: 18px; color: rgba(255,255,255,0.55); max-width: 520px; margin: 0 auto 32px; line-height: 1.6; }
.lp-badge { display: inline-block; background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.25); border-radius: 20px; padding: 5px 16px; font-size: 12px; color: #4ade80; margin-bottom: 24px; letter-spacing: 0.5px; }
.lp-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.btn-primary { background: #4ade80; color: #0d1117; border-radius: 10px; padding: 14px 28px; font-size: 15px; font-weight: 800; text-decoration: none; display: inline-block; transition: background .2s; }
.btn-primary:hover { background: #22c55e; }
.btn-ghost { border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; padding: 14px 22px; font-size: 15px; color: rgba(255,255,255,0.7); text-decoration: none; transition: border-color .2s, color .2s; }
.btn-ghost:hover { border-color: rgba(255,255,255,0.5); color: #fff; }

.lp-problems { padding: 80px 48px; max-width: 1000px; margin: 0 auto; }
.lp-problems h2 { font-size: 36px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 40px; text-align: center; }
.lp-problems h2 em { font-style: normal; color: #4ade80; }
.problems-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.problem-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 24px; }
.problem-icon { font-size: 28px; margin-bottom: 12px; }
.problem-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 8px; color: rgba(255,255,255,0.85); }
.problem-card p { font-size: 13px; color: rgba(255,255,255,0.45); line-height: 1.65; }

.lp-features { padding: 80px 48px; background: rgba(255,255,255,0.01); }
.lp-features-inner { max-width: 1000px; margin: 0 auto; }
.lp-features h2 { font-size: 36px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 48px; text-align: center; }
.lp-features h2 em { font-style: normal; color: #4ade80; }
.lp-feature-row { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; margin-bottom: 64px; }
.lp-feature-row.reverse { direction: rtl; }
.lp-feature-row.reverse > * { direction: ltr; }
.feature-content h3 { font-size: 26px; font-weight: 800; margin-bottom: 12px; }
.feature-content h3 em { font-style: normal; color: #4ade80; }
.feature-content p { font-size: 15px; color: rgba(255,255,255,0.55); line-height: 1.7; margin-bottom: 16px; }
.feature-content ul { list-style: none; padding: 0; margin: 0; }
.feature-content ul li { font-size: 14px; color: rgba(255,255,255,0.65); padding: 4px 0 4px 20px; position: relative; }
.feature-content ul li::before { content: '✓'; position: absolute; left: 0; color: #4ade80; font-weight: 700; }
.feature-mockup { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 28px; }
.mockup-label { font-size: 10px; color: #4ade80; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 14px; }
.mockup-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 13px; }
.mockup-row:last-child { border-bottom: none; }
.mockup-status { font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 700; }
.status-green { background: rgba(74,222,128,0.1); color: #4ade80; }
.status-yellow { background: rgba(251,191,36,0.1); color: #fbbf24; }
.mockup-price { color: #4ade80; font-weight: 700; }
.mockup-ai { background: linear-gradient(135deg, rgba(74,222,128,0.06), rgba(139,92,246,0.04)); border: 1px solid rgba(74,222,128,0.15); border-radius: 10px; padding: 14px; margin-top: 14px; font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.55; }
.mockup-ai-label { font-size: 10px; color: #4ade80; letter-spacing: 0.5px; margin-bottom: 8px; }

.lp-cta { padding: 80px 48px; text-align: center; background: radial-gradient(ellipse 50% 60% at 50% 100%, rgba(74,222,128,0.06) 0%, transparent 70%); }
.lp-cta h2 { font-size: 44px; font-weight: 900; letter-spacing: -1px; margin-bottom: 16px; }
.lp-cta h2 em { font-style: normal; color: #4ade80; }
.lp-cta-sub { font-size: 16px; color: rgba(255,255,255,0.45); margin-bottom: 32px; }
.lp-cta-trust { margin-top: 16px; font-size: 12px; color: rgba(255,255,255,0.25); }

.breadcrumb { padding: 14px 48px 0; font-size: 13px; color: rgba(255,255,255,0.3); }
.breadcrumb a { color: rgba(255,255,255,0.3); text-decoration: none; }
.breadcrumb a:hover { color: rgba(255,255,255,0.6); }
.breadcrumb span { margin: 0 6px; }

@media (max-width: 768px) {
    .lp-hero { padding: 56px 20px 48px; }
    .lp-hero h1 { font-size: 34px; }
    .problems-grid { grid-template-columns: 1fr; }
    .lp-problems, .lp-features { padding: 56px 20px; }
    .lp-feature-row { grid-template-columns: 1fr; gap: 28px; }
    .lp-feature-row.reverse { direction: ltr; }
    .lp-cta { padding: 56px 20px; }
    .lp-cta h2 { font-size: 30px; }
    .breadcrumb { padding: 14px 20px 0; }
}
</style>
@endsection

@section('content')

<div class="breadcrumb">
    <a href="/">Strona główna</a>
    <span>›</span>
    Dla firm sprzątających
</div>

{{-- ═══ HERO ══════════════════════════════════════════════════ --}}
<section class="lp-hero">
    <div class="lp-badge">Dla firm sprzątających</div>

    <h1>Program dla <em>firmy sprzątającej</em><br>który pracuje z telefonu</h1>

    <p class="lp-hero-sub">
        Wyceny metrażowe w minutę, harmonogram zleceń bez Excela,
        przypomnienia dla klientów i pełna historia każdego mieszkania —
        wszystko w jednej aplikacji.
    </p>

    <div class="lp-actions">
        <a href="/admin/register" class="btn-primary">Zacznij 30 dni za darmo</a>
        <a href="/" class="btn-ghost">Poznaj wszystkie funkcje</a>
    </div>
    <p style="margin-top: 14px; font-size: 12px; color: rgba(255,255,255,0.25);">Bez karty kredytowej · Potem 50 zł/mies.</p>
</section>

{{-- ═══ PROBLEMY ═══════════════════════════════════════════════ --}}
<section class="lp-problems">
    <h2>Znasz to aż za dobrze?<br><em>TBA to rozwiązuje.</em></h2>

    <div class="problems-grid">
        <div class="problem-card">
            <div class="problem-icon">📱</div>
            <h3>Klient pyta o wycenę, a Ty jesteś na sprzątaniu</h3>
            <p>TBA przygotuje wycenę metrażową na podstawie Twoich poprzednich zleceń. Wyślij jednym kliknięciem — bez wychodzenia z kuchni klienta.</p>
        </div>
        <div class="problem-card">
            <div class="problem-icon">📅</div>
            <h3>Harmonogram w głowie lub na kartce</h3>
            <p>Wpisz zlecenie raz — TBA pilnuje terminu, przypomni klientowi dzień wcześniej i Tobie rano. Zero telefonów "czy pani dzisiaj przyjedzie".</p>
        </div>
        <div class="problem-card">
            <div class="problem-icon">📋</div>
            <h3>Nie pamiętasz co gdzie robiłaś rok temu</h3>
            <p>Historia każdego klienta i mieszkania w jednym miejscu. Co sprzątałaś, jaką stawkę miałaś, czy był jakiś problem — wszystko zapisane automatycznie.</p>
        </div>
    </div>
</section>

{{-- ═══ FUNKCJE ════════════════════════════════════════════════ --}}
<section class="lp-features">
    <div class="lp-features-inner">
        <h2>Jak TBA pomaga<br><em>firmie sprzątającej</em></h2>

        <div class="lp-feature-row">
            <div class="feature-content">
                <h3>Wyceny metrażowe<br><em>w 60 sekund</em></h3>
                <p>Powiedz TBA: "mieszkanie 80m², Warszawa Mokotów, gruntowne sprzątanie". AI proponuje cenę na podstawie Twoich poprzednich wycen dla podobnych mieszkań. Ty zatwierdzasz lub edytujesz — i wysyłasz do klienta.</p>
                <ul>
                    <li>Wyceny metrażowe i godzinowe</li>
                    <li>Historia cen dla każdego klienta</li>
                    <li>Wycena mailem jednym kliknięciem</li>
                    <li>Klient akceptuje online — bez maili tam-siam</li>
                </ul>
            </div>
            <div class="feature-mockup">
                <div class="mockup-label">✨ AI sugeruje wycenę</div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 12px;">
                    Klient: Anna Kowalska, 3 pokoje, 78m², Warszawa
                </div>
                <div class="mockup-ai">
                    <div class="mockup-ai-label">ASYSTENT AI</div>
                    Na podstawie 6 podobnych zleceń sugeruję:<br>
                    <strong style="color: #4ade80; font-size: 16px;">350–400 zł</strong><br>
                    <span style="font-size: 12px; color: rgba(255,255,255,0.4);">Twoja średnia dla 70-85m² = 4,5 zł/m²</span>
                </div>
                <div style="display: flex; gap: 8px; margin-top: 14px;">
                    <div style="background: #4ade80; color: #0d1117; border-radius: 6px; padding: 7px 16px; font-size: 12px; font-weight: 700;">Wyślij: 380 zł</div>
                    <div style="border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; padding: 7px 12px; font-size: 12px; color: rgba(255,255,255,0.5);">Zmień</div>
                </div>
            </div>
        </div>

        <div class="lp-feature-row reverse">
            <div class="feature-content">
                <h3>Harmonogram tygodnia<br><em>bez chaosu</em></h3>
                <p>Wszystkie zlecenia sprzątania w jednym widoku tygodniowym. Dodaj nowe zlecenie w 30 sekund — adres, godzina, typ sprzątania. TBA pilnuje harmonogramu i automatycznie przypomina klientom.</p>
                <ul>
                    <li>Widok tygodniowy wszystkich zleceń</li>
                    <li>Automatyczne SMS/e-mail do klienta dzień wcześniej</li>
                    <li>Przypomnienie dla Ciebie rano w dniu sprzątania</li>
                    <li>Oznaczaj zlecenia: wykonane, odwołane, do przerobienia</li>
                </ul>
            </div>
            <div class="feature-mockup">
                <div class="mockup-label">Harmonogram — ten tydzień</div>
                <div class="mockup-row">
                    <span style="color: rgba(255,255,255,0.7);">Pn 09:00 · Kowalska, 78m²</span>
                    <span class="mockup-status status-green">Wykonane</span>
                </div>
                <div class="mockup-row">
                    <span style="color: rgba(255,255,255,0.7);">Wt 10:30 · Nowak, 55m²</span>
                    <span class="mockup-status status-yellow">Dziś</span>
                </div>
                <div class="mockup-row">
                    <span style="color: rgba(255,255,255,0.7);">Śr 08:00 · Wiśniewska, 120m²</span>
                    <span style="font-size: 11px; color: rgba(255,255,255,0.3);">Jutro</span>
                </div>
                <div class="mockup-row">
                    <span style="color: rgba(255,255,255,0.7);">Pt 11:00 · Zając, biuro</span>
                    <span style="font-size: 11px; color: rgba(255,255,255,0.3);">Pt</span>
                </div>
            </div>
        </div>

        <div class="lp-feature-row">
            <div class="feature-content">
                <h3>Karta każdego klienta<br><em>zawsze pod ręką</em></h3>
                <p>Każdy klient ma swoją kartę: adres, kod do klatki, preferencje, historia zleceń, notatki głosowe po sprzątaniu. Nikt nie musi dzwonić z pytaniem "a co ja miałam u pani Nowak?". Wszystko jest.</p>
                <ul>
                    <li>Notatki głosowe — nagraj po zleceniu, AI zrobi transkrypt</li>
                    <li>Historia cen i poprzednich zleceń</li>
                    <li>Dane dostępowe (kod, klucze) zaszyfrowane</li>
                    <li>Rozmowy z AI asystentem o kliencie</li>
                </ul>
            </div>
            <div class="feature-mockup">
                <div class="mockup-label">Karta klienta</div>
                <div style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 4px;">Anna Kowalska</div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 14px;">ul. Mokotowska 12/5, Warszawa · od 2024-03</div>
                <div class="mockup-row">
                    <span style="color: rgba(255,255,255,0.5); font-size: 12px;">Ostatnie zlecenie</span>
                    <span style="font-size: 12px; color: rgba(255,255,255,0.8);">2026-06-10 · 380 zł</span>
                </div>
                <div class="mockup-row">
                    <span style="color: rgba(255,255,255,0.5); font-size: 12px;">Łącznie zleceń</span>
                    <span class="mockup-price">18 · 6 240 zł</span>
                </div>
                <div style="background: rgba(255,255,255,0.03); border-radius: 8px; padding: 10px; margin-top: 12px; font-size: 12px; color: rgba(255,255,255,0.45);">
                    📝 Notatka (10 cze): Pani prosi żeby nie używać chemii z chlorem w łazience. Klucze pod wycieraczką w piątek.
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ FINAL CTA ══════════════════════════════════════════════ --}}
<section class="lp-cta">
    <h2>Zacznij zarządzać firmą<br><em>sprzątającą jak pro.</em></h2>
    <p class="lp-cta-sub">
        Dołącz do firm sprzątających, które używają TBA do prowadzenia zleceń.
        Pierwsze 30 dni za darmo — bez karty kredytowej.
    </p>
    <a href="/admin/register" class="btn-primary" style="font-size: 16px; padding: 16px 36px;">
        Zacznij 30 dni za darmo
    </a>
    <p class="lp-cta-trust">30 dni bezpłatnie · Potem 50 zł/mies. · Anuluj kiedy chcesz</p>
</section>

@include('partials.public-footer')

@endsection
