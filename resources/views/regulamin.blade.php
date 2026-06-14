@extends('layouts.public')

@section('head')
<title>Regulamin — TBA | tbasystent.pl</title>
<meta name="description" content="Regulamin świadczenia usług drogą elektroniczną przez Twój Biznes Asystent (TBA).">
<link rel="canonical" href="https://tbasystent.pl/regulamin">
<meta name="robots" content="noindex, follow">
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
    font-size: 18px; font-weight: 700; margin: 40px 0 12px;
    color: #4ade80;
}
.legal-page h3 {
    font-size: 15px; font-weight: 700; margin: 24px 0 8px;
    color: rgba(255,255,255,0.85);
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
@media (max-width: 768px) {
    .legal-page { padding: 40px 20px 64px; }
    .legal-page h1 { font-size: 28px; }
}
</style>
@endsection

@section('content')
<div class="legal-page">

    <h1>Regulamin</h1>
    <p class="legal-meta">
        Wersja 1.0 · Obowiązuje od 14 czerwca 2026 r. · Usługa: Twój Biznes Asystent (TBA) · tbasystent.pl
    </p>

    <div class="legal-box">
        <p><strong>Usługodawca:</strong> Wojciech Rybiński</p>
        <p><strong>Adres:</strong> ul. Lekka 3/110, 01-910 Warszawa</p>
        <p><strong>NIP:</strong> 8133481592</p>
        <p><strong>REGON:</strong> 364036823</p>
        <p><strong>Kontakt:</strong> <a href="mailto:kontakt@tbasystent.pl">kontakt@tbasystent.pl</a></p>
    </div>

    <h2>§ 1. Definicje</h2>
    <p>Użyte w Regulaminie pojęcia oznaczają:</p>
    <ol>
        <li><strong>Usługodawca</strong> – Wojciech Rybiński, prowadzący jednoosobową działalność gospodarczą, ul. Lekka 3/110, 01-910 Warszawa, NIP 8133481592, REGON 364036823.</li>
        <li><strong>Usługa / TBA</strong> – aplikacja internetowa „Twój Biznes Asystent" (TBA) dostępna pod adresem tbasystent.pl i powiązanymi subdomenami, umożliwiająca zarządzanie zleceniami, wycenami i komunikacją z klientami z wykorzystaniem sztucznej inteligencji.</li>
        <li><strong>Użytkownik</strong> – osoba fizyczna prowadząca działalność gospodarczą lub osoba prawna, która dokonała rejestracji konta i korzysta z Usługi.</li>
        <li><strong>Konto</strong> – indywidualne konto Użytkownika w Usłudze, chronione loginem i hasłem.</li>
        <li><strong>Okres próbny</strong> – pierwsze 30 dni od rejestracji konta, w których dostęp do Usługi jest bezpłatny i nieograniczony. Po upływie okresu próbnego Usługa przechodzi na plan płatny (§ 6).</li>
        <li><strong>Dane</strong> – informacje wprowadzane przez Użytkownika do Usługi, w tym dane klientów, wyceny i notatki.</li>
        <li><strong>Regulamin</strong> – niniejszy dokument.</li>
    </ol>

    <h2>§ 2. Postanowienia ogólne</h2>
    <ol>
        <li>Regulamin określa zasady świadczenia usług drogą elektroniczną przez Usługodawcę w rozumieniu ustawy z dnia 18 lipca 2002 r. o świadczeniu usług drogą elektroniczną (t.j. Dz.U. 2020 poz. 344 ze zm.).</li>
        <li>Korzystanie z Usługi oznacza akceptację Regulaminu w całości. Jeśli Użytkownik nie akceptuje Regulaminu, powinien zaniechać korzystania z Usługi.</li>
        <li>Usługodawca zastrzega prawo zmiany Regulaminu z 14-dniowym wyprzedzeniem. O zmianie Użytkownik zostanie powiadomiony drogą e-mail oraz komunikatem w aplikacji.</li>
    </ol>

    <h2>§ 3. Wymagania techniczne</h2>
    <p>Do korzystania z Usługi niezbędne są:</p>
    <ol>
        <li>Urządzenie z dostępem do Internetu (komputer, smartfon lub tablet).</li>
        <li>Aktualna przeglądarka internetowa obsługująca JavaScript (np. Chrome 110+, Safari 16+, Firefox 110+, Edge 110+).</li>
        <li>Aktywny adres poczty elektronicznej.</li>
    </ol>

    <h2>§ 4. Rejestracja i konto</h2>
    <ol>
        <li>Rejestracja konta jest bezpłatna i wymaga podania adresu e-mail, hasła oraz danych firmy.</li>
        <li>Użytkownik zobowiązuje się do podania prawdziwych danych i ich aktualizowania.</li>
        <li>Użytkownik odpowiada za poufność hasła i wszelkie działania wykonane na jego koncie.</li>
        <li>Jedno konto odpowiada jednej firmie (jednej subdomenie). Tworzenie wielu kont przez ten sam podmiot w celu obejścia ograniczeń jest zabronione.</li>
        <li>Użytkownik może w każdej chwili usunąć konto, wysyłając wiadomość na adres <a href="mailto:kontakt@tbasystent.pl">kontakt@tbasystent.pl</a>. Dane zostaną usunięte w ciągu 30 dni.</li>
    </ol>

    <h2>§ 5. Zakres i dostępność Usługi</h2>
    <ol>
        <li>Usługa obejmuje m.in.: zarządzanie zleceniami i klientami, tworzenie wycen z pomocą AI, rejestrowanie notatek i nagrań głosowych, planowanie harmonogramu, generowanie raportów.</li>
        <li>Usługodawca dąży do dostępności Usługi 24/7, lecz nie gwarantuje jej nieprzerwanego działania. Planowane przerwy techniczne będą komunikowane z wyprzedzeniem.</li>
        <li>Usługa jest udostępniana <strong>w stanie, w jakim się znajduje (as-is)</strong>. Mogą w niej wystąpić błędy. Usługodawca nie ponosi odpowiedzialności za szkody wynikłe z awarii lub błędów Usługi.</li>
        <li>Usługodawca zastrzega prawo do modyfikacji zakresu Usługi, wycofania funkcji lub zawieszenia dostępu z uzasadnionych przyczyn technicznych lub prawnych.</li>
    </ol>

    <h2>§ 6. Okres próbny i opłaty</h2>
    <ol>
        <li>Każde nowe konto otrzymuje bezpłatny okres próbny trwający 30 dni od daty rejestracji. W tym czasie dostęp do wszystkich funkcji Usługi jest pełny i nieograniczony.</li>
        <li>Po upływie okresu próbnego dostęp do Usługi jest płatny według aktualnego cennika opublikowanego na stronie tbasystent.pl. Aktualny abonament wynosi <strong>50 zł netto miesięcznie</strong>.</li>
        <li>Opłaty są pobierane z góry za każdy miesiąc rozliczeniowy. Usługodawca poinformuje Użytkownika o konieczności dokonania płatności przed upływem okresu próbnego.</li>
        <li>Usługodawca zastrzega prawo zmiany cennika z co najmniej 30-dniowym wyprzedzeniem. O zmianie cen Użytkownik zostanie powiadomiony drogą e-mail.</li>
        <li>Użytkownik ma prawo do eksportu swoich Danych przed zakończeniem subskrypcji lub usunięciem konta.</li>
    </ol>

    <h2>§ 7. Obowiązki Użytkownika</h2>
    <p>Użytkownik zobowiązuje się do:</p>
    <ol>
        <li>Korzystania z Usługi zgodnie z prawem, Regulaminem i dobrymi obyczajami.</li>
        <li>Nienaruszania praw osób trzecich, w szczególności praw autorskich i danych osobowych.</li>
        <li>Nieupubliczniania danych swoich klientów za pośrednictwem Usługi w sposób sprzeczny z przepisami o ochronie danych osobowych (RODO).</li>
        <li>Niezakłócania działania Usługi (m.in. zakaz ataków DDoS, scraping, nadmiernego obciążania serwerów).</li>
        <li>Niezamieszczania treści nielegalnych, obraźliwych lub wprowadzających w błąd.</li>
    </ol>

    <h2>§ 8. Przetwarzanie danych osobowych</h2>
    <ol>
        <li>Administratorem danych osobowych Użytkownika jest Usługodawca (dane w § 1 pkt 1).</li>
        <li>Dane osobowe są przetwarzane w celu świadczenia Usługi, kontaktu z Użytkownikiem i ulepszania Usługi — na podstawie art. 6 ust. 1 lit. b i f RODO.</li>
        <li>Dane klientów wprowadzane przez Użytkownika do Usługi są przetwarzane przez Usługodawcę jako podmiot przetwarzający (procesor) w rozumieniu art. 28 RODO. Użytkownik pozostaje administratorem tych danych wobec swoich klientów.</li>
        <li>Szczegóły dotyczące przetwarzania danych, praw osób, których dane dotyczą, oraz stosowanych środków bezpieczeństwa opisuje <a href="/polityka-prywatnosci">Polityka prywatności</a>.</li>
    </ol>

    <h2>§ 9. Funkcje AI i ograniczenia odpowiedzialności</h2>
    <ol>
        <li>Usługa korzysta z zewnętrznych modeli języka (m.in. Anthropic Claude, OpenAI Whisper). Wyniki generowane przez AI mają charakter sugestii i mogą zawierać błędy.</li>
        <li>Użytkownik ponosi wyłączną odpowiedzialność za weryfikację i zatwierdzanie treści generowanych przez AI przed wysłaniem ich do klientów.</li>
        <li>Usługodawca nie ponosi odpowiedzialności za szkody wynikłe z zastosowania niezweryfikowanych sugestii AI.</li>
        <li>Dane głosowe (nagrania) przekazywane do transkrypcji są przetwarzane przez OpenAI Whisper API. Nagrania nie są przez Usługodawcę archiwizowane dłużej niż to konieczne do wykonania transkrypcji.</li>
    </ol>

    <h2>§ 10. Reklamacje</h2>
    <ol>
        <li>Reklamacje dotyczące Usługi należy składać na adres <a href="mailto:kontakt@tbasystent.pl">kontakt@tbasystent.pl</a> z tematem „Reklamacja".</li>
        <li>Reklamacja powinna zawierać: imię i nazwisko lub nazwę firmy, adres e-mail konta, opis problemu oraz datę jego wystąpienia.</li>
        <li>Usługodawca rozpatruje reklamacje w terminie 14 dni roboczych od ich otrzymania i informuje o wyniku drogą e-mail.</li>
    </ol>

    <h2>§ 11. Rozwiązanie umowy</h2>
    <ol>
        <li>Użytkownik może w każdej chwili zrezygnować z Usługi, usuwając konto (kontakt: <a href="mailto:kontakt@tbasystent.pl">kontakt@tbasystent.pl</a>).</li>
        <li>Usługodawca może zawiesić lub usunąć konto Użytkownika, który narusza Regulamin, po uprzednim wezwaniu do zaprzestania naruszeń (z wyjątkiem rażących naruszeń, gdy zawieszenie następuje natychmiastowo).</li>
        <li>Usługodawca zastrzega prawo do zakończenia świadczenia Usługi z 30-dniowym wyprzedzeniem.</li>
    </ol>

    <h2>§ 12. Postanowienia końcowe</h2>
    <ol>
        <li>Regulamin podlega prawu polskiemu.</li>
        <li>Spory wynikłe z korzystania z Usługi będą rozstrzygane przez sąd właściwy dla siedziby Usługodawcy, z zastrzeżeniem przepisów o właściwości sądu dla konsumentów.</li>
        <li>Usługa skierowana jest do przedsiębiorców. Przepisy o ochronie konsumentów stosuje się wyłącznie w zakresie, w jakim Użytkownik jest konsumentem w rozumieniu Kodeksu cywilnego.</li>
        <li>W sprawach nieuregulowanych Regulaminem stosuje się przepisy Kodeksu cywilnego i ustawy o świadczeniu usług drogą elektroniczną.</li>
    </ol>

    <p style="margin-top: 48px; font-size: 13px; color: rgba(255,255,255,0.25);">
        Pytania? Pisz na <a href="mailto:kontakt@tbasystent.pl">kontakt@tbasystent.pl</a>
    </p>

</div>

@include('partials.public-footer')

@endsection
