---
name: CRM project context
description: What is being built in /Users/rybinski/Sites/crm — horizontal AI quoting CRM, starting with cleaning as first vertical
type: project
---

**Project:** AI-powered CRM for small service businesses that do quote-based work. Horizontal product, not cleaning-specific — targets any vertical where pricing depends on client history, scope, and context (cleaning, remodeling, photography, tutoring, moving, handyman, etc.).

**Design partner / first user:** Wife's cleaning company (Poland). Primary testing ground through Phase 1. All features should work generically across verticals, but cleaning is the first go-to-market wedge.

**Core product thesis:** AI pricing suggestions from client history + voice notes + client context memory. Competes with Excel + WhatsApp + "w głowie", not with Salesforce.

**Tech stack (per user spec):** PHP / Laravel. Target markets: PL first (NIP/GUS integration, Fakturownia), US later (EIN lookup).

**Why:** User wants to ship a product; wife has real SMB pain + real data + daily feedback loop. Starting narrow with cleaning, but architected for multi-vertical from day one.

**How to apply:** When planning features, prefer configurable/generic over cleaning-specific (e.g., "custom fields per vertical" > hardcoded "klucze/kody"). When discussing GTM, cleaning is wedge #1 but positioning copy shouldn't box the product in.
