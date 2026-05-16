@extends('layouts.public')

@section('head')
<title>TBA — Asystent dla firm usługowych | tbasystent.pl</title>
<meta name="description" content="AI asystent dla małych firm usługowych. Klienci, wyceny, grafik — wszystko w jednym miejscu. Bezpłatnie przez beta.">
<link rel="canonical" href="https://tbasystent.pl/">
<meta property="og:type" content="website">
<meta property="og:title" content="TBA — Asystent dla firm usługowych">
<meta property="og:description" content="AI asystent dla małych firm usługowych. Klienci, wyceny, grafik — wszystko w jednym miejscu. Bezpłatnie przez beta.">
<meta property="og:url" content="https://tbasystent.pl/">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="TBA — Asystent dla firm usługowych">
<meta name="twitter:description" content="AI asystent dla małych firm usługowych. Klienci, wyceny, grafik — wszystko w jednym miejscu. Bezpłatnie przez beta.">
<meta name="robots" content="index, follow">
@endsection

@section('content')

{{-- §3 Hero --}}
<section class="bg-gradient-to-br from-teal-600 to-teal-700">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">

            {{-- Left: copy + CTAs --}}
            <div class="text-white">
                <div class="text-xs font-semibold tracking-widest uppercase opacity-70 mb-4">
                    AI dla firm usługowych
                </div>
                <h1>
                    <span class="block text-4xl lg:text-5xl font-extrabold leading-tight mb-4">Twój biznes.<br>Twój asystent.</span>
                </h1>
                <p class="text-lg opacity-85 mb-8">
                    Klienci, wyceny, grafik — wszystko w jednym miejscu.<br>
                    AI proponuje ceny, Ty decydujesz.
                </p>
                <div class="flex flex-wrap gap-3 mb-4">
                    <a href="/admin/register"
                       class="bg-white text-teal-600 font-bold px-6 py-3 rounded-lg hover:bg-teal-50 transition-colors">
                        Wypróbuj za darmo →
                    </a>
                    <a href="#jak-to-dziala"
                       class="border border-white/40 text-white px-6 py-3 rounded-lg hover:bg-white/10 transition-colors">
                        Jak to działa ↓
                    </a>
                </div>
                <p class="text-xs opacity-55">Bezpłatnie przez beta · Bez karty · Dane w Polsce</p>
            </div>

            {{-- Right: AI suggestion card mockup --}}
            <div class="mt-10 lg:mt-0">
                <div class="bg-white rounded-xl shadow-lg p-5 max-w-sm mx-auto">
                    <div class="text-teal-600 text-sm font-semibold mb-3" aria-hidden="true">✦ Sugestia AI — pani Kowalska</div>
                    <div class="border-t border-slate-100 pt-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-700">Sprzątanie 90m²</span>
                            <span class="font-semibold text-slate-900">380 zł</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Dojazd 18 km</span>
                            <span class="text-slate-500">28 zł</span>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 mt-3 pt-3 flex justify-between text-sm font-bold">
                        <span class="text-slate-900">Razem</span>
                        <span class="text-teal-600">408 zł</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- §4 Honesty Strip --}}
<section class="bg-teal-50 border-l-4 border-teal-500">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-teal-600 flex-shrink-0 flex items-center justify-center text-white font-bold text-lg">
                A
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-900">Ania, firma sprzątająca · Warszawa</div>
                <p class="italic text-slate-600 text-sm mt-1 leading-relaxed">
                    "Wyceniałam codziennie w Excelu. Teraz robię to w 2 minuty i z historią klienta."
                </p>
                <p class="text-xs text-slate-400 mt-2">Buduję TBA razem z Anią od dnia pierwszego.</p>
            </div>
        </div>
    </div>
</section>

{{-- §5 Branża Switcher --}}
<section id="jak-to-dziala" class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="text-2xl font-bold text-center text-slate-900 mb-8">Dla jakiej branży?</h2>

        <div x-data="{ industry: 'sprzatanie' }">

            {{-- Chips --}}
            <div class="flex flex-wrap justify-center gap-3 mb-8">
                <button type="button" @click="industry = 'sprzatanie'"
                        :class="industry === 'sprzatanie' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors">
                    ✓ Sprzątanie
                </button>
                <button type="button" @click="industry = 'remonty'"
                        :class="industry === 'remonty' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors">
                    Remonty
                </button>
                <button type="button" @click="industry = 'fotografia'"
                        :class="industry === 'fotografia' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors">
                    Fotografia
                </button>
                <button type="button" @click="industry = 'korepetycje'"
                        :class="industry === 'korepetycje' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors">
                    Korepetycje
                </button>
                <button type="button" @click="industry = 'inna'"
                        :class="industry === 'inna' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors">
                    Inna branża
                </button>
            </div>

            {{-- Cleaning panel --}}
            <div x-show="industry === 'sprzatanie'" class="max-w-lg mx-auto">
                <ul class="space-y-3 mb-6">
                    <li class="flex items-start gap-3 text-slate-700 text-sm">
                        <span class="text-teal-600 font-bold mt-0.5">✓</span>
                        Zapamiętuje każdą klientkę — metraż, klucze, alergie, preferencje
                    </li>
                    <li class="flex items-start gap-3 text-slate-700 text-sm">
                        <span class="text-teal-600 font-bold mt-0.5">✓</span>
                        AI proponuje ceny na podstawie historii i odległości
                    </li>
                    <li class="flex items-start gap-3 text-slate-700 text-sm">
                        <span class="text-teal-600 font-bold mt-0.5">✓</span>
                        Grafik ekipy, cykliczne zlecenia, dojazd doliczony automatycznie
                    </li>
                </ul>
                <a href="/admin/register" class="text-teal-600 font-semibold text-sm hover:underline">
                    Wypróbuj za darmo →
                </a>
            </div>

            {{-- Other industries panel --}}
            <div x-show="industry !== 'sprzatanie'" class="max-w-lg mx-auto text-center" x-cloak>
                <p class="text-slate-700 mb-2">
                    Preset dla tej branży jest w budowie.
                </p>
                <p class="text-sm text-slate-500 mb-6">
                    Podstawa produktu (klienci, notatki, wyceny) działa dla każdej firmy usługowej już teraz.
                </p>
                <a href="/admin/register" class="text-teal-600 font-semibold text-sm hover:underline">
                    Wypróbuj już teraz →
                </a>
            </div>

        </div>
    </div>
</section>

{{-- §6 Value Props --}}
<section id="funkcje" class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-6">

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <div class="text-3xl mb-3" aria-hidden="true">💡</div>
                <h3 class="font-bold text-slate-900 mb-2">Wycenia za Ciebie</h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    AI proponuje cenę z historii zleceń i kosztu dojazdu. Nie musisz pamiętać ile wzięłaś od pani Kowalskiej w lutym.
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <div class="text-3xl mb-3" aria-hidden="true">🧠</div>
                <h3 class="font-bold text-slate-900 mb-2">Pamięta klientów</h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Notatki głosowe z samochodu, zdjęcia, preferencje, alergie — wszystko w jednym miejscu. Zapytaj, odpowie.
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <div class="text-3xl mb-3" aria-hidden="true">📅</div>
                <h3 class="font-bold text-slate-900 mb-2">Porządkuje tydzień</h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Grafik ekipy, cykliczne zlecenia, dojazd doliczony automatycznie. Mniej Excela, więcej spokoju.
                </p>
            </div>

        </div>
    </div>
</section>

{{-- §7a Feature: AI Wycena (image-left) --}}
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">

            <div class="bg-teal-50 border border-teal-100 rounded-xl p-5 mb-8 lg:mb-0">
                <div class="text-teal-600 text-xs font-semibold tracking-widest uppercase mb-3" aria-hidden="true">✦ AI WYCENA</div>
                <p class="text-slate-600 text-sm mb-3">
                    Notatka: <em>"90m² mieszkanie, kot, pierwsze sprzątanie generalne"</em>
                </p>
                <p class="text-teal-700 font-semibold text-sm mb-1">→ Sugestia: 380 zł + 28 zł dojazd</p>
                <p class="text-slate-400 text-xs mb-4">Bo: 3 podobne zlecenia 340–420 zł, 18 km od bazy</p>
                <div class="flex gap-2">
                    <span class="bg-teal-600 text-white text-xs px-3 py-1.5 rounded cursor-default">Użyj sugestii</span>
                    <span class="bg-white border border-slate-200 text-slate-600 text-xs px-3 py-1.5 rounded cursor-default">Zmień</span>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-slate-900 mb-3">AI proponuje. Ty decydujesz.</h2>
                <p class="text-slate-500 leading-relaxed mb-3">
                    Na podstawie historii Twoich zleceń i odległości od domu. Zawsze możesz zmienić cenę — to sugestia, nie decyzja.
                </p>
                <p class="text-xs text-slate-400 italic">Wkrótce — Sprint 3</p>
            </div>

        </div>
    </div>
</section>

{{-- §7b Feature: Notatki Głosowe (image-right) --}}
<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">

            <div class="order-2 lg:order-1">
                <h2 class="text-2xl font-bold text-slate-900 mb-3">Nagraj z samochodu. TBA przepisze.</h2>
                <p class="text-slate-500 leading-relaxed mb-3">
                    Whisper transkrybuje notatki po polsku. Trafiają na profil klientki automatycznie, gotowe do przeszukiwania.
                </p>
                <p class="text-xs text-slate-400 italic">Wkrótce — Sprint 5</p>
            </div>

            <div class="order-1 lg:order-2 bg-white border border-slate-100 rounded-xl p-5 mb-8 lg:mb-0">
                <div class="text-teal-600 text-xs font-semibold tracking-widest uppercase mb-3" aria-hidden="true">🎙️ NOTATKI GŁOSOWE</div>
                <div class="bg-red-50 border border-red-100 rounded-lg px-3 py-2 text-sm text-red-700 font-medium mb-3">
                    ● Nagrywanie... 0:42
                </div>
                <div class="border-l-2 border-teal-300 pl-3 text-slate-600 text-sm leading-relaxed">
                    "Pani Nowak — nowe mieszkanie, 75 metrów, dwa koty,
                    klucz pod wycieraczką, prosi o środki bezzapachowe..."
                </div>
            </div>

        </div>
    </div>
</section>

{{-- §7c Feature: Chat o Kliencie (image-left) --}}
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 mb-8 lg:mb-0 font-mono text-sm">
                <div class="text-teal-600 text-xs font-semibold tracking-widest uppercase mb-4 font-sans" aria-hidden="true">💬 CHAT O KLIENCIE</div>
                <div class="bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 text-slate-700 mb-3">
                    Ty: Co obiecałam pani Kowalskiej na maj?
                </div>
                <div class="bg-teal-50 border border-teal-100 rounded-lg px-3 py-2 text-slate-600 leading-relaxed">
                    TBA: Obiecałaś generalne sprzątanie po malowaniu salonu
                    [notatka z 14 marca]. Prosiła też żeby zabrać dywan
                    do prania.
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-slate-900 mb-3">Zapytaj normalnie. Odpowie z dowodami.</h2>
                <p class="text-slate-500 leading-relaxed mb-3">
                    Przeszukuje notatki i historię zleceń. Cytuje konkretne wpisy. Nigdy nie zgaduje.
                </p>
                <p class="text-xs text-slate-400 italic">Wkrótce — Sprint 6</p>
            </div>

        </div>
    </div>
</section>

{{-- §8 Pricing --}}
<section id="cennik" class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="text-2xl font-bold text-center text-slate-900 mb-4">Cennik</h2>

        <div class="grid md:grid-cols-3 gap-6 mb-6">

            {{-- Beta (highlighted) --}}
            <div class="bg-teal-600 text-white rounded-xl p-6 md:col-span-1">
                <div class="text-xs tracking-widest uppercase opacity-80 mb-1">Beta</div>
                <div class="text-4xl font-extrabold mb-1">0 zł</div>
                <div class="text-sm opacity-80 mb-6">Dostępny teraz</div>
                <ul class="space-y-2 text-sm mb-6">
                    <li class="flex items-start gap-2"><span class="opacity-70">✓</span> Wszystkie funkcje bez limitu</li>
                    <li class="flex items-start gap-2"><span class="opacity-70">✓</span> Wsparcie bezpośrednio od twórcy</li>
                    <li class="flex items-start gap-2"><span class="opacity-70">✓</span> Kształtujesz produkt swoim feedbackiem</li>
                    <li class="flex items-start gap-2"><span class="opacity-70">✓</span> Dane w Polsce (Hetzner)</li>
                </ul>
                <a href="/admin/register"
                   class="block text-center bg-white text-teal-600 font-bold py-2.5 rounded-lg hover:bg-teal-50 transition-colors">
                    Dołącz teraz
                </a>
            </div>

            {{-- Starter --}}
            <div class="bg-white rounded-xl p-6 border border-slate-100 opacity-60">
                <div class="text-xs tracking-widest uppercase text-slate-400 mb-1">Starter</div>
                <div class="text-4xl font-extrabold text-slate-900 mb-1">49 zł<span class="text-base font-normal text-slate-400">/mies</span></div>
                <div class="text-sm text-slate-400">od 2027</div>
            </div>

            {{-- Solo --}}
            <div class="bg-white rounded-xl p-6 border border-slate-100 opacity-60">
                <div class="text-xs tracking-widest uppercase text-slate-400 mb-1">Solo</div>
                <div class="text-4xl font-extrabold text-slate-900 mb-1">79 zł<span class="text-base font-normal text-slate-400">/mies</span></div>
                <div class="text-sm text-slate-400">od 2027</div>
            </div>

        </div>

        <p class="text-center text-slate-400 text-sm italic">
            "Pricing widoczny już teraz, bo ukryte ceny to nasza największa irytacja u konkurencji."
        </p>

    </div>
</section>

{{-- §9 FAQ --}}
<section class="py-16 bg-white">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="text-2xl font-bold text-center text-slate-900 mb-8">Często pytane</h2>

        <div class="space-y-2">

            <details class="border border-slate-100 rounded-xl overflow-hidden group">
                <summary class="flex justify-between items-center px-5 py-4 cursor-pointer text-slate-900 font-medium list-none hover:bg-slate-50">
                    Nie jestem techniczna, czy sobie poradzę?
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="px-5 pb-4 text-slate-500 text-sm leading-relaxed">
                    Jeśli piszesz na WhatsAppie i robisz zakupy online, poradzisz sobie. Dodanie klientki to 3 pola i kliknięcie.
                </div>
            </details>

            <details class="border border-slate-100 rounded-xl overflow-hidden group">
                <summary class="flex justify-between items-center px-5 py-4 cursor-pointer text-slate-900 font-medium list-none hover:bg-slate-50">
                    Ile to kosztuje naprawdę?
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="px-5 pb-4 text-slate-500 text-sm leading-relaxed">
                    W becie: 0 zł. Bez karty. Bez ukrytych opłat. Płatne plany od 2027 — widzisz je już teraz w cenniku.
                </div>
            </details>

            <details class="border border-slate-100 rounded-xl overflow-hidden group">
                <summary class="flex justify-between items-center px-5 py-4 cursor-pointer text-slate-900 font-medium list-none hover:bg-slate-50">
                    Czy dane moich klientek są bezpieczne?
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="px-5 pb-4 text-slate-500 text-sm leading-relaxed">
                    Serwery w Polsce (Hetzner). Szyfrowanie AES-256. Zgodność z RODO. Klucze i kody do mieszkań są szyfrowane — nawet my ich nie widzimy.
                </div>
            </details>

            <details class="border border-slate-100 rounded-xl overflow-hidden group">
                <summary class="flex justify-between items-center px-5 py-4 cursor-pointer text-slate-900 font-medium list-none hover:bg-slate-50">
                    Co jeśli chcę wyjść?
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="px-5 pb-4 text-slate-500 text-sm leading-relaxed">
                    Napisz do nas — wyeksportujemy Twoje dane w ciągu 24 godzin. Twoje dane to Twoje dane.
                </div>
            </details>

            <details class="border border-slate-100 rounded-xl overflow-hidden group">
                <summary class="flex justify-between items-center px-5 py-4 cursor-pointer text-slate-900 font-medium list-none hover:bg-slate-50">
                    Co jeśli nie mam internetu w trasie?
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="px-5 pb-4 text-slate-500 text-sm leading-relaxed">
                    TBA działa w przeglądarce i dobrze sprawuje się na telefonie. Aplikacja jest mobilna i responsywna — możesz wygodnie korzystać z ekranu telefonu w terenie.
                </div>
            </details>

            <details class="border border-slate-100 rounded-xl overflow-hidden group">
                <summary class="flex justify-between items-center px-5 py-4 cursor-pointer text-slate-900 font-medium list-none hover:bg-slate-50">
                    Prowadzę inną branżę niż sprzątanie — to dla mnie?
                    <span class="text-slate-400 group-open:rotate-180 transition-transform">↓</span>
                </summary>
                <div class="px-5 pb-4 text-slate-500 text-sm leading-relaxed">
                    Podstawa (klienci, notatki, wyceny) działa dla każdej firmy usługowej. Szablony branżowe dla remontów, fotografii itp. są w budowie — możesz zacząć już teraz.
                </div>
            </details>

        </div>
    </div>
</section>

{{-- §10 Final CTA Band --}}
<section class="bg-gradient-to-br from-teal-600 to-teal-700 py-20">
    <div class="max-w-xl mx-auto px-4 text-center text-white">
        <h2 class="text-3xl font-extrabold mb-3">Gotowa zacząć?</h2>
        <p class="opacity-80 mb-8">Dołącz do bety. Bezpłatnie. Bez karty.</p>
        <a href="/admin/register"
           class="inline-block bg-white text-teal-600 font-bold px-8 py-4 rounded-lg text-lg hover:bg-teal-50 transition-colors">
            Wypróbuj za darmo →
        </a>
        <p class="text-xs opacity-55 mt-4">Bezpłatnie przez beta · Bez karty · Dane w Polsce</p>
    </div>
</section>

@endsection
