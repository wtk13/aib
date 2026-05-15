# ADR-001: Modular Monolith over Microservices

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Single Laravel 11 codebase with internal `app/Modules/` split.

## Rationale
Solo dev, 15–20h/week. Microservices multiply operational cost (separate deploys, network calls, distributed tracing) with zero benefit at N=1 tenant. Modular monolith gives clean boundaries without the tax.

## Consequences
M11+ team mode may warrant extracting a service. The module boundaries make that feasible without rewrite.
