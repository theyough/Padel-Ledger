# Padel — local development helpers (Docker Compose).
# Requires Docker. Run `make help` for targets.

COMPOSE ?= docker compose

.DEFAULT_GOAL := help

.PHONY: help init up down cs-fix cs-fixer-check

help:
	@echo "Padel — common targets"
	@echo "  make init             Build and start stack, install PHP/JS deps in containers"
	@echo "  make up / make down   Start or stop dev services"
	@echo "  make cs-fix           PHP CS Fixer (apply) — needs stack up: make up"
	@echo "  make cs-fixer-check   PHP CS Fixer dry-run — needs stack up"
	@echo ""
	@echo "Tests (see AGENTS.md): docker compose -f docker-compose.test.yml up --build --abort-on-container-exit"

up:
	$(COMPOSE) up -d --build

down:
	$(COMPOSE) down

## Bring up services, then install Composer and npm deps inside running containers.
init: up
	$(COMPOSE) exec backend composer install --no-interaction
	$(COMPOSE) exec frontend npm install

cs-fix:
	$(COMPOSE) exec backend composer cs-fix

cs-fixer-check:
	$(COMPOSE) exec backend composer cs-fixer:check
