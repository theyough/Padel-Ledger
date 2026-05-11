# Project Rules

## API

- All `/api/*` endpoints must be API Platform operations.
- Do not add Symfony controllers for API behavior.
- Entity reads and writes should be modeled as API Platform resources, operations, state providers, and state processors.
- Keep business rules in services or processors; keep entities focused on state and small domain helpers.
- Use Lexik JWT for authentication. Do not reintroduce custom auth-token entities or tables.
- Keep route names and payloads in English.

## Backend

- Use Symfony 7, API Platform, Doctrine ORM, PostgreSQL, and Lexik JWT.
- Use DTO inputs for non-trivial write operations instead of deserializing directly into entities.
- Protect match resources so only the 4 players in a match can read or mutate them.
- Run backend tests through Symfony PHPUnit Bridge **inside the test container** (see Docker).
- Format PHP with **PHP CS Fixer** (`backend/.php-cs-fixer.dist.php`, Symfony-oriented rules). With the dev stack running, use **`docker compose exec backend composer cs-fix`** to apply fixes, or **`docker compose exec backend composer cs-fixer:check`** for a dry run (requires the `backend` service from `docker compose up`, not a one-off `run`).

## Frontend

- Use React, Vite, Vitest, Testing Library, and jsdom.
- The frontend should call only `/api/*` API Platform operations.
- All user-visible copy must be in **French** (labels, buttons, headings, empty states, placeholders). Keep code identifiers, file names, props, API field names, and routes in **English**.
- Prefer a sober, operational UI with concise wording.
- Run frontend Vitest **via Docker Compose** (see Docker), not as the default workflow on the host.

## Docker

- Development runs with `docker compose up --build`.
- **All automated tests (backend PHPUnit and frontend Vitest) must be run with Docker Compose**, using `docker compose -f docker-compose.test.yml up --build --abort-on-container-exit`. That is the canonical command for agents and CI-style checks; do not treat bare `phpunit` / `npm test` on the host as the primary way to verify the project.
- For a faster loop on one stack, you may run a single service, for example: `docker compose -f docker-compose.test.yml run --rm backend_tests` or `docker compose -f docker-compose.test.yml run --rm frontend_tests` (same compose file; still Compose, not a local toolchain).
- PHP syntax checks should target source/config/test files, not generated `backend/var` or `backend/vendor` files.
