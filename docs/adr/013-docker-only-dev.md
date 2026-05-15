# ADR-013: Docker-Only Development Environment

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
All development commands run through Docker Compose containers. No local PHP, Postgres, Redis, Node installations. CI uses the same Dockerfile.

## Rationale
Solo dev + future contractors onboard with `git clone && make up`. No "works on my machine" drift. Dev/CI/prod parity from day 1.

## Consequences
Bootstrap requires Docker installed locally. Slightly slower first `docker build`. Acceptable trade.
