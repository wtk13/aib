<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-language" content="pl">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    @yield('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-[Inter,sans-serif] antialiased">

    {{-- Sticky Nav --}}
    <nav aria-label="Główna nawigacja" class="sticky top-0 z-50 bg-teal-600">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <a href="/" class="text-white font-bold text-lg tracking-tight">✦ TBA</a>
                <div class="hidden md:flex items-center gap-6 text-sm text-white/80">
                    <a href="#jak-to-dziala" class="hover:text-white transition-colors">Jak to działa</a>
                    <a href="#cennik" class="hover:text-white transition-colors">Cennik</a>
                </div>
                <a href="/admin/register"
                   class="bg-white text-teal-600 font-bold text-sm px-4 py-2 rounded-lg hover:bg-teal-50 transition-colors">
                    Wypróbuj →
                </a>
            </div>
        </div>
    </nav>

    @yield('content')

</body>
</html>
