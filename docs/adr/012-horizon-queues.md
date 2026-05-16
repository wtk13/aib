# ADR-012: Queue Worker — Laravel Horizon

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Use Laravel Horizon to manage Redis-backed queues. The `horizon` service in docker-compose.yml runs alongside the app container using the same PHP image.

## Rationale
Horizon provides a real-time dashboard for monitoring queue throughput, job failures, and worker status. The Redis queue driver is already required for session and cache in production; co-locating the queue backend eliminates an extra dependency.

## Consequences
Horizon must be started explicitly (`php artisan horizon`) in production. The `TenantAwareJob` base class ensures every queued job restores tenant context on the worker side.
