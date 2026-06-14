<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d0d14">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="alternate" hreflang="pl" href="https://tbasystent.pl/">
    <link rel="alternate" hreflang="x-default" href="https://tbasystent.pl/">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,600;0,700;0,900;1,400&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,600;0,700;0,900;1,400&display=swap"></noscript>
    @yield('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0d0d14; color: #ffffff; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .pub-nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 48px; height: 60px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            position: sticky; top: 0; z-index: 100;
            background: rgba(13,13,20,0.85); -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px);
        }
        .pub-nav-logo { font-weight: 900; font-size: 18px; letter-spacing: -0.5px; color: #fff; text-decoration: none; }
        .pub-nav-logo span { color: #4ade80; }
        .pub-nav-links { display: flex; gap: 28px; }
        .pub-nav-links a { color: rgba(255,255,255,0.65); font-size: 14px; text-decoration: none; transition: color .2s; }
        .pub-nav-links a:hover { color: #fff; }
        .pub-nav-links a:focus-visible { outline: 2px solid #4ade80; outline-offset: 3px; border-radius: 3px; }
        .pub-nav-cta {
            background: #4ade80; color: #0d1117; border-radius: 8px;
            padding: 8px 18px; font-size: 14px; font-weight: 700; text-decoration: none;
            transition: background .2s;
        }
        .pub-nav-cta:hover { background: #22c55e; }
        .pub-nav-cta:focus-visible { outline: 2px solid #4ade80; outline-offset: 3px; }
        .pub-nav-mobile-btn {
            display: none; background: none; border: none; color: rgba(255,255,255,0.7);
            cursor: pointer; padding: 8px; border-radius: 6px; line-height: 0;
        }
        .pub-nav-mobile-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }
        @media (max-width: 768px) {
            .pub-nav { padding: 0 20px; }
            .pub-nav-mobile-btn { display: flex; align-items: center; }
            .pub-nav-links {
                display: none; position: fixed; top: 60px; left: 0; right: 0; bottom: 0;
                background: rgba(13,13,20,0.97); flex-direction: column;
                padding: 24px 20px; gap: 0; z-index: 99;
                border-top: 1px solid rgba(255,255,255,0.06);
                overflow-y: auto;
            }
            .pub-nav-links.is-open { display: flex; }
            .pub-nav-links a { font-size: 17px; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
            .pub-nav-links a:last-child { border-bottom: none; }
            .pub-nav-cta { padding: 6px 14px; font-size: 13px; }
        }

        /* ─── FOOTER ─────────────────────────────────────────── */
        .pub-footer {
            padding: 28px 48px; border-top: 1px solid rgba(255,255,255,0.06);
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
        }
        .pub-footer-logo { font-weight: 900; font-size: 15px; letter-spacing: -0.5px; color: #fff; text-decoration: none; }
        .pub-footer-logo span { color: #4ade80; }
        .pub-footer-copy { font-size: 12px; color: rgba(255,255,255,0.25); margin: 0; }
        .pub-footer-links { display: flex; gap: 20px; flex-wrap: wrap; align-items: center; }
        .pub-footer-links-item { font-size: 12px; color: rgba(255,255,255,0.35); text-decoration: none; transition: color .2s; }
        .pub-footer-links-item:hover { color: rgba(255,255,255,0.7); }
        @media (max-width: 768px) {
            .pub-footer { padding: 20px; flex-direction: column; text-align: center; }
            .pub-footer-links { justify-content: center; }
        }
    </style>
</head>
<body class="antialiased">

    <nav aria-label="Główna nawigacja">
        <div class="pub-nav">
            <a href="/" class="pub-nav-logo">T<span>.</span>B<span>.</span>A</a>
            <div class="pub-nav-links" id="pub-nav-links">
                <a href="/#jak-to-dziala">Jak działa</a>
                <a href="/#cennik">Cennik</a>
                <a href="/#faq">FAQ</a>
                <a href="/blog">Blog</a>
                <a href="/regulamin">Regulamin</a>
                <a href="/polityka-prywatnosci">Polityka</a>
            </div>
            <a href="/admin/register" class="pub-nav-cta">Zacznij za darmo</a>
            <button class="pub-nav-mobile-btn" id="pub-nav-toggle" aria-label="Otwórz menu" aria-expanded="false" aria-controls="pub-nav-links">
                <svg class="icon-menu" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
                <svg class="icon-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="display:none">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </nav>

    @yield('content')

<script>
(function () {
    var btn = document.getElementById('pub-nav-toggle');
    var links = document.getElementById('pub-nav-links');
    if (!btn || !links) return;
    btn.addEventListener('click', function () {
        var open = links.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', String(open));
        btn.querySelector('.icon-menu').style.display = open ? 'none' : '';
        btn.querySelector('.icon-close').style.display = open ? '' : 'none';
    });
    links.addEventListener('click', function (e) {
        if (e.target.tagName === 'A') {
            links.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
            btn.querySelector('.icon-menu').style.display = '';
            btn.querySelector('.icon-close').style.display = 'none';
        }
    });
}());
</script>
</body>
</html>
