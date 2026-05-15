# ADR-003: Tenant ID Format — ULID + slug

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
`tenants.id` is a standard bigint PK for joins. `tenants.ulid` (CHAR 26, unique) is used in external-facing contexts (queue job payloads, signed URLs). `tenants.slug` (kebab) is the subdomain.

## Rationale
ULID is URL-safe, lexicographically sortable, and shorter than UUID v4. Slug gives human-readable subdomains.
