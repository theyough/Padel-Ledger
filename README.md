# Padel Level Manager

Web application for managing player levels in a padel club.

## Stack

- Backend: Symfony 7, API Platform, JWT, stateless JSON API
- Frontend: React + Vite
- Database: PostgreSQL
- Development email: Mailpit
- Environment: Docker Compose

## Run the project

```bash
docker compose up --build
```

Then open:

- Frontend: http://localhost:5173
- API Symfony: http://localhost:8000
- Mailpit: http://localhost:8025
- API Platform documentation: http://localhost:8000/api/docs

At startup, the backend installs Composer dependencies, runs migrations, generates JWT keys when needed, and starts the API.

## Run tests

```bash
docker compose -f docker-compose.test.yml up --build --abort-on-container-exit
```

Backend tests use Symfony PHPUnit Bridge. Frontend tests use Vitest, Testing Library, and jsdom.

## Included features

- Player registration with email, password, and initial questionnaire.
- JWT authentication with Bearer tokens.
- Player level from 1 to 8, plus a granular internal rating and match history.
- Four-player match creation.
- End-of-match email invitations to enter or validate the score.
- The first submitted score becomes the current default score.
- Players can reject a score and submit a corrected proposal.
- The current score must be approved by all 4 players.
- Ratings and levels are updated automatically when a score is validated.
- Every `/api/*` route is an API Platform operation.

## Main API Platform Operations

- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/health`
- `GET /api/me`
- `GET /api/players`
- `POST /api/questionnaire/level`
- `GET /api/matches`
- `POST /api/matches`
- `GET /api/matches/{id}`
- `POST /api/matches/{id}/finish`
- `POST /api/matches/{id}/score-proposals`
- `POST /api/matches/{id}/score-proposals/current/approve`
- `POST /api/matches/{id}/score-proposals/current/reject`

## Level algorithm

The visible level remains an integer from 1 to 8. The system also stores an internal `rating` between 100 and 899:

- level 1: 100-199
- level 2: 200-299
- level 3: 300-399
- level 4: 400-499
- level 5: 500-599
- level 6: 600-699
- level 7: 700-799
- level 8: 800-899

When a match is validated:

1. The average rating of each team is calculated.
2. The expected win probability is calculated with an Elo-like formula.
3. The expected result is compared with the actual result.
4. A margin factor is applied based on the game difference.
5. Each player gains or loses points based on the opposing average level and their own rating.
6. The visible level is recalculated from the rating.

This avoids abrupt promotions or demotions while rewarding wins against stronger opponents and penalizing losses against weaker opponents.
