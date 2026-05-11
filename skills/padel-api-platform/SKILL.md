---
name: padel-api-platform
description: Use when working on this padel club app's Symfony/API Platform backend, JWT authentication, entity operations, score validation workflow, or test stack. Ensures all /api routes stay API Platform-owned.
---

# Padel API Platform

## Core Rules

- All `/api/*` routes are API Platform operations.
- Do not add Symfony controllers for API endpoints.
- Use API Platform resources, DTO input classes, state providers, and state processors for entity management and workflow commands.
- Use Lexik JWT for auth. Do not add custom token tables or hand-rolled bearer token lookup.
- Keep all code, API messages, docs, and UI labels in English.

## Backend Shape

- Entities live in `backend/src/Entity`.
- API-only DTOs live in `backend/src/Dto`.
- Non-entity API resources live in `backend/src/ApiResource`.
- API Platform providers and processors live in `backend/src/State`.
- Shared business rules live in `backend/src/Service`.

## Workflow Conventions

- Match creation, finishing, score proposal, score approval, and score rejection are API Platform operations.
- Use DTOs for write payloads such as match creation, score proposal, score rejection, login, and registration.
- Use `MatchWorkflow` for current player lookup, match access checks, current score lookup, and score validation upserts.
- Keep match visibility limited to participating players.

## Validation

- Run PHP syntax checks with `rg --files backend/src backend/tests backend/config backend/migrations -g '*.php' -0 | xargs -0 -n1 php -l`.
- Validate Compose files with `docker compose config --quiet` and `docker compose -f docker-compose.test.yml config --quiet`.
- Full tests are expected through `docker compose -f docker-compose.test.yml up --build --abort-on-container-exit`.
