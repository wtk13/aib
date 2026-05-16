# TBA / Twój Biznes Asystent — Product Vision

**Date:** 2026-05-16
**Brand:** TBA (panel short name) / Twój Biznes Asystent (full PL name) / `tbasystent.pl`
**Status:** Active — supersedes prior "Wyceny" and "Twój Asystent" product framing

---

## What It Is

TBA (Twój Biznes Asystent) is a personal AI business assistant for independent service professionals. It is not a tool you operate — it is a partner that knows your business and helps you run it better, day by day.

The application replaces the cognitive overhead of running a service business solo: tracking clients and their needs, planning the work week, pricing new jobs fairly, and noticing patterns you would otherwise miss.

---

## Primary User (Now)

One-person cleaning business. The assistant knows every client — their home, area, access codes, preferences, pricing history. It tells you what is on today, flags a client who has not rebooked in six weeks, and proposes what to charge for a new job based on similar work done before.

---

## Core Experience

**Opening the app**, you see your day: today's jobs, drive times, who still needs attention. You did not ask for any of this — it is just there.

**After a first visit to a new client**, you add a short note: *"90m² apartment, cat, needs deep clean first."* The assistant reads that note alongside the client's area and property type and proposes three prices: one-time, monthly, weekly. Each price accounts for the drive to that client. You adjust if needed and send the quote.

**Over time**, the assistant learns: which jobs run over time, which clients are more work than the price suggests, which months are slow. It surfaces these patterns as alerts.

---

## Experience Pillars

1. **Day at a glance** — dashboard is the home screen. Today's jobs, schedule, revenue, who needs attention. No digging required.
2. **Business memory** — full client + job + note history, semantically searchable.
3. **Smart pricing** — quote suggestions grounded in real job history + commute cost.
4. **Commute-aware** — every price and every plan accounts for where clients are relative to home base.
5. **Proactive alerts** — surfaces things you would otherwise miss: overdue clients, jobs running over estimate, slow months ahead.

---

## Assessment → Quote Flow (core workflow)

1. Create client → enter area, property type, access notes
2. First visit → add assessment note (free text)
3. Assistant reads notes + client data + historical job data → proposes pricing at 3 frequencies (one-time, monthly, weekly)
4. Each price includes estimated round-trip commute cost (distance × fuel rate per km, set in tenant settings)
5. User adjusts if needed → quote generated
6. Quote sent to client (link, PDF, or message)

---

## Commute as a Pricing Factor

The assistant knows where the owner lives (home base address, stored in tenant settings). When viewing a new client or creating a job, it calculates:

- Straight-line distance to client (km)
- Round-trip commute cost (km × 2 × fuel rate PLN/km)

This cost is factored into every pricing suggestion and shown as a separate line in quotes.

For frequent clients, commute cost is amortized across more visits — making far clients relatively cheaper to serve weekly than monthly.

Example display on a new client:

```
📍 ul. Mokotowska 12, Warszawa
   ~18 km from home base · ~28 PLN round trip (fuel)

Suggested pricing:
  One-time deep clean:    410 PLN  (job 380 + commute 30)
  Monthly (1×/month):     320 PLN/visit
  Weekly  (4×/month):     260 PLN/visit
```

---

## Architecture Principles

**Preset = vertical knowledge.** The cleaning preset defines service types, pricing factors, custom fields, and AI hints for one trade. Replacing the preset generalizes the assistant to a different trade without rewriting the product. See ADR-005 and ADR-016.

**Notes are AI input.** Visit notes are the AI's primary input. They are stored with vector embeddings (`note_embeddings`, pgvector) so the AI can find clients similar to a new one and reason about pricing from description alone.

**Usage before AI.** AI features are grounded in real data. The assistant gets smarter as the user builds job history, completes jobs, and accepts or adjusts pricing suggestions. The operational layer (Sprints 1–2) must be in use before AI features (Sprint 3+) deliver real value.

**Start simple, stay open.** Built for one cleaning business today. The preset engine and module boundaries ensure generalizing to other service trades later is evolution, not a rewrite. See option C rationale in brainstorming session 2026-05-16.

**The code name stays.** The internal code name `wyceny` (used in slugs, DB seeds, config keys) is retained as a technical artifact. It is not user-facing.

---

## Sprint Roadmap (high-level)

| Sprint | Theme | Deliverable |
|--------|-------|-------------|
| 0 | Foundation | Tenancy, Docker, CI, preset engine, all DB tables |
| 1 | Clients | Client CRUD, registration, notes, NIP autofill, geocoding |
| 2 | Operations | Jobs, scheduling, dashboard home screen, commute display, tenant settings |
| 3 | AI Pricing | Assessment → price proposal, pricing suggestions, quote generation |
| 4 | Quotes & PDF | Quote resource, PDF generation, share-by-link, quote status flow |
| 5 | Intelligence | Proactive alerts, client comparison, revenue analytics, note embeddings |

---

## Name

**Twój Biznes Asystent** ("Your Business Assistant" in Polish). Panel short name: **TBA**. Domain: `tbasystent.pl` (confirmed available 2026-05-16). The name signals a relationship, not a tool category — direct, warm, AI-first positioning clear from the first word.
