@extends('layouts.public')

@section('head')
<title>Polityka prywatności — TBA | tbasystent.pl</title>
<meta name="description" content="Polityka prywatności Twój Biznes Asystent (TBA) — jak przetwarzamy dane osobowe zgodnie z RODO.">
<link rel="canonical" href="https://tbasystent.pl/polityka-prywatnosci">
<meta name="robots" content="index, follow">
<style>
.legal-page {
    max-width: 760px; margin: 0 auto; padding: 64px 48px 96px;
}
.legal-page h1 {
    font-size: 36px; font-weight: 900; letter-spacing: -1px; margin-bottom: 8px;
}
.legal-meta {
    font-size: 13px; color: rgba(255,255,255,0.35); margin-bottom: 48px;
    padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.07);
}
.legal-page h2 {
    font-size: 18px; font-weight: 700; margin: 40px 0 12px; color: #4ade80;
}
.legal-page h3 {
    font-size: 15px; font-weight: 700; margin: 24px 0 8px; color: rgba(255,255,255,0.85);
}
.legal-page p {
    font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.75; margin-bottom: 12px;
}
.legal-page ul, .legal-page ol {
    font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.75;
    margin: 8px 0 16px; padding-left: 24px;
}
.legal-page li { margin-bottom: 6px; }
.legal-page a { color: #4ade80; text-decoration: none; }
.legal-page a:hover { text-decoration: underline; }
.legal-box {
    background: rgba(74,222,128,0.05); border: 1px solid rgba(74,222,128,0.15);
    border-radius: 10px; padding: 20px 24px; margin: 32px 0;
}
.legal-box p { margin-bottom: 4px; }
.legal-box strong { color: rgba(255,255,255,0.85); }
.legal-table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
.legal-table th {
    text-align: left; padding: 10px 12px; background: rgba(255,255,255,0.04);
    color: rgba(255,255,255,0.5); font-weight: 600; border-bottom: 1px solid rgba(255,255,255,0.08);
}
.legal-table td {
    padding: 10px 12px; color: rgba(255,255,255,0.6);
    border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: top;
}
@media (max-width: 768px) {
    .legal-page { padding: 40px 20px 64px; }
    .legal-page h1 { font-size: 28px; }
    .legal-table { display: block; overflow-x: auto; }
}
</style>
@endsection

@section('content')
<div class="legal-page">

    <h1>Polityka prywatności</h1>
    <p class="legal-meta">
        Wersja 1.0 · Obowiązuje od 14 czerwca 2026 r. · Usługa: Twój Biznes Asystent (TBA) · tbasystent.pl
    </p>

    <div class="legal-box">
        <p><strong>Administrator danych osobowych:</strong></p>
        <p><strong>Wojciech Rybiński</strong></p>
        <p>ul. Lekka 3/110, 01-910 Warszawa</p>
        <p>NIP: 8133481592 · REGON: 364036823</p>
        <p>E-mail: <a href="mailto:kontakt@tbasystent.pl">kontakt@tbasystent.pl</a></p>
    </div>

    <h2>1. Jakie dane zbieramy</h2>
    <table class="legal-table">
        <thead>
            <tr>
                <th>Kategoria danych</th>
                <th>Przykłady</th>
                <th>Cel</th>
                <th>Podstawa prawna</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dane konta</td>
                <td>Imię i nazwisko lub nazwa firmy, adres e-mail, hasło (zaszyfrowane)</td>
                <td>Rejestracja i logowanie</td>
                <td>Art. 6 ust. 1 lit. b RODO (wykonanie umowy)</td>
            </tr>
            <tr>
                <td>Dane firmy</td>
                <td>NIP, adres, dane kontaktowe firmy Użytkownika</td>
                <td>Konfiguracja konta, wystawianie dokumentów</td>
                <td>Art. 6 ust. 1 lit. b RODO</td>
            </tr>
            <tr>
                <td>Dane klientów Użytkownika</td>
                <td>Imiona, adresy, numery telefonów, e-maile klientów firmy</td>
                <td>Świadczenie funkcji CRM i wycen</td>
                <td>Art. 6 ust. 1 lit. b RODO (umowa powierzenia)</td>
            </tr>
            <tr>
                <td>Nagrania głosowe</td>
                <td>Pliki audio rejestrowane w module notatek głosowych</td>
                <td>Transkrypcja (OpenAI Whisper), tworzenie notatki</td>
                <td>Art. 6 ust. 1 lit. b RODO</td>
            </tr>
            <tr>
                <td>Dane techniczne</td>
                <td>Adres IP, typ przeglądarki, logi dostępu</td>
                <td>Bezpieczeństwo, diagnostyka</td>
                <td>Art. 6 ust. 1 lit. f RODO (uzasadniony interes)</td>
            </tr>
            <tr>
                <td>Dane użycia AI</td>
                <td>Liczba tokenów, czas odpowiedzi, model AI (bez treści zapytań)</td>
                <td>Optymalizacja kosztów, jakość Usługi</td>
                <td>Art. 6 ust. 1 lit. f RODO</td>
            </tr>
        </tbody>
    </table>

    <h2>2. Jak długo przechowujemy dane</h2>
    <ul>
        <li><strong>Dane konta:</strong> przez czas aktywności konta, a po jego usunięciu — do 30 dni (kopie zapasowe).</li>
        <li><strong>Dane klientów i notatki:</strong> przez czas trwania konta. Usuwane wraz z kontem.</li>
        <li><strong>Nagrania głosowe:</strong> usuwane niezwłocznie po pomyślnej transkrypcji; maksymalnie 7 dni.</li>
        <li><strong>Logi techniczne:</strong> 90 dni.</li>
        <li><strong>Dane rozliczeniowe</strong> (gdy Usługa stanie się płatna): 5 lat (obowiązek podatkowy).</li>
    </ul>

    <h2>3. Komu przekazujemy dane</h2>
    <p>Dane mogą być przekazywane wyłącznie podmiotom, które są niezbędne do świadczenia Usługi:</p>
    <table class="legal-table">
        <thead>
            <tr><th>Podmiot</th><th>Rola</th><th>Przekazywane dane</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Anthropic (USA)</td>
                <td>Dostawca AI (Claude)</td>
                <td>Treści notatek i kontekst potrzebny do odpowiedzi AI (bez danych identyfikacyjnych Użytkownika)</td>
            </tr>
            <tr>
                <td>OpenAI (USA)</td>
                <td>Transkrypcja głosu (Whisper) i embeddingi tekstu</td>
                <td>Pliki audio i treści notatek</td>
            </tr>
            <tr>
                <td>Hetzner / OVH (UE)</td>
                <td>Hosting serwerów</td>
                <td>Wszystkie dane (serwery w UE)</td>
            </tr>
        </tbody>
    </table>
    <p>Przekazywanie danych do USA (Anthropic, OpenAI) odbywa się na podstawie standardowych klauzul umownych (SCC) zatwierdzonych przez Komisję Europejską.</p>

    <h2>4. Powierzenie przetwarzania (Użytkownik jako administrator)</h2>
    <p>
        Dane osobowe klientów Użytkownika (imiona, adresy, kontakty) wprowadzane do Usługi są przetwarzane przez Usługodawcę
        jako <strong>podmiot przetwarzający (procesor)</strong> na podstawie art. 28 RODO.
        Użytkownik pozostaje <strong>administratorem</strong> tych danych i odpowiada za ich legalność
        (m.in. za posiadanie odpowiedniej podstawy prawnej do przetwarzania danych swoich klientów).
    </p>
    <p>
        Szczegółowe warunki powierzenia przetwarzania określone są w
        <a href="/regulamin">Regulaminie (§ 8)</a>. Na pisemny wniosek Użytkownika
        Usługodawca może zawrzeć odrębną umowę powierzenia przetwarzania.
    </p>

    <h2>5. Prawa osób, których dane dotyczą</h2>
    <p>Przysługują Ci następujące prawa:</p>
    <ul>
        <li><strong>Dostęp</strong> — prawo do uzyskania informacji o przetwarzanych danych (art. 15 RODO).</li>
        <li><strong>Sprostowanie</strong> — prawo do poprawienia nieprawidłowych danych (art. 16 RODO).</li>
        <li><strong>Usunięcie</strong> — prawo do żądania usunięcia danych (art. 17 RODO) — zrealizowane przez usunięcie konta.</li>
        <li><strong>Ograniczenie przetwarzania</strong> — prawo do ograniczenia zakresu przetwarzania (art. 18 RODO).</li>
        <li><strong>Przeniesienie danych</strong> — prawo do otrzymania danych w formacie nadającym się do odczytu maszynowego (art. 20 RODO).</li>
        <li><strong>Sprzeciw</strong> — prawo do sprzeciwu wobec przetwarzania opartego na uzasadnionym interesie (art. 21 RODO).</li>
        <li><strong>Skarga</strong> — prawo do wniesienia skargi do <strong>Prezesa Urzędu Ochrony Danych Osobowych</strong> (UODO), ul. Stawki 2, 00-193 Warszawa.</li>
    </ul>
    <p>Aby skorzystać z praw, wyślij wiadomość na <a href="mailto:kontakt@tbasystent.pl">kontakt@tbasystent.pl</a>. Odpowiadamy w ciągu 30 dni.</p>

    <h2>6. Bezpieczeństwo danych</h2>
    <ul>
        <li>Połączenie z Usługą szyfrowane jest protokołem TLS (HTTPS).</li>
        <li>Hasła przechowywane są w postaci zaszyfrowanej (bcrypt).</li>
        <li>Dostęp do danych jest izolowany per-firma (subdomena) — dane jednej firmy są niedostępne dla innych użytkowników.</li>
        <li>Regularne kopie zapasowe bazy danych (retencja: 7 dni).</li>
        <li>Dostęp do infrastruktury produkcyjnej jest ograniczony i monitorowany.</li>
    </ul>

    <h2>7. Pliki cookie</h2>
    <p>Usługa używa wyłącznie niezbędnych plików cookie:</p>
    <ul>
        <li><strong>session</strong> — sesja logowania (wygasa po zamknięciu przeglądarki lub po 2 godzinach nieaktywności).</li>
        <li><strong>XSRF-TOKEN</strong> — ochrona przed atakami CSRF (bezpieczeństwo formularzy).</li>
    </ul>
    <p>Nie używamy plików cookie analitycznych, reklamowych ani plików cookie stron trzecich w celach śledzenia.</p>

    <h2>8. Zmiany Polityki prywatności</h2>
    <p>
        O istotnych zmianach Polityki prywatności informujemy Użytkowników drogą e-mail
        z co najmniej 14-dniowym wyprzedzeniem. Aktualna wersja dostępna jest zawsze pod adresem
        <a href="https://tbasystent.pl/polityka-prywatnosci">tbasystent.pl/polityka-prywatnosci</a>.
    </p>

    <p style="margin-top: 48px; font-size: 13px; color: rgba(255,255,255,0.25);">
        Pytania dotyczące ochrony danych? Pisz na <a href="mailto:kontakt@tbasystent.pl">kontakt@tbasystent.pl</a>
    </p>

</div>

<footer class="pub-footer">
    <a href="/" class="pub-footer-logo">T<span>.</span>B<span>.</span>A</a>
    <p class="pub-footer-copy">© 2026 Wojciech Rybiński · NIP 8133481592 · tbasystent.pl</p>
    <div class="pub-footer-links">
        <a href="/polityka-prywatnosci" class="pub-footer-links-item" style="color:#4ade80">Polityka prywatności</a>
        <a href="/regulamin" class="pub-footer-links-item">Regulamin</a>
    </div>
</footer>

@endsection
