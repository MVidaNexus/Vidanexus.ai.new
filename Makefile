.DEFAULT_GOAL := help
COMPOSE := docker compose
COMPOSE_FILE := docker-compose.yml

.PHONY: help setup build up down restart logs shell migrate fresh

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

setup: ## First-run: copy .env.example → .env (skips if .env exists)
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "Created .env from .env.example — edit it before running 'make up'"; \
	else \
		echo ".env already exists, skipping"; \
	fi

build: ## Build all images
	$(COMPOSE) -f $(COMPOSE_FILE) build

up: ## Start all services in the background
	$(COMPOSE) -f $(COMPOSE_FILE) up -d

down: ## Stop and remove containers
	$(COMPOSE) -f $(COMPOSE_FILE) down

restart: ## Restart all services
	$(COMPOSE) -f $(COMPOSE_FILE) restart

logs: ## Tail logs for all services (Ctrl+C to stop)
	$(COMPOSE) -f $(COMPOSE_FILE) logs -f

shell: ## Open a shell in the app container
	$(COMPOSE) -f $(COMPOSE_FILE) exec app sh

migrate: ## Run database migrations
	$(COMPOSE) -f $(COMPOSE_FILE) exec app php artisan migrate --force

fresh: ## Drop all tables, re-run migrations and seeders
	$(COMPOSE) -f $(COMPOSE_FILE) exec app php artisan migrate:fresh --seed --force
