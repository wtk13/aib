---
name: Docker-only dev environment
description: All development runs through Docker; no local installations of Postgres, Redis, PHP, Node, etc.
type: feedback
---

**Rule:** The development environment runs **entirely through Docker / docker-compose**. No local installations of Postgres, Redis, PHP, Composer, Node, npm, or any other dependency. CI also runs through the same container images.

**Why:** Reproducibility — "works on my machine" is a cost the project cannot afford with solo-dev part-time velocity. Container parity between dev/CI/prod is the only way to catch environmental drift before wife's tenant hits it. Also: when a future contractor or second dev joins, onboarding is `git clone && docker compose up`, not a 4-hour Brewfile session.

**How to apply:**

- **`docker-compose.yml` is mandatory, not optional.** Updates `sprint-plan.md` S0.9 from "(optional)" to required. Includes: postgres+pgvector, redis, mailhog, the Laravel app container (php-fpm), nginx, Horizon worker, optional node container for Vite.
- **Dockerfile per service.** App container is built from a versioned base image (`php:8.3-fpm-alpine` + extensions). Pinned versions across the board (PHP, Node, Postgres, Redis, Chromium for Browsershot).
- **All commands run through Docker:** `docker compose exec app php artisan ...`, `docker compose exec app composer ...`, `docker compose exec node npm ...`. Wrap common ones in a `Makefile` or `bin/` shell scripts so the typing burden stays low.
- **CI mirrors local.** GitHub Actions builds the same Dockerfile, runs Pest/Pint/Larastan inside the container. No `setup-php` GitHub Actions — use `services:` with Docker.
- **Browsershot/Chromium** runs inside its own container or a sidecar — pin Chromium version (per `architecture.md` risk note).
- **Production parity:** Forge deploys built artifacts that match the dev container as closely as possible. If Hetzner managed Postgres lacks pgvector, run Postgres in a Docker container on the same Forge box rather than diverging local from prod.
- **No Herd, no Valet, no Laragon.** These are explicitly off the table.
