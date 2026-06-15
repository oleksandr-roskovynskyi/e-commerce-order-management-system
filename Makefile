# E-commerce Order Management System — developer shortcuts.
#
# Commands run through Laravel Sail (Docker), matching the README workflow.
# Run `make` (or `make help`) to list everything. If you are not using Docker,
# run the underlying tool directly (e.g. `composer lint`, `php artisan test`).

SAIL := ./vendor/bin/sail

.DEFAULT_GOAL := help

.PHONY: help init install setup up down restart shell \
        migrate fresh seed build dev \
        test test-catalog test-order lint fix analyse quality tinker

help: ## List available commands
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

# ---- Setup & environment -------------------------------------------------

init: ## Fresh-clone bootstrap: .env, install deps (one-off Composer container), start, set up
	@test -f .env || cp .env.example .env
	docker run --rm -u "$$(id -u):$$(id -g)" -v "$$(pwd):/var/www/html" -w /var/www/html \
		laravelsail/php85-composer:latest composer install --ignore-platform-reqs
	$(SAIL) up -d
	$(MAKE) setup

install: ## Install PHP and JS dependencies (containers must be running)
	$(SAIL) composer install
	$(SAIL) npm install

setup: ## App key, migrate + seed, build assets (containers must be running)
	$(SAIL) artisan key:generate
	$(SAIL) artisan migrate --seed
	$(SAIL) npm install
	$(SAIL) npm run build

up: ## Start the containers (app + PostgreSQL) in the background
	$(SAIL) up -d

down: ## Stop the containers
	$(SAIL) down

restart: ## Restart the containers
	$(SAIL) restart

shell: ## Open a bash shell inside the app container
	$(SAIL) shell

# ---- Database ------------------------------------------------------------

migrate: ## Run outstanding migrations
	$(SAIL) artisan migrate

fresh: ## Drop all tables, re-migrate and seed
	$(SAIL) artisan migrate:fresh --seed

seed: ## Seed the database
	$(SAIL) artisan db:seed

# ---- Front-end -----------------------------------------------------------

build: ## Build front-end assets
	$(SAIL) npm run build

dev: ## Start the Vite dev server
	$(SAIL) npm run dev

# ---- Tests & quality -----------------------------------------------------

test: ## Run the full Pest suite
	$(SAIL) pest

test-catalog: ## Run the Catalog module tests
	$(SAIL) pest Modules/Catalog

test-order: ## Run the Order module tests
	$(SAIL) pest Modules/Order

lint: ## Check code style (Laravel Duster)
	$(SAIL) composer lint

fix: ## Auto-fix code style (Laravel Duster)
	$(SAIL) composer fix

analyse: ## Run static analysis (Larastan, level 6)
	$(SAIL) composer analyse

quality: lint analyse test ## Run style + static analysis + tests (CI parity)

tinker: ## Open a Tinker REPL
	$(SAIL) artisan tinker
