---
name: padel-api-platform
description: Applies this repository’s Symfony 7 + API Platform + Lexik JWT conventions—operations on `/api/*`, DTO-backed writes, state providers and processors, entity security for matches, and no Symfony controllers for API behavior. Use when adding or changing API endpoints, authentication flows, serializers, or backend tests in this project.
disable-model-invocation: true
---

# Padel backend API (API Platform)

## Non-negotiables (see `AGENTS.md`)

- Expose behavior only through **API Platform operations** under `/api/*`. Do **not** add Symfony controllers for API behavior.
- **Lexik JWT** for API auth; **`Player`** is the security user. Do not introduce custom auth-token entities or tables.
- **English** operation names, URI templates, and request/response field naming.
- **Non-trivial writes** use **DTO `input` classes** and **state processors**, not direct entity deserialization for the whole payload.
- **Match data**: only the **four match players** may read or mutate a match; encode that in operation `security` and/or processor checks.

## Layout in this codebase

| Concern | Location |
|--------|----------|
| Doctrine entities with `#[ApiResource]` | `backend/src/Entity/` |
| Non-persisted API surfaces (health, auth session, level estimate) | `backend/src/ApiResource/` |
| API Platform mapping | `backend/config/packages/api_platform.yaml` (`Entity` + `ApiResource` paths) |
| `ProviderInterface` / `ProcessorInterface` | `backend/src/State/` |
| Request DTOs | `backend/src/Dto/` |
| Domain workflow / rules | `backend/src/Service/` (call from processors, not from thin controllers) |

## Patterns to follow

**URI templates** — Use explicit `uriTemplate` on operations (e.g. `/matches`, `/matches/{id}/finish`, `/auth/login`) so routes stay predictable and documented.

**Custom POST actions** — For commands with no body, use `input: false`, `read: false`, `deserialize: false`, and a dedicated `processor`. When a body is needed, point `input` at a DTO class.

**Serialization** — Prefer `normalizationContext: ['groups' => ['…']]` on the resource or operation; put `#[Groups]` on entity/API resource properties.

**Match authorization** — On item reads/mutations, use expressions like `object.hasPlayer(user)` where `user` is the authenticated `Player` and the entity exposes `hasPlayer(Player $player): bool`.

**Processors** — Validate `$data` is the expected DTO type; use `BadRequestHttpException` / `AccessDeniedHttpException` for client errors; persist via `EntityManagerInterface` in the processor or delegate to a service that returns the entity to serialize.

**Public vs JWT firewalls** — `backend/config/packages/security.yaml` marks specific paths as `PUBLIC_ACCESS` (health, login/register, level questionnaire POST, docs). Everything else under `/api` requires authentication unless you intentionally extend that list.

## Testing and tooling

- Run backend tests with **Symfony PHPUnit Bridge** **via Docker Compose** only (`docker compose -f docker-compose.test.yml …`); see `AGENTS.md`. Use `run --rm backend_tests` for a backend-only loop.
- For PHP syntax checks, target `backend/src`, `backend/config`, and tests—not `backend/var` or `backend/vendor`.

## Examples (existing code)

- **Entity resource + DTO POST + processor**: `PadelMatch` + `CreateMatchInput` + `CreateMatchProcessor`.
- **Item security for players**: `Get` on `/matches/{id}` with `security: "object.hasPlayer(user)"`.
- **Stateless command POSTs**: `finish_match`, `approve_current_score`, `reject_current_score` on `PadelMatch`.
- **Collection provider**: `MatchCollectionProvider` for `GetCollection` on `/matches`.
- **Auth without entity persistence**: `AuthSession` in `ApiResource` + `LoginInput` / `AuthPayload` + `LoginProcessor`; registration lives on `Player` as `register_player`.
- **Current user**: `Get` `/me` with `read: false` + `CurrentPlayerProvider`.

When unsure, open the closest existing operation and mirror its metadata flags (`input`, `read`, `deserialize`, `provider`, `processor`, `security`).
