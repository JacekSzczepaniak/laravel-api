# ===== Konfiguracja =====
SHELL := /bin/bash
COMPOSE := docker compose

SERVICE_APP := app-api
APP := $(COMPOSE) exec $(SERVICE_APP)

# ===== Helpery =====
.PHONY: help ensure-up ps logs

help: ## Wyświetl listę dostępnych komend
	@echo ""
	@echo "Dostępne komendy:"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| sort \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'
	@echo ""

ensure-up: ## Upewnij się, że kontener app działa
	@$(COMPOSE) up -d $(SERVICE_APP)

ps: ## Lista serwisów docker-compose
	$(COMPOSE) ps

logs: ## Logi z aplikacji
	$(COMPOSE) logs -f $(SERVICE_APP)

# ===== Główne komendy =====
.PHONY: up down build bash test stan cs-fix cs-check swagger-open key migrate seed tinker artisan

up: ## Uruchom docker-compose
	$(COMPOSE) up -d

down: ## Zatrzymaj docker-compose
	$(COMPOSE) down

build: ## Przebuduj obrazy
	$(COMPOSE) build --no-cache

bash: ensure-up ## Wejdź do kontenera aplikacji (bash)
	$(APP) bash

artisan: ensure-up ## Dowolna komenda artisan: make artisan cmd="route:list"
	$(APP) php artisan $(cmd)

key: ensure-up ## Wygeneruj APP_KEY w .env
	$(APP) php artisan key:generate

migrate: ensure-up ## Migracje bazy
	$(APP) php artisan migrate

seed: ensure-up ## Seed bazy
	$(APP) php artisan db:seed

tinker: ensure-up ## Otwórz Tinker
	$(APP) php artisan tinker

test: ensure-up ## Uruchom testy (phpunit/pest)
	$(APP) php artisan test || $(APP) ./vendor/bin/pest

stan: ensure-up ## Analiza statyczna (phpstan)
	$(APP) ./vendor/bin/phpstan analyse --memory-limit=1G
# Dodaj poniżej nowy cel do instalacji narzędzi dev
stan-setup: ensure-up ## Zainstaluj Larastan/PHPStan (dev)
	$(APP) composer require --dev nunomaduro/larastan:^3.6 phpstan/phpstan:^2.1

cs-fix: ensure-up ## Napraw formatowanie (Pint)
	$(APP) php ./vendor/bin/pint

cs-check: ensure-up ## Sprawdź formatowanie (Pint --test)
	$(APP) php ./vendor/bin/pint --test

swagger-open: ensure-up ## Generuj dokumentację Swagger
	$(APP) php artisan l5-swagger:generate
