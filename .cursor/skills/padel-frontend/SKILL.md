---
name: padel-frontend
description: Guides the padel React 19 + Vite app in frontend/—French user-facing copy, JSON calls to /api/* only, colocated Vitest + Testing Library + jsdom tests, and a scalable src/ layout. Use when editing the frontend, adding UI, hooks, API helpers, or client tests.
disable-model-invocation: true
---

# Padel frontend (React + Vite)

## Stack (authoritative)

| Layer | Choice |
|-------|--------|
| UI | React 19, React DOM 19 |
| Build | Vite 6, `@vitejs/plugin-react` |
| Icons | `lucide-react` |
| Tests | Vitest 3, **jsdom**, **@testing-library/react**, **@testing-library/jest-dom** |
| Env | `import.meta.env` (e.g. `VITE_API_URL`); see `frontend/src/api.js` |

With the dev stack up, use **`docker compose exec frontend …`** for npm (e.g. `exec frontend npm install`, `exec frontend npm run dev`). **`make frontend-install`** wraps `exec frontend npm install`. **Run tests through Docker Compose** (see `AGENTS.md`); Vitest runs in `frontend_tests` for CI-style checks.

## Project rules (see `AGENTS.md`)

- Call **only** `/api/*` (API Platform). No ad-hoc non-API backend URLs for app data.
- **User-visible strings: French.** Code stays English: component names, props, variables, file names, JSON keys sent to the API.
- Keep the UI **sober and operational** (clear hierarchy, short labels).

## `src/` layout (implemented)

```
frontend/src/
├── main.jsx
├── styles.css
├── api.js
├── api.test.jsx
├── App.jsx                    # session, data fetch, composes shell + features
├── App.test.jsx
├── components/                # cross-feature UI
│   ├── AuthenticatedShell.jsx
│   ├── EmptyState.jsx
│   ├── Notice.jsx
│   └── StatusPill.jsx
├── features/
│   ├── auth/                  # login, register, level table
│   ├── dashboard/           # match list + create match
│   └── matches/             # match detail, score form, validations
├── hooks/                     # add shared hooks here as they appear
└── lib/                       # pure helpers + *.test.js
```

**Principles**

- **Colocate tests** next to the module: `Foo.test.jsx` beside `Foo.jsx`, or under `features/bar/__tests__/`. Prefer the same basename for grep and reviews.
- **Thin `App.jsx`**: composition and providers; avoid a single multi-thousand-line file.
- **One component per file** when the component grows beyond ~150 lines or gains distinct concerns.
- **Extract hooks** when state + effects are reused or obscure the render tree.
- **Keep `api.js` (or `api/`)** as the single place for `fetch`, headers, and error shaping—components call small functions, not raw URLs scattered everywhere.

## Testing stack and practices

- **Runner**: Vitest (`vite.config.js` → `test.environment: 'jsdom'`, `globals: true`, `setupFiles: vitest.setup.js`).
- **Setup**: `vitest.setup.js` imports `@testing-library/jest-dom/vitest` for matchers like `toBeInTheDocument()`.
- **Component tests**: render with `render()` from `@testing-library/react`; assert with **roles and accessible names** (`getByRole`, `findByRole`)—not CSS selectors or implementation details unless unavoidable.
- **Network**: mock `globalThis.fetch` with `vi.spyOn` (see `api.test.jsx`) or MSW later if integration-style tests multiply.
- **User flows**: prefer **`@testing-library/user-event`** over `fireEvent` when adding the dependency; install with `docker compose exec frontend npm install -D @testing-library/user-event` (dev stack up) and `user.setup()` per test for realistic interactions.

**Commands (project rule: use Compose)**

```bash
# Full backend + frontend test stack (canonical)
docker compose -f docker-compose.test.yml up --build --abort-on-container-exit

# Frontend tests only
docker compose -f docker-compose.test.yml run --rm frontend_tests
```

Inside a dev container with `frontend` mounted, `npm test` / `npm run test:watch` are fine for interactive work; for merge-ready verification, prefer the Compose commands above.

## React patterns (concise)

- Prefer **function components** and hooks; avoid class components.
- **Stable keys** in lists (`id` from API, not array index when order changes).
- **Controlled inputs** for forms that sync to server or cross-field validation.
- **Lift state** only as high as needed; prefer local state + clear props over global store until complexity demands it.
- **Effects**: minimal dependencies; split unrelated logic into separate `useEffect` hooks; avoid `useEffect` for things that can run in event handlers.
- **Lists / async**: handle loading and error UI explicitly; do not assume happy-path only.

## HTML / a11y

- Keep **`index.html` `lang="fr"`** in sync with UI language.
- Prefer semantic elements (`button` for actions, `nav`, `main`, `label` tied to inputs).
- Icon-only buttons need **`aria-label`** (French copy).

## Do not

- Add Symfony routes or non-`/api` JSON endpoints for core app behavior (backend owns API Platform).
- Ship English user-facing copy without project approval (default is French).
- Skip tests for new non-trivial behavior (happy path + one failure path when meaningful).

When adding a new top-level area, create its folder under `components/` or `features/` first, then add the module and its `*.test.jsx` in the same change.
