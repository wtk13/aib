<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="pl">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d0d14">
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
        @media (max-width: 768px) {
            .pub-nav { padding: 0 20px; }
            .pub-nav-links { display: none; }
            .pub-nav-cta { padding: 6px 14px; font-size: 13px; }
        }
    </style>
</head>
<body class="antialiased">

    <nav aria-label="Główna nawigacja">
        <div class="pub-nav">
            <a href="/" class="pub-nav-logo">T<span>.</span>B<span>.</span>A</a>
            <div class="pub-nav-links">
                <a href="/#jak-to-dziala">Funkcje</a>
                <a href="/#cennik">Cennik</a>
                <a href="/regulamin">Regulamin</a>
            </div>
            <a href="/admin/register" class="pub-nav-cta">Zacznij za darmo</a>
        </div>
    </nav>

    @yield('content')

</body>
</html>
