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
- Run backend tests through Symfony PHPUnit Bridge.

## Frontend

- Use React, Vite, Vitest, Testing Library, and jsdom.
- The frontend should call only `/api/*` API Platform operations.
- Prefer a sober, operational UI with concise English labels.

## Docker

- Development runs with `docker compose up --build`.
- Tests run with `docker compose -f docker-compose.test.yml up --build --abort-on-container-exit`.
- PHP syntax checks should target source/config/test files, not generated `backend/var` or `backend/vendor` files.
