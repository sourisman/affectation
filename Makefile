# =============================================================================
#  AFFECTA — raccourcis de développement et d'exploitation
# =============================================================================
.DEFAULT_GOAL := help
PHP ?= php
COMPOSE ?= docker compose

.PHONY: help install up down restart logs shell migrate fresh seed test audit lint assets clean

help: ## Affiche la liste des commandes disponibles
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[1;32m%-12s\033[0m %s\n", $$1, $$2}'

install: ## Prépare l'environnement local (.env + dépendances + base)
	@test -f .env || cp .env.example .env
	@test -d vendor || composer install --no-interaction || true
	@$(PHP) bin/migrate.php
	@$(PHP) bin/seed.php

up: ## Démarre la pile Docker (nginx + PHP-FPM + MySQL)
	$(COMPOSE) up -d --build

down: ## Arrête la pile sans supprimer les volumes
	$(COMPOSE) down

restart: ## Redémarre les conteneurs applicatifs
	$(COMPOSE) restart app web

logs: ## Suit les journaux applicatifs
	$(COMPOSE) logs -f app

shell: ## Ouvre un shell dans le conteneur applicatif
	$(COMPOSE) exec app sh

migrate: ## Applique les migrations en attente
	$(PHP) bin/migrate.php

fresh: ## Recrée le schéma complet (supprime les données)
	$(PHP) bin/migrate.php --fresh

seed: ## Recharge les données de démonstration
	$(PHP) bin/seed.php --force

test: ## Vérifie toutes les routes (public, auth, console, admin)
	$(PHP) tests/smoke.php

audit: ## Audit statique : classes CSS, icônes, variables de vue
	$(PHP) tests/audit.php

dump: ## Regénère database/affecta.sql (schéma MySQL + données)
	$(PHP) bin/dump-sql.php

lint: ## Contrôle syntaxique de tous les fichiers PHP
	@find app config routes views bin database tests -name '*.php' -print0 \
		| xargs -0 -n1 -P4 $(PHP) -l | grep -v '^No syntax errors detected' || true
	@node --check public/assets/js/app.js
	@node --check public/assets/js/landing.js
	@echo "Vérification terminée."

serve: ## Lance le serveur PHP intégré sur http://localhost:8080
	$(PHP) -S 0.0.0.0:8080 -t public public/router.php

clean: ## Supprime caches, journaux et exports générés
	@rm -f storage/logs/*.log storage/cache/* storage/exports/* 2>/dev/null || true
	@echo "Nettoyage terminé."
