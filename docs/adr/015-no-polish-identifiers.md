# ADR-015: No Polish Identifiers in Code or DB

**Status:** Accepted  
**Date:** 2026-05-15

## Decision
Entity names, column names, JSON keys, enum values, route path segments, PHP class/method/property names — English only. Polish exists only in `lang/*.json` files and user-facing copy.

## Rationale
English-identifier codebase is readable to any developer regardless of Polish fluency. Prevents encoding issues in migrations, SQL logs, and error messages.

## Enforcement
`tests/Feature/NoPolishInCodeTest.php` scans `app/`, `database/`, `config/` for Polish characters in string literals.
