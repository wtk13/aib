# ADR-002: Multi-tenancy — Single DB + tenant_id Global Scope

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Single PostgreSQL database. Every tenant-scoped table has `tenant_id BIGINT NOT NULL FK tenants.id`. Eloquent global scope (`TenantScope`) adds `WHERE tenant_id = ?` to all queries. Missing context throws, not silently returns everything.

## Rationale
Separate-schema or separate-DB multi-tenancy is operationally expensive (100 tenants = 100 schema migrations). Single-DB with global scope is the Laravel-native approach and sufficient for our scale through M11+.

## Consequences
Raw queries and queue jobs are scope-leak surfaces. Mitigated by Larastan rule (raw queries outside Repositories) and `TenantAwareJob` base class.
