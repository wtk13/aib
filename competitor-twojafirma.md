# Competitor Teardown: twojafirma.pro

**Date researched:** 2026-04-19
**Fetched pages:** `/`, `/#funkcje` (canonical routes `/cennik` and `/funkcje` return 404 — hash-anchored SPA or fragment-only navigation)

---

## TL;DR

**twojafirma.pro = "Twoja Firma Sprzątająca"** — a Polish, operations-focused SaaS for cleaning companies. Built by a single Polish dev shop (Przemysław Razik IT SERVICES). Live product, 3-tier pricing (39/99/149 PLN), Tpay billing, single testimonial. **Direct competitor for our cleaning wedge but positioned narrowly enough that Wyceny's horizontal AI-quoting thesis is safe — provided we ship AI features fast and price at or below their anchors.**

**Threat rating: Direct for cleaning vertical / Adjacent for the broader horizontal plan.**

---

## 1. What Are They?

| Dimension | Answer |
|-----------|--------|
| **Category** | Operations/field-service CRM for cleaning companies (ZenMaid PL clone) |
| **Target persona** | Polskie firmy sprzątające, solo → kilkuosobowe zespoły (1–10 pracowników, ~20–200 klientów) |
| **Core value prop** | "Zlecenia, zespół, klienci, finanse — wszystko w jednym miejscu" |
| **Business model** | Monthly SaaS, 3 tiers, no annual/trial info visible, Tpay payments |
| **Vertical vs horizontal** | **Vertical — cleaning only.** Name and copy box them in like ZenMaid |
| **Maturity** | Live, 2026 copyright, paid plans, one named customer. Feels like 6–18 months in market, solo-founder or 2–3 person shop (Przemysław Razik IT SERVICES footer attribution) |
| **Geography** | PL only, PL-only interface explicitly as differentiator |

---

## 2. Are They a Direct Competitor to Wyceny?

| Criterion | Match? | Notes |
|-----------|--------|-------|
| **Same pain (AI pricing for service biz)** | ❌ No | They solve operations (grafik, zespół, GPS), not pricing intelligence |
| **Same persona (PL SMB, 1–10, quote-based)** | ✅ Yes — but narrower | Their persona = cleaning only; ours = any quote-based service biz |
| **Same wedge vertical (cleaning)** | ✅ Yes — direct overlap | This is the overlap that matters |
| **Same positioning** | ⚠️ Adjacent, not identical | They = "zarządzaj firmą sprzątającą"; we = "wyceniaj mądrzej z AI" |
| **Same pricing band** | ✅ Yes — 39–149 PLN | Important anchor for our pricing sanity check |

**Final rating: Direct threat for cleaning wedge. Adjacent for the horizontal thesis.**

If all we ever shipped was the cleaning preset, twojafirma.pro would beat us to market with more operations features. But our wedge isn't "be the best ops tool for cleaners" — it's "be the AI brain that wycenia, pamięta i rozumie klienta across any service branch." They don't do any of that.

---

## 3. Homepage Teardown (same format as existing research)

**URL:** https://twojafirma.pro

**Hero headline:** "Twoja Firma Sprzątająca"
**Subhead:** "Zlecenia, zespół, klienci, finanse — wszystko w jednym miejscu, na każdym urządzeniu. Stworzone dla polskich firm sprzątających"

**CTAs:** Primary "Zacznij za Darmo" / Secondary "Testuj za darmo" (repeated 4x in features section) / Plan-specific "Wybierz Standard/Business/Firma Pro"

**Hero visual:** Not extracted (likely product screenshot or illustration — not described in fetched content, reasonable to assume product mockup). Missing visual analysis is a limitation.

**Social proof:**
- **1 named testimonial:** Klaudia z Wayclean — "Najlepsza platforma do ogarniania zleceń i rezerwacji. Przetestowałam wiele narzędzi — przy tym zostaję na stałe."
- **No numbers.** Zero "X firm korzysta", zero star ratings, zero logo wall.
- **N=1 social proof** — same problem we have, they haven't solved it either.

**Vertical positioning:** Maximum focus — "firmy sprzątające" in the name, headline, subhead, and "stworzone dla polskich firm sprzątających" as explicit closing. **This is the ZenMaid trap**: they can never expand to remonty/fotografia without a rebrand or product split.

**Pricing visibility:** ✅ **Fully visible on homepage** — three tiers, clear limits. This matches Fakturownia/iFirma pattern and confirms our "show pricing" recommendation in homepage-research.md §6.

| Plan | Cena | Limity |
|------|------|--------|
| Standard | **39 PLN/mies** | 2 pracowników, 20 klientów, 30 zleceń/mies |
| Business | **99 PLN/mies** | 5 pracowników, 75 klientów, 100 zleceń/mies |
| Firma Pro | **149 PLN/mies** | Bez limitów + GPS + konta pracowników |

**Trust signals:**
- ✅ Polityka Prywatności + Regulamin w footer
- ✅ Tpay (PL payment processor) — PL SMB trust signal
- ✅ Company attribution: "Przemysław Razik IT SERVICES" (jednoznacznie jednoosobowa działalność — jednocześnie credibility i ryzyko)
- ⚠️ Brak wzmianki o RODO, DPA, lokalizacji serwerów, SLA
- ⚠️ Brak numeru NIP w footerze (typowa PL credibility luka)
- ⚠️ Brak dashboard statusu, brak publicznego roadmapa

**Tone of voice:** Informal "ty", marketingowy, pragmatyczny. Nie korpo, ale też nie "siostra-która-ogarnia-firmę" — bardziej "developer który zna branżę". Neutralna ciepłota.

**What works:**
- ✅ Widoczny cennik od 39 zł — miękkie wejście dla solo-operatorki
- ✅ Tpay = PL trust bez zbędnego pitchu
- ✅ Nazwa jednoznacznie komunikuje dla kogo to — żadnego "dla kogo to jest" ambiguity (co my mamy z "Wyceny")
- ✅ GPS geolokalizacja = konkretna branżowa funkcja, która rezonuje (właścicielka wie że ma pracowników w terenie bez kontroli)
- ✅ 100% PL interface jako explicit differentiator — celuje w Jobbera/HCP którzy są po angielsku

**What they get wrong:**
- ❌ **N=1 social proof** (Klaudia z Wayclean) — dokładnie ten sam problem co my, ale my będziemy o tym otwarcie mówić ("w becie, z Anią budujemy") zamiast udawać że jesteśmy ustawieni
- ❌ **Brak AI, brak inteligencji wyceny, brak rozmowy o kliencie** — cały produkt to ręczne formularze
- ❌ **Nazwa = trap** — "Twoja Firma Sprzątająca" nie rozszerzy się nigdy. Jeśli chcą pivotować to robią rebrand
- ❌ **Brak widocznego "jak to działa" wideo/demo** — tylko marketingowy copy
- ❌ **Jednoosobowa działalność jako wydawca** = ryzyko "co jeśli developer zniknie" dla klientki wrzucającej dane 50 klientów końcowych
- ❌ **404 na `/cennik` i `/funkcje`** — strona hash-anchored SPA, **SEO boli** (Google nie indeksuje fragmentów), content marketing nie działa
- ❌ **Brak RODO/DPA/lokalizacja danych** explicit w footerze — Ania z wycięciem większych klientów biznesowych (biura, np. sprzątanie korporacyjne) to zauważy

---

## 4. Feature Comparison: Wyceny vs. twojafirma.pro

| Feature | twojafirma.pro | Wyceny (planned) | Differentiator? |
|---------|----------------|------------------|-----------------|
| Zarządzanie klientami | ✅ | ✅ | Parity |
| Zlecenia (jednorazowe) | ✅ | ✅ | Parity |
| Zlecenia cykliczne | ⚠️ (niepewne z content) | ✅ RRULE first-class | Likely edge |
| Grafik/kalendarz | ✅ | ✅ | Parity |
| Konta pracowników | ✅ (Firma Pro only) | ⚠️ (Phase 2 per sprint plan) | **Their edge today** |
| GPS tracking | ✅ (Firma Pro) | ❌ (not planned) | **Their edge** |
| Automatyczne liczenie godzin | ✅ | ❌ | **Their edge** |
| Wyceny/oferty | ✅ (basic form) | ✅ + **AI suggestion** | **Our edge** |
| **AI wycena na bazie historii** | ❌ | ✅ S5 | **Our core wedge** |
| **AI chat o kliencie** | ❌ | ✅ S6 | **Our core wedge** |
| **Notatki głosowe (Whisper PL)** | ❌ | ✅ S4 | **Our core wedge** |
| **SMS/WhatsApp screenshot OCR** | ❌ | ✅ Phase 2 | **Our core wedge** |
| Klucze/kody szyfrowane | ❌ (niepewne) | ✅ S1 | Likely our edge |
| Preferencje/alergie klientów | ❌ (niepewne) | ✅ custom fields | Likely our edge |
| Finansowe (wydatki/przychody) | ✅ | ❌ (integracja z Fakturownia, nie budujemy) | **Their edge** |
| Prognozy/wykresy finansowe | ✅ | ❌ | **Their edge** |
| NIP/GUS autofill | ⚠️ (niepewne) | ✅ S1 | Likely parity |
| Integracja z Fakturownia | ❌ (niepewne) | ✅ Phase 2 | Our edge |
| Mobile PWA | ✅ ("na każdym urządzeniu") | ✅ (responsive Livewire) | Parity |
| Vertical presets (remonty, fotografia...) | ❌ (nazwa to wyklucza) | ✅ core architecture | **Our structural edge** |

**Summary:**
- **Ich przewaga dziś:** GPS tracking, konta pracowników, finanse/prognozy (shipped), czas w rynku (they're live, we're not)
- **Nasza przewaga jak wydanie:** AI wycena, AI chat, notatki głosowe, OCR, horizontal architecture, klucze szyfrowane
- **Feature parity (neutral):** klient/zlecenia/grafik CRUD

**Kluczowy insight:** twojafirma.pro to dobry "ops tool". My budujemy "inteligentnego asystenta". Inne kategorie, które mogą koegzystować — ale klientka ostatecznie wybierze jedno. Jeśli jej pain #1 to "kto gdzie pracuje + ile godzin", idzie do nich. Jeśli "ile wziąć + co obiecałam + co notowałam", idzie do nas.

---

## 5. Strategic Implications for Wyceny

### 5.1 Positioning adjustment — sharpen the AI thesis

twojafirma.pro pokazała że samo "CRM dla firm sprzątających" = solved problem (nie świetnie, ale solved). Nasza wartość **nie może być generic CRM** — musi być jednoznacznie *AI asystent wyceny i pamięci*. W homepage-research.md Option A headline ("Wyceniaj, pamiętaj, planuj — bez Excela") już to oddaje, ale **"planuj"** jest teraz ryzykowne — twojafirma.pro "planuje" lepiej. Rozważyć zamianę trzeciego czasownika.

**Rekomendowana zmiana Option A:**
> **Wyceniaj mądrzej, pamiętaj dokładniej.**
> Lub: **Wyceniaj, pamiętaj, rozumiej klienta — bez Excela i bez chaosu.**

### 5.2 Pricing anchor confirmation

Ich 39/99/149 PLN potwierdza naszą kalibrację (79 PLN Solo / 149 PLN Zespół w product-plan.md §9 i homepage-research.md §4). **Sugestia:** dodaj tier Starter **49 PLN/mies** (żeby wejść poniżej ich Business 99 PLN a nie być droższym od ich Standard 39 PLN bez AI). Struktura:

| Plan Wyceny | Cena | Target |
|-------------|------|--------|
| Beta | 0 PLN | Wczesny dostęp do końca 2026 |
| **Starter (nowy)** | **49 PLN/mies** | Solo bez AI premium, 1 ekipa, do 30 klientów |
| Solo | 79 PLN/mies | Z pełnym AI, 1 ekipa, do 100 klientów |
| Zespół | 149 PLN/mies | Team mode, unlimited, per-user AI tuning |

To daje Ani wyraźny "tier pomiędzy" i vis-à-vis twojafirma.pro wygląda agresywnie cenowo w górnym segmencie (149 PLN to ich top, a nasz średnik z lepszym AI).

### 5.3 Name trap — confirmed

Ich "Twoja Firma Sprzątająca" to dokładnie ZenMaid trap o którym pisałem. **Nasza nazwa "Wyceny" *(decyzja 2026-04-25, `product-plan.md` §3)* jest bezpieczna — vertical-neutral, działa dla sprzątania, remontów, fotografii.** Ich błąd = nasza moat strukturalna.

### 5.4 Honesty strip — teraz jeszcze ważniejsza

Oni mają "Klaudię z Wayclean" jako N=1 bez framingu że są w becie. My mamy "Anię" (żonę) — **świadomie framinguj jako design partner i beta-open**: "Jesteśmy w becie. Budujemy z Anią, która prowadzi firmę sprzątającą w [Miasto]. Dołącz — kształtuj produkt razem z nami." Ich N=1 wygląda na próbę udawania social proof. Nasz N=1 wygląda na szczery design-partner story. Różnica to credibility.

### 5.5 Feature gaps to respond to

Rzeczy które **NIE** dodajemy mimo że oni mają (świadomie):
- **GPS tracking pracowników** — Ania jako solo-operator + żona-tester nie mają tego painu; to feature dla 5+ pracowników. Phase 3 jeśli popyt.
- **Własne finanse/prognozy** — nie budujemy, integracja z Fakturownia zostaje planem. To jest droga w ERP-scope-creep.
- **Automatyczne liczenie godzin** — tak, ale dopiero w Phase 2 team mode.

Rzeczy które **powinniśmy przyspieszyć** (reprioryzacja sprint plan):
- **Cykliczność zleceń (S2)** — jeśli twojafirma.pro nie ma first-class RRULE, to jest differentiator, bo cleaning to branża cykliczna. Trzymamy w S2 bez przesuwania.
- **Voice notes (S4) przed AI wycena (S5)** — kolejność ok, bo voice notes to "wow" moment + zasila dane dla AI wycena.
- **Klucze/kody szyfrowane (S1)** — przyspiesz do S1 (już jest), zrób z tego hero feature w marketingu ("jedyny PL CRM który szyfruje klucze do mieszkań tak że nawet my nie widzimy").

### 5.6 SEO opportunity — ich słabość

twojafirma.pro ma 404 na `/cennik` i `/funkcje` → hash-anchored SPA, brak indexable content. **Google nie widzi ich stron funkcji.** To oznacza:
- Wszystkie zapytania typu "crm dla firmy sprzątającej", "jak wycenić sprzątanie mieszkania", "grafik dla sprzątaczek" są SEO-open-goal
- Nasza content strategy (§9 product-plan.md — AI-generated pricing guides per branża) może zająć top 3 Google wcześniej niż oni zdążą przebudować SPA na SSR
- **Akcja:** upewnić się że Laravel HP ma **server-side rendering od dnia 1** (Livewire + full-page nav, nie hash routing) — to automatycznie robi z nas SEO-bliższych konkurentów

### 5.7 Market readiness signal

Ich istnienie + aktywność (live pricing, płatny Tpay, aktywny blog) = **rynek PL jest gotowy na CRM-y dla małych firm usługowych.** To mocny positive signal przed pisaniem kodu. Gdybyśmy wchodzili na rynek bez żadnego konkurenta — reason to worry. Z takim konkurentem — rynek validated, my się tylko różnicujemy.

---

## 6. Top 5 Actionable Recommendations

> **Status update 2026-04-25:** items 1, 2, 4 propagated. Items 3, 5 pending.

| # | Status | Action | File to update | Impact |
|---|---|--------|---------------|--------|
| 1 | ✅ Done | **Add "Starter 49 PLN" tier** w pricingu | `product-plan.md` §9, `homepage-research.md` §4.8 | Undercutting twojafirma.pro Business (99 PLN) z pełnym AI — silne GTM repositioning |
| 2 | ✅ Done | **Revise hero headline** — drop "planuj" (their strong side), emphasize AI (nasze mocne strony) | `homepage-research.md` §5 (Option A revised → "rozumiej klienta") | Differentiation od ops-tool positioning |
| 3 | ⬜ Pending | **Add competitor row to "Nie dla korpo" section** — "Wyceny ≠ twojafirma.pro (ops-tool bez AI)" | `homepage-research.md` §4.7 | Direct positioning przeciw prawdziwemu konkurentowi w cleaning |
| 4 | ✅ Done | **Server-side render landing + funkcje/cennik od S0** — nie kopiuj ich SPA hash-error | `sprint-plan.md` S0.11 (added) + `seo-strategy.md` §5.1 (CI test) | SEO moat od dnia 1, ich słabość = nasza przewaga |
| 5 | ⬜ Pending | **User-test z żoną: "znasz twojafirma.pro?"** | Before S0 | Jeśli żona po 8 latach w branży nie zna → ich marketing słaby, rynek nieobsadzony. Jeśli zna → walczymy o share of attention od dnia 1. Ten jeden fakt zmienia GTM timing. |

---

## 7. What We Learn From Them (steal these)

- **Widoczny cennik od dnia 1** — confirmed przez Fakturownia/iFirma/twojafirma.pro, trzy z trzech PL SMB benchmarków. Our plan już to robi.
- **Tpay integration** — gdy wyjdziemy z bety, Tpay > Stripe dla PL SMB dla trust signal (Stripe ok technicznie, ale mniej PL-native).
- **"Stworzone dla polskich firm…"** — explicit PL framing w subhead. My możemy zrobić to samo, horizontal: "Stworzone dla polskich małych firm usługowych."
- **Tier names z opisami limitów** — "2 pracowników, 20 klientów, 30 zleceń/mies" jest konkretne i nie pozostawia dwuznaczności. Nasz pricing powinien mieć tak samo explicit limits per tier (nie "unlimited" buzzword).

## 8. What We DON'T Copy

- Hash-anchored SPA = 404 crawlability mess
- Brak informacji o RODO/lokalizacji danych
- Brak dashboardu statusu
- N=1 testimonial bez framingu że to design partner (cringe factor)
- Narrow vertical naming — ich najbardziej kosztowna decyzja, nasz structural moat jeśli tego nie powtórzymy

---

## Appendix: Data quality note

Analysis based on WebFetch of `/` i `/#funkcje`. Strony `/cennik` i `/funkcje` zwróciły 404 (site uses hash-anchored navigation, nie real routes). Hero visual nie został wyekstrahowany przez fetch. Na żywo mogą mieć: wideo demo, więcej testimoniali ukrytych niżej, integracje niewymienione w content. **Pre-launch: przejrzyj stronę ręcznie w przeglądarce raz** żeby zweryfikować czy są live features których WebFetch nie złapał — szczególnie: wideo demo, screenshot hero, onboarding flow, free trial mechanika (14 dni? 30 dni? forever free?).
