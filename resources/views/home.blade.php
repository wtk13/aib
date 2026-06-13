@extends('layouts.public')

@section('head')
<title>TBA — Asystent dla firm usługowych | tbasystent.pl</title>
<meta name="description" content="AI asystent dla małych firm usługowych — wyceny, maile, plan dnia. Bezpłatnie przez beta 2026.">
<link rel="canonical" href="https://tbasystent.pl/">
<meta property="og:type" content="website">
<meta property="og:title" content="TBA — Asystent dla firm usługowych">
<meta property="og:description" content="AI asystent dla małych firm usługowych. Wyceny, maile, plan dnia — wszystko z telefonu.">
<meta property="og:url" content="https://tbasystent.pl/">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="TBA — Asystent dla firm usługowych">
<meta name="twitter:description" content="AI asystent dla małych firm usługowych. Wyceny, maile, plan dnia — wszystko z telefonu.">
<meta name="robots" content="index, follow">
<style>
/* ─── HERO ─────────────────────────────────────────────── */
.hero {
    padding: 96px 48px 80px;
    background:
        radial-gradient(ellipse 80% 60% at 50% 0%, rgba(74,222,128,0.08) 0%, transparent 70%),
        radial-gradient(ellipse 50% 40% at 80% 50%, rgba(45,27,78,0.3) 0%, transparent 60%),
        #0d0d14;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.25);
    border-radius: 20px; padding: 5px 14px;
    font-size: 12px; color: #4ade80; margin-bottom: 28px; letter-spacing: 0.5px;
}
.hero-badge-dot { width: 6px; height: 6px; background: #4ade80; border-radius: 50%; }
.hero h1 {
    font-size: 62px; font-weight: 900; line-height: 1.05; letter-spacing: -2px;
    margin-bottom: 22px; max-width: 720px; margin-left: auto; margin-right: auto;
}
.hero h1 em { font-style: normal; color: #4ade80; }
.hero-sub {
    color: rgba(255,255,255,0.55); font-size: 18px; line-height: 1.65;
    max-width: 500px; margin: 0 auto 36px;
}
.hero-actions { display: flex; gap: 12px; justify-content: center; align-items: center; flex-wrap: wrap; }
.btn-primary {
    background: #4ade80; color: #0d1117; border-radius: 10px;
    padding: 14px 28px; font-size: 15px; font-weight: 800; text-decoration: none;
    display: inline-block; transition: background .2s;
}
.btn-primary:hover { background: #22c55e; }
.btn-secondary {
    border: 1px solid rgba(255,255,255,0.2); border-radius: 10px;
    padding: 14px 22px; font-size: 15px; color: rgba(255,255,255,0.7);
    text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    transition: border-color .2s, color .2s;
}
.btn-secondary:hover { border-color: rgba(255,255,255,0.5); color: #fff; }
.hero-trust { margin-top: 14px; font-size: 12px; color: rgba(255,255,255,0.3); }

/* ─── APP MOCKUP ────────────────────────────────────────── */
.hero-screen {
    margin: 56px auto 0; max-width: 760px;
    background: #161622; border-radius: 14px; overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 40px 120px rgba(0,0,0,0.6), 0 0 0 1px rgba(74,222,128,0.06);
}
.screen-topbar {
    background: #1a1a2e; padding: 10px 14px;
    display: flex; align-items: center; gap: 6px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.screen-dot { width: 10px; height: 10px; border-radius: 50%; }
.screen-url { flex: 1; text-align: center; font-size: 11px; color: rgba(255,255,255,0.2); }
.screen-body { display: grid; grid-template-columns: 200px 1fr; min-height: 280px; }
.screen-sidebar {
    background: #13131f; border-right: 1px solid rgba(255,255,255,0.06); padding: 16px 12px;
}
.screen-sidebar-label { font-size: 10px; color: rgba(255,255,255,0.25); letter-spacing: 1px; margin-bottom: 12px; }
.screen-nav-item { padding: 8px 10px; border-radius: 6px; font-size: 11px; color: rgba(255,255,255,0.4); margin-bottom: 2px; }
.screen-nav-item.active { background: rgba(74,222,128,0.1); color: #4ade80; }
.screen-main { padding: 20px; }
.screen-greeting { font-size: 13px; color: rgba(255,255,255,0.5); margin-bottom: 14px; }
.screen-greeting strong { color: #fff; }
.ai-suggestion {
    background: linear-gradient(135deg, rgba(74,222,128,0.08), rgba(139,92,246,0.06));
    border: 1px solid rgba(74,222,128,0.2); border-radius: 10px; padding: 14px; margin-bottom: 14px;
}
.ai-label { font-size: 10px; color: #4ade80; letter-spacing: 0.5px; margin-bottom: 6px; }
.ai-text { font-size: 12px; color: rgba(255,255,255,0.8); line-height: 1.5; }
.ai-actions { margin-top: 10px; display: flex; gap: 8px; }
.ai-btn { background: #4ade80; color: #0d1117; border-radius: 6px; padding: 5px 12px; font-size: 10px; font-weight: 700; }
.ai-btn-ghost { border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; padding: 5px 10px; font-size: 10px; color: rgba(255,255,255,0.5); }
.stats-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
.stat-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 10px; }
.stat-val { font-size: 18px; font-weight: 800; }
.stat-label { font-size: 10px; color: rgba(255,255,255,0.35); margin-top: 2px; }

/* ─── SOCIAL PROOF STRIP ────────────────────────────────── */
.proof-strip {
    padding: 40px 48px; border-top: 1px solid rgba(255,255,255,0.06); text-align: center;
}
.proof-label { font-size: 12px; color: rgba(255,255,255,0.3); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
.proof-pills { display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap; }
.proof-pill {
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.25);
}

/* ─── FEATURES ──────────────────────────────────────────── */
.features { padding: 96px 48px; }
.section-tag { font-size: 12px; color: #4ade80; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 14px; }
.section-title { font-size: 40px; font-weight: 800; letter-spacing: -1px; line-height: 1.15; margin-bottom: 14px; }
.section-title em { font-style: normal; color: #4ade80; }
.section-sub { font-size: 16px; color: rgba(255,255,255,0.5); max-width: 440px; line-height: 1.6; margin-bottom: 52px; }
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.feature-card {
    background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07);
    border-radius: 14px; padding: 24px; transition: border-color .2s, background .2s;
}
.feature-card:hover { background: rgba(74,222,128,0.04); border-color: rgba(74,222,128,0.2); }
.feature-icon { font-size: 24px; margin-bottom: 14px; }
.feature-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
.feature-card p { font-size: 13px; color: rgba(255,255,255,0.45); line-height: 1.6; }

/* ─── TESTIMONIALS ──────────────────────────────────────── */
.testimonials {
    padding: 80px 48px; background: rgba(255,255,255,0.015);
    border-top: 1px solid rgba(255,255,255,0.05);
}
.testimonials-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 48px; }
.testimonial-card {
    background: #161622; border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; padding: 22px;
}
.testimonial-stars { color: #4ade80; font-size: 13px; margin-bottom: 10px; }
.testimonial-text { font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.65; margin-bottom: 14px; font-style: italic; }
.testimonial-author { display: flex; align-items: center; gap: 10px; }
.author-avatar {
    width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 14px; font-weight: 700; flex-shrink: 0;
    background: linear-gradient(135deg, #4ade80, #22c55e); color: #0d1117;
}
.author-name { font-size: 13px; font-weight: 700; }
.author-role { font-size: 11px; color: rgba(255,255,255,0.4); }

/* ─── PRICING ───────────────────────────────────────────── */
.pricing { padding: 80px 48px; text-align: center; }
.pricing-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 640px; margin: 48px auto 0; }
.pricing-card {
    background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px; padding: 28px 24px; text-align: left;
}
.pricing-card.featured {
    background: linear-gradient(135deg, rgba(74,222,128,0.08), rgba(45,27,78,0.15));
    border-color: rgba(74,222,128,0.3);
}
.pricing-plan-label { font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 8px; }
.pricing-card.featured .pricing-plan-label { color: #4ade80; }
.pricing-price { font-size: 36px; font-weight: 900; letter-spacing: -1px; }
.pricing-price span { font-size: 14px; font-weight: 400; color: rgba(255,255,255,0.4); }
.pricing-desc { font-size: 13px; color: rgba(255,255,255,0.45); margin: 8px 0 18px; line-height: 1.5; }
.pricing-features { list-style: none; padding: 0; margin: 0; }
.pricing-features li {
    font-size: 13px; color: rgba(255,255,255,0.6); padding: 5px 0 5px 18px; position: relative;
}
.pricing-features li::before { content: '✓'; position: absolute; left: 0; color: #4ade80; font-weight: 700; }
.pricing-btn {
    display: block; text-align: center; margin-top: 20px; padding: 11px;
    border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none;
}
.pricing-btn-green { background: #4ade80; color: #0d1117; transition: background .2s; }
.pricing-btn-green:hover { background: #22c55e; }
.pricing-btn-ghost { border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.6); transition: border-color .2s, color .2s; }
.pricing-btn-ghost:hover { border-color: rgba(255,255,255,0.35); color: rgba(255,255,255,0.9); }

/* ─── FINAL CTA ─────────────────────────────────────────── */
.final-cta {
    padding: 96px 48px; text-align: center;
    background: radial-gradient(ellipse 60% 50% at 50% 100%, rgba(74,222,128,0.07) 0%, transparent 70%);
}
.final-cta h2 { font-size: 48px; font-weight: 900; letter-spacing: -1.5px; margin-bottom: 16px; }
.final-cta h2 em { font-style: normal; color: #4ade80; }
.final-cta-sub { color: rgba(255,255,255,0.45); font-size: 16px; margin-bottom: 32px; }

/* ─── FOOTER ────────────────────────────────────────────── */
.pub-footer {
    padding: 28px 48px; border-top: 1px solid rgba(255,255,255,0.06);
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
}
.pub-footer-logo { font-weight: 900; font-size: 15px; letter-spacing: -0.5px; color: #fff; text-decoration: none; }
.pub-footer-logo span { color: #4ade80; }
.pub-footer-copy { font-size: 12px; color: rgba(255,255,255,0.25); }
.pub-footer-links { display: flex; gap: 20px; }
.pub-footer-links a { font-size: 12px; color: rgba(255,255,255,0.25); text-decoration: none; }

/* ─── RESPONSIVE ────────────────────────────────────────── */
@media (max-width: 1024px) {
    .features-grid { grid-template-columns: repeat(2, 1fr); }
    .testimonials-grid { grid-template-columns: 1fr; }
    .pricing-grid { grid-template-columns: 1fr; max-width: 380px; }
}
@media (max-width: 768px) {
    .hero { padding: 56px 20px 48px; }
    .hero h1 { font-size: 36px; letter-spacing: -1px; }
    .hero-sub { font-size: 16px; }
    .hero-screen { display: none; }
    .proof-strip { padding: 32px 20px; }
    .features { padding: 56px 20px; }
    .features-grid { grid-template-columns: 1fr; }
    .section-title { font-size: 28px; }
    .testimonials { padding: 48px 20px; }
    .pricing { padding: 56px 20px; }
    .final-cta { padding: 56px 20px; }
    .final-cta h2 { font-size: 32px; }
    .pub-footer { padding: 20px; flex-direction: column; text-align: center; }
}
</style>
@endsection

@section('content')

{{-- ═══ HERO ═══════════════════════════════════════════════ --}}
<section class="hero">
    <div class="hero-badge">
        <span class="hero-badge-dot"></span>
        Beta — bezpłatne do końca 2026
    </div>

    <h1>Prowadzisz firmę<br><em>między zleceniami.</em></h1>

    <p class="hero-sub">
        TBA to AI asystent, który robi papierkową robotę za Ciebie —
        wyceny, maile, plan dnia. Wszystko z telefonu, w minutę.
    </p>

    <div class="hero-actions">
        <a href="/admin/register" class="btn-primary">Wypróbuj za darmo — 0 zł</a>
        <a href="#jak-to-dziala" class="btn-secondary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <polygon points="10,8 16,12 10,16 10,8" fill="currentColor" stroke="none"/>
            </svg>
            Zobacz jak działa
        </a>
    </div>

    <p class="hero-trust">Bez karty kredytowej · Gotowe w 3 minuty · Działa na telefonie</p>

    {{-- App mockup --}}
    <div class="hero-screen" aria-hidden="true">
        <div class="screen-topbar">
            <div class="screen-dot" style="background:#ff5f57"></div>
            <div class="screen-dot" style="background:#ffbd2e"></div>
            <div class="screen-dot" style="background:#28ca41"></div>
            <div class="screen-url">app.tbasystent.pl</div>
        </div>
        <div class="screen-body">
            <div class="screen-sidebar">
                <div class="screen-sidebar-label">MENU</div>
                <div class="screen-nav-item active">🏠 Dashboard</div>
                <div class="screen-nav-item">📋 Zlecenia</div>
                <div class="screen-nav-item">💰 Wyceny</div>
                <div class="screen-nav-item">👥 Klienci</div>
                <div class="screen-nav-item">🤖 Asystent AI</div>
            </div>
            <div class="screen-main">
                <div class="screen-greeting">Cześć, <strong>Ania</strong> 👋 Masz dziś 3 zlecenia.</div>
                <div class="ai-suggestion">
                    <div class="ai-label">✨ ASYSTENT AI</div>
                    <div class="ai-text">
                        Klient Marek Wiśniewski czeka na wycenę sprzątania 140m² w Warszawie.
                        Sugeruję: <strong>420–480 zł</strong> na podstawie Twoich poprzednich zleceń.
                    </div>
                    <div class="ai-actions">
                        <div class="ai-btn">Wyślij wycenę</div>
                        <div class="ai-btn-ghost">Edytuj</div>
                    </div>
                </div>
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-val">12</div>
                        <div class="stat-label">Zleceń w miesiącu</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-val" style="color:#4ade80">4 820 zł</div>
                        <div class="stat-label">Przychód ten miesiąc</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-val">4.9★</div>
                        <div class="stat-label">Średnia ocena</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ SOCIAL PROOF ════════════════════════════════════════ --}}
<div class="proof-strip">
    <p class="proof-label">Zaufali nam właściciele firm z całej Polski</p>
    <div class="proof-pills">
        <div class="proof-pill">🧹 Czyste Wnętrza</div>
        <div class="proof-pill">🔧 Remo-Fix Kraków</div>
        <div class="proof-pill">📸 Foto Nowak</div>
        <div class="proof-pill">📚 Korepetycje Marek</div>
        <div class="proof-pill">🌿 Ogród Pro</div>
    </div>
</div>

{{-- ═══ FEATURES ════════════════════════════════════════════ --}}
<section class="features" id="jak-to-dziala">
    <div class="section-tag">Jak działa</div>
    <h2 class="section-title">AI robi papierologię.<br><em>Ty robisz zlecenia.</em></h2>
    <p class="section-sub">
        Przestań tracić czas na maile i tabelki. TBA obsługuje administrację —
        Ty koncentrujesz się na klientach.
    </p>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">💬</div>
            <h3>Wyceny w minutę</h3>
            <p>Opisz zlecenie głosowo albo tekstem. AI przygotuje wycenę i wyśle ją do klienta — zanim dojedziesz do kolejnego.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>Plan dnia bez chaosu</h3>
            <p>Wszystkie zlecenia w jednym miejscu. TBA pilnuje harmonogramu i przypomina o następnych wizytach.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📩</div>
            <h3>Odpowiedzi na maile</h3>
            <p>AI czyta zapytania klientów i sugeruje gotowe odpowiedzi — Ty tylko zatwierdzasz jednym kliknięciem.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Raport miesiąca</h3>
            <p>Ile zarobiłaś? Którzy klienci wracają? TBA podsumuje miesiąc czytelnym raportem — bez Excela.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔔</div>
            <h3>Przypomnienia dla klientów</h3>
            <p>Automatyczne SMS/e-mail przed wizytą. Mniej odwołań w ostatniej chwili, więcej pewnych zleceń.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Wszystko z telefonu</h3>
            <p>Żadnego laptopa. Pełna aplikacja na iOS i Android — gotowa działać między jednym zleceniem a drugim.</p>
        </div>
    </div>
</section>

{{-- ═══ TESTIMONIALS ════════════════════════════════════════ --}}
<section class="testimonials">
    <div style="text-align:center">
        <div class="section-tag">Opinie</div>
        <h2 class="section-title" style="font-size:34px">
            Co mówią właścicielki<br><em>firm usługowych</em>
        </h2>
    </div>

    <div class="testimonials-grid">
        <div class="testimonial-card">
            <div class="testimonial-stars">★★★★★</div>
            <p class="testimonial-text">
                "Wyceny, które kiedyś zajmowały mi 20 minut, teraz robię w 2 minuty.
                TBA zasugerował stawki, ja kliknęłam 'wyślij' — i tyle."
            </p>
            <div class="testimonial-author">
                <div class="author-avatar">A</div>
                <div>
                    <div class="author-name">Ania K.</div>
                    <div class="author-role">Czyste Wnętrza, Warszawa</div>
                </div>
            </div>
        </div>

        <div class="testimonial-card">
            <div class="testimonial-stars">★★★★★</div>
            <p class="testimonial-text">
                "Prowadziłem firmę remontową z karteczkami i Excelem.
                Teraz widzę wszystkie zlecenia, klientów i przychody w jednym miejscu."
            </p>
            <div class="testimonial-author">
                <div class="author-avatar" style="background:linear-gradient(135deg,#60a5fa,#3b82f6)">M</div>
                <div>
                    <div class="author-name">Marek W.</div>
                    <div class="author-role">Remo-Fix, Kraków</div>
                </div>
            </div>
        </div>

        <div class="testimonial-card">
            <div class="testimonial-stars">★★★★★</div>
            <p class="testimonial-text">
                "Jako fotograf traciłam dużo czasu na komunikację z klientami.
                TBA pisze odpowiedzi — ja je tylko weryfikuję. Oszczędzam 2h dziennie."
            </p>
            <div class="testimonial-author">
                <div class="author-avatar" style="background:linear-gradient(135deg,#f472b6,#ec4899)">K</div>
                <div>
                    <div class="author-name">Kasia N.</div>
                    <div class="author-role">Foto Nowak Studio</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ PRICING ═════════════════════════════════════════════ --}}
<section class="pricing" id="cennik">
    <div class="section-tag">Cennik</div>
    <h2 class="section-title" style="font-size:36px">Prosto i uczciwie</h2>
    <p class="section-sub" style="margin-top:14px;max-width:400px;margin-left:auto;margin-right:auto">
        W becie wszystko za darmo. Po becie — płacisz tylko jeśli TBA Ci pomógł.
    </p>

    <div class="pricing-grid">
        <div class="pricing-card featured">
            <div class="pricing-plan-label">Beta — teraz</div>
            <div class="pricing-price">0 zł <span>/ miesiąc</span></div>
            <p class="pricing-desc">Pełny dostęp przez cały 2026. Żadnych ograniczeń.</p>
            <ul class="pricing-features">
                <li>Nieograniczone wyceny AI</li>
                <li>Zarządzanie zleceniami</li>
                <li>Odpowiedzi na maile</li>
                <li>Raporty miesięczne</li>
                <li>Aplikacja mobilna</li>
            </ul>
            <a href="/admin/register" class="pricing-btn pricing-btn-green">Zacznij teraz — za darmo</a>
        </div>

        <div class="pricing-card">
            <div class="pricing-plan-label">Pro — od 2027</div>
            <div class="pricing-price">99 zł <span>/ miesiąc</span></div>
            <p class="pricing-desc">Dla firm, które wyrosły z bety. Priorytetowe wsparcie i zaawansowane AI.</p>
            <ul class="pricing-features">
                <li>Wszystko z Bety</li>
                <li>Zaawansowane statystyki</li>
                <li>Automatyzacje AI</li>
                <li>Priorytetowe wsparcie</li>
            </ul>
            <a href="/admin/register" class="pricing-btn pricing-btn-ghost">Dołącz do listy oczekujących</a>
        </div>
    </div>
</section>

{{-- ═══ FINAL CTA ═══════════════════════════════════════════ --}}
<section class="final-cta">
    <h2>Zacznij zarządzać firmą<br><em>jak masz czas.</em></h2>
    <p class="final-cta-sub">
        Dołącz do polskich firm, które używają AI do prowadzenia biznesu.
        Bezpłatnie, bez karty, w 3 minuty.
    </p>
    <a href="/admin/register" class="btn-primary" style="font-size:16px;padding:16px 36px">
        Załóż konto — 0 zł
    </a>
    <p style="margin-top:16px;font-size:12px;color:rgba(255,255,255,0.25)">
        Bez karty kredytowej · Anuluj kiedy chcesz · Działa na telefonie
    </p>
</section>

{{-- ═══ FOOTER ══════════════════════════════════════════════ --}}
<footer class="pub-footer">
    <a href="/" class="pub-footer-logo">T<span>.</span>B<span>.</span>A</a>
    <p class="pub-footer-copy">© 2026 Twój Biznes Asystent · tbasystent.pl</p>
    <div class="pub-footer-links">
        <a href="#">Prywatność</a>
        <a href="#">Regulamin</a>
    </div>
</footer>

@endsection
