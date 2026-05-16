# Product Plan: TBA — Twój Biznes Asystent

> **Brand:** **TBA** (panel name) / **Twój Biznes Asystent** (full Polish name) / domain: `tbasystent.pl`
> **Positioning:** Horizontal product for **any service business that quotes jobs** (sprzątanie, remonty, fotografia, korepetycje, przeprowadzki, DJ-e, ogrodnictwo, itd.).
>
> **First vertical wedge:** firmy sprzątające — Ania (design partner / user #1) runs one and provides daily feedback from day 1.

## 1. Product Vision

CRM + AI asystent dla małych firm usługowych, który:
- **Pamięta** każdego klienta, zlecenie, notatkę, rozmowę
- **Wycenia** nowe zlecenia na podstawie historii + kontekstu (miasto, dojazd, zakres)
- **Planuje** grafik, dojazdy, ekipy
- **Automatyzuje** nudne: transkrypcje rozmów, PDF-y wycen, przypomnienia

**Architectural principle:** **Generic core + vertical presets.** Dane klienta, zlecenia, wyceny, notatki są uniwersalne. Słownictwo, pola dodatkowe i szablony wycen to konfiguracja per branża.

## 2. Primary Persona (horizontal definition)

**"Właściciel jednoosobowej/małej firmy usługowej"**
- 1–10 osób, obrót 10k–500k PLN/mies
- Wykonuje wyceny regularnie, bo każde zlecenie jest inne
- Zarządza firmą dziś przez: Excel + WhatsApp + kalendarz Google + głowę
- Traci czas na: wyceny, szukanie historii klienta, grafik ekip, przypomnienie sobie "co mu ostatnio obiecałem"

**Sub-persona dla Phase 1 — "Ania" (cleaning):** 35–50, właścicielka firmy sprzątającej, 1–8 sprzątaczek, 30–150 klientów. Nieograniczony dostęp przez małżeństwo — nasze złoto dla product discovery.

**Inne sub-persony (Phase 2+):**
- "Michał" — firma remontowa, 2–5 osób, wyceny po obejrzeniu lokalu
- "Kasia" — fotograf ślubny, każde wesele inna wycena
- "Paweł" — korepetytor/mała szkoła językowa, pakiety godzin
- "Tomek" — firma przeprowadzkowa, wycena po m³ + dystans
- "Magda" — DJ/event, wycena po godzinach + dojazd + sprzęt

## 3. Name Candidates (horizontal, vertical-neutral)

Nazwa **NIE MOŻE** być branżowa (odpada Szczotka/Klucznik) — musi służyć fotografowi, remontowcowi, DJ-owi tak samo.

| Name | Angle | Notes |
|------|-------|-------|
| **Kwotka** | "wycena" + dim. | PL-native, ciepła, jednoznacznie o wycenach, vertical-neutral |
| **Wycenio** | verb-as-brand | Clear, akcyjna |
| **Stawka** | rate/bet | Krótka, mocna, `.ai` TLD realistic |
| **Klientor** | client operator | Bardziej "CRM", mniej "wycena" |
| **Rzetelnik** | reliable one | Brand positioning na zaufanie |
| **Notowano** | "it was noted" | Na notatki/pamięć jako hero |
| **Ofertki** | małe oferty | Soft, mały biznes feel |

**Original recommendation (2026-04-19):** Kwotka lub Stawka — wycofane.

**Decision (2026-04-25):** **Wyceny** @ **`wyceny.app`** — later revised.
- "Kwotka" — diminutywa "kwoty" niejednoznaczna; brak instant clarity co produkt robi
- "Stawka" — overloaded (zakłady / godzinowa stawka / pozycja)
- Verb-direction (`wyceniaj.pl`, `wyceniam.pl`) niedostępne; `wycena.ai` odrzucone (AI-suffix dating fast)
- "Wyceny" = plural noun + `.app` TLD = czytelne SaaS, ages well, brandable

**Final decision (2026-05-16):** **Twój Biznes Asystent** @ **`tbasystent.pl`** (confirmed available).
- Panel brand name: **TBA** (short form, permanent)
- "Wyceny" fully retired — not user-facing anywhere
- Internal code artifact `wyceny` (slugs, preset key) retained as technical identifier only

## 4. Strategic Positioning

**Not competing with:** Salesforce, HubSpot, Pipedrive (B2B SaaS sales, zbyt drogie/ogólne/EN).

**Loosely competing with:** Jobber, Housecall Pro, ServiceTitan (US, field service, drogie, nie znają PL realiów).

**Directly competing with:** Excel + Google Calendar + WhatsApp + głowa.

**Wedge proposition:** *"CRM dla małych firm usługowych — wycenia, pamięta i planuje za Ciebie."*

**Moat over time:**
1. **Per-user AI pricing model** trenowany na historii danej firmy → im dłużej używasz, tym lepiej wycenia → switching cost rośnie
2. **Vertical preset library** — im więcej branż w bazie presetów, tym szybciej nowa firma startuje
3. **PL-native integrations** (GUS, Fakturownia, BLIK, Przelewy24) — bariera dla graczy zagranicznych

## 5. MVP Scope (Phase 1 — 8–12 weeks)

> Budujemy core uniwersalny + preset "sprzątanie" jako pierwszy. Żona testuje codziennie.

**Generic core (działa dla każdej branży):**
- Auth + onboarding z NIP autofill (GUS API)
- **Klient:** podstawowe dane, adres, **custom fields per vertical** (cleaning: klucze/kody/alergie; remont: typ lokalu, stan; fotograf: typ eventu)
- **Zlecenie:** jednorazowe vs. cykliczne, typ usługi (konfigurowalny per vertical)
- **Wycena:** pozycje, stawki, rabaty, PDF eksport z brandingiem
- **Notatki:** tekstowe + głosowe → Whisper transkrypcja (PL/EN)
- **Kalendarz/grafik:** widok tygodniowy, drag & drop
- **Hero AI #1 — Wycena:** sugestia ceny na bazie historii klienta + typu zlecenia + dojazdu
- **Hero AI #2 — Chat o kliencie:** "co ostatnio u pani Kowalskiej?" — AI czyta historię i odpowiada
- **Dojazd + paliwo** (Google Distance Matrix + cena paliwa)

**Vertical preset "sprzątanie" (pierwszy, bo żona):**
- Pola klienta: m², typ lokalu, klucze/kod, preferencje/alergie
- Typy zleceń: podstawowe / generalne / po remoncie / okna / pranie
- Cykliczność: 1x/tydzień, 2x/tydzień, 2x/mies, 1x/mies
- Szablony wycen: per m² + mnożnik za typ

**Out (explicit cuts):**
- Multi-user/team mode ponad 1 właściciel (Phase 2)
- Własny invoicing → **integracja z Fakturownia** później
- Payments online (Phase 2)
- Native mobile app → responsywny web PWA
- Portal klienta

## 6. Key Generic Features (worth including before launch)

- **📸 Photo attachments per zlecenie** (before/after, dokumenty, umowy) — uniwersalnie przydatne
- **📞 Screenshot OCR** rozmów WhatsApp/SMS → historia w karcie klienta (killer feature, universal)
- **🔁 Cykliczność jako first-class concept** — nie każde zlecenie to nowe zlecenie
- **⚠️ Client risk flags** — AI czyta notatki i flaguje problemowych klientów (negocjatorzy, opóźnienia)
- **📊 Rentowność per klient** — uwzględniając dojazd, czas, reklamacje
- **📅 Auto-follow-up** — "klient X nie wrócił od 3 mies, zaproponować coś?"
- **🔎 Global search** — znajdź cokolwiek w notatkach (pgvector semantic search)
- **🏷️ Tags + segmenty** — konfigurowalne per user (VIP, trudny, sezonowy)
- **📈 Dashboard:** przychody, top klienci, win rate wycen, prognoza miesiąca

## 7. Vertical Preset System (our architectural moat)

Każda branża = preset z:
- **Custom fields** klienta i zlecenia (JSON schema)
- **Service types** (typy usług z default stawkami)
- **Quote template** (jak się składa wycena — per m², per h, per punkt)
- **Pricing AI hints** (jakie cechy brać pod uwagę — m² dla cleaning, stan lokalu dla remontu)
- **Vocabulary** (np. "sprzątanie" vs "sesja" vs "zlecenie" w UI)
- **PDF template** (jak wygląda wycena)

Phase 1: hand-crafted preset "sprzątanie."
Phase 2: 5 presetów (sprzątanie, remonty, fotografia, korepetycje, przeprowadzki).
Phase 3: użytkownicy mogą klonować i edytować presety → community presets.
Phase 4: AI generuje preset z opisu — "prowadzę małą firmę myjni samochodowej" → gotowa konfiguracja.

## 8. Additional Feature Ideas (Phase 2+)

- Route optimization (wiele klientów jednego dnia — dla cleaning, konserwacji, usług mobilnych)
- Waloryzacja cen cykliczna (1-click inflacja X% wszystkim stałym)
- Klient-facing portal (status, historia, płatności)
- Team mode + stawki/payroll dla pracowników
- Integracje: Fakturownia, iFirma, Google Calendar, Stripe, Przelewy24
- Mini-strona firmy (SEO-friendly, "firma sprzątająca Warszawa Mokotów")
- Email/SMS kampanie do klientów (segmenty)
- Public API + Zapier/Make integration

## 9. Go-to-Market Strategy (wedge-based)

**Phase 0 — Design Partner (M1–3):**
- Firma żony = user #1, codzienne użycie, zastępuje jej Excel całkowicie
- Cel: 1 firma używa idealnie przed jakąkolwiek ekspansją

**Phase 1 — Cleaning wedge (M3–6, 5–50 firm):**
- Żona zaprasza znajome właścicielki firm sprzątających (FB groups "Firmy sprzątające Polska" — 10–20k członków)
- Darmowy dostęp za feedback + case study
- Content: Instagram/TikTok żony jako face of product — "jak prowadzę firmę sprzątającą bez chaosu"
- Cel: 50 firm, 80%+ retention, 10 case studies

**Phase 2 — Vertical expansion (M6–12, 200–1000 firm):**
- **Drugi vertical — remonty** (next biggest PL SMB segment with quote complexity)
  - Kanał: FB groups remontowe, Reddit r/polska handyman threads, YT-owcy budowlańcy
- **Trzeci vertical — fotografia eventowa**
  - Kanał: FB groups fotografów, Instagram, grupy branżowe
- Każdy vertical = osobna landing page + preset + case studies, ale **ten sam produkt**
- Pricing (zaktualizowane po `competitor-twojafirma.md` §5.2):
  - **Beta** — 0 PLN/mies (darmo za feedback do końca 2026)
  - **Starter** — 49 PLN/mies (1 user, do 30 klientów, AI sugestie limitowane) — undercut wobec twojafirma.pro Business 99 PLN
  - **Solo** — 79 PLN/mies (1 user, do 100 klientów, pełne AI)
  - **Zespół** — 149 PLN/mies (team mode, unlimited, per-user AI tuning)

**Phase 3 — Horizontal SEO (Year 2):**
- Pełen plan: `seo-strategy.md`. Skrót:
  - Pillar + cluster per branża ("jak wycenić sprzątanie/remont/sesję") z osadzonym kalkulatorem — hand-built, 3 publikacje/tydzień max przed M12
  - **Programmatic SEO nie startuje przed M12** (anti-thin-content guardrails — `seo-strategy.md` §6.3); skala "setki/tysiące stron" to M14–M18, nie M6
  - "CRM dla [branża]" landing pages — hand-edited, jeden per vertical
  - Calculator-first content (`/kalkulator/wycena-sprzatania` itd.) jako linkable assets
- Referral program: miesiąc darmo za polecenie

**Phase 4 — US expansion (Year 2–3):**
- Ten sam produkt, EN i18n, EIN zamiast NIP, presety EN (house cleaning, handyman, photography)
- Pozycjonowanie: tańszy i AI-native konkurent Jobbera dla solo-operatorów

## 10. Technical Plan

**Stack:**
- **Laravel 11** — core
- **Livewire 3 + Filament v3** — admin UI, reactive widoki bez SPA
- **PostgreSQL + pgvector** — dane + semantic search notatek
- **Redis + Horizon** — queues (AI, Whisper, OCR)
- **Tailwind + daisyUI** — styling
- **Claude API** — pricing reasoning, chat o kliencie, transcript cleanup
- **Whisper API** — voice → text PL/EN
- **Claude Vision** — OCR screenshotów WhatsApp/SMS
- **Google Maps / Mapbox** — distance + geocoding
- **GUS REGON API** — NIP autofill PL

**Architectural notes:**
- **Preset system** jako JSON schema w bazie + registry w kodzie
- **Multi-tenant** od dnia 1 (każda firma = tenant, soft-isolated)
- **Event-driven** — każda akcja (create quote, add note, complete job) emituje event → feeds AI context builder
- **Per-tenant AI context cache** — embeddings notatek klienta cache'owane dla szybkiego pricing/chat

**Infra:** Hetzner PL/FIN (RODO-friendly, tanie) + Laravel Forge.

**Unit economics target:** AI cost < 8 PLN/user/mies przy normalnym użyciu → 79 PLN cena = 90%+ gross margin.

## 11. Roadmap (12-month)

| Miesiąc | Milestone |
|---------|-----------|
| **M1** | Auth, NIP lookup, tenant setup, klient + zlecenie CRUD (generic), grafik MVP, preset engine v1 |
| **M2** | Wycena + PDF, notatki głosowe, cykliczność, preset "sprzątanie" pełny, dojazd+paliwo |
| **M3** | AI wycena v1, AI chat o kliencie, **żona używa codziennie jako primary tool** |
| **M4** | Photo attachments, SMS screenshot OCR, preferencje/custom fields, **beta z 5 firmami sprzątającymi** |
| **M5** | Rentowność per klient, auto-follow-up, global search, waloryzacja cen, billing (Stripe) |
| **M6** | **Publiczny launch PL — wedge cleaning**, pricing tiers live, content machine start |
| **M7–8** | Preset "remonty", **druga branża live**, route optimization, Fakturownia integration |
| **M9–10** | Preset "fotografia" + "korepetycje", community presets, klient-portal |
| **M11–12** | Per-user fine-tuned pricing model, team mode, US beta prep (EN i18n, EIN) |

## 12. Key Risks & Open Questions

- **Horizontality trap:** budowanie "dla każdego" = jest dla nikogo. **Mitigacja:** preset system pozwala wyglądać branżowo dla każdej branży, ale kod uniwersalny
- **Design partner N=1 bias:** żona to jeden typ firmy. **Mitigacja:** od M4 walidacja na 5+ firmach przed decyzjami architektonicznymi
- **Second-vertical risk:** działa dla cleaning nie znaczy działa dla remontów. M7 będzie prawdą — jeśli preset "remonty" nie wystarczy, architektura jest wadliwa → refactor
- **RODO/privacy:** dane klientów końcowych wrażliwe (adresy, klucze) — encryption at rest, DPA, rejestr czynności
- **AI pricing liability:** zawsze *sugestia*, nigdy *decyzja* — w UI, w ToS
- **Cold start:** pierwsze 2–3 miesiące dane klienta są cienkie → AI pricing słabsze. **Rozwiązanie:** preset-level benchmarks + vertical community data jako baseline
- **Żona-tester emocje:** "dodaj tę funkcję!" — musisz umieć powiedzieć "nie, bo inne firmy pracują inaczej" bez rodzinnego konfliktu

## 13. North Star Metric

**Weekly Active Quoting per Tenant** — ile wycen firma robi w systemie tygodniowo (z AI pomocą).
Mierzy jednocześnie: retention, aktywność, działanie hero feature, wartość biznesowa.

**Supporting metrics:**
- TTFQ — Time To First Quote (onboarding success)
- AI adoption rate — % wycen gdzie AI sugestia została użyta
- Vertical penetration — ile firm na vertical (sygnał czy preset działa)
- Monthly retention cohorts

---

## Next 3 Steps (this week)

1. **Audit workflow żony** (2h) — zmapuj jej codzienność: kontakt klienta → wycena → grafik → wizyta → płatność → reklamacja. **Zapytaj co ją boli najbardziej** — to wytnie 70% feature'ów z listy.
2. ✅ **Nazwa i domena** *(done 2026-04-25)* — **Wyceny @ `wyceny.app`** (per §3). Akcja pozostała: kupić domenę + zarezerwować social handles (@wycenyapp na X, IG, FB, LinkedIn, TikTok, YouTube)
3. **Narysuj data model** (ERD) — klient, zlecenie, wycena, notatka, ekipa, preset — z myślą o multi-tenant + multi-vertical od dnia 1

Po tym: writing-plans skill → MVP spec na 8 tygodni → start kodu.
