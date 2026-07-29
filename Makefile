MAKEFLAGS += --silent

DOCKER_PHP = exp-bookings-php
USER_ID = $(shell id -u)
GROUP_ID = $(shell id -g)

COLOR_NO_COLOR = \033[0m
COLOR_GREEN = \033[0;32m
COLOR_YELLOW = \033[0;33m

-include .env
-include .env.local

main:
	$(MAKE) help

build: ## Build and start all services
	docker network inspect exp-bookings >/dev/null 2>&1 || docker network create exp-bookings >/dev/null
	USER_ID=${USER_ID} \
	GROUP_ID=${GROUP_ID} \
	XDEBUG_HOST=${XDEBUG_HOST} \
	XDEBUG_PORT=${XDEBUG_PORT} \
	NGINX_PORT=${NGINX_PORT} \
	docker compose up -d --build

clear-cache: ## Clear Symfony cache
	docker exec -it ${DOCKER_PHP} bin/console cache:clear
	docker exec -it ${DOCKER_PHP} bin/console cache:pool:clear cache.app

composer-dump: ## Run composer dump-autoload
	docker exec ${DOCKER_PHP} composer dump-autoload

composer-install: ## Run composer install
	docker exec -it ${DOCKER_PHP} composer install

create-db: ## Create a database
	docker exec -it ${DOCKER_PHP} bin/console doctrine:database:create --if-not-exists
	docker exec -it ${DOCKER_PHP} bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

down: ## Stop and remove all services
	docker compose down

help: ## Show this help message
	echo ' '
	echo '▓█████ ▒██   ██▒ ██▓███        ▄▄▄▄    ▒█████   ▒█████   ██ ▄█▀ ██▓ ███▄    █   ▄████   ██████  '
	echo '▓█   ▀ ▒▒ █ █ ▒░▓██░  ██▒     ▓█████▄ ▒██▒  ██▒▒██▒  ██▒ ██▄█▒ ▓██▒ ██ ▀█   █  ██▒ ▀█▒▒██    ▒  '
	echo '▒███   ░░  █   ░▓██░ ██▓▒     ▒██▒ ▄██▒██░  ██▒▒██░  ██▒▓███▄░ ▒██▒▓██  ▀█ ██▒▒██░▄▄▄░░ ▓██▄    '
	echo '▒▓█  ▄  ░ █ █ ▒ ▒██▄█▓▒ ▒     ▒██░█▀  ▒██   ██░▒██   ██░▓██ █▄ ░██░▓██▒  ▐▌██▒░▓█  ██▓  ▒   ██▒ '
	echo '░▒████▒▒██▒ ▒██▒▒██▒ ░  ░ ██▓ ░▓█  ▀█▓░ ████▓▒░░ ████▓▒░▒██▒ █▄░██░▒██░   ▓██░░▒▓███▀▒▒██████▒▒ '
	echo '░░ ▒░ ░▒▒ ░ ░▓ ░▒▓▒░ ░  ░ ▒▓▒ ░▒▓███▀▒░ ▒░▒░▒░ ░ ▒░▒░▒░ ▒ ▒▒ ▓▒░▓  ░ ▒░   ▒ ▒  ░▒   ▒ ▒ ▒▓▒ ▒ ░ '
	echo ' ░ ░  ░░░   ░▒ ░░▒ ░      ░▒  ▒░▒   ░   ░ ▒ ▒░   ░ ▒ ▒░ ░ ░▒ ▒░ ▒ ░░ ░░   ░ ▒░  ░   ░ ░ ░▒  ░ ░ '
	echo '   ░    ░    ░  ░░        ░    ░    ░ ░ ░ ░ ▒  ░ ░ ░ ▒  ░ ░░ ░  ▒ ░   ░   ░ ░ ░ ░   ░ ░  ░  ░   '
	echo '   ░  ░ ░    ░             ░   ░          ░ ░      ░ ░  ░  ░    ░           ░       ░       ░   '
	echo '                           ░        ░                                                           ' "${COLOR_GREEN}${APP_VERSION}\n"
	echo "${COLOR_YELLOW}Usage:"
	echo "  ${COLOR_NO_COLOR}make [target]\n"
	echo "${COLOR_YELLOW}Targets:"
	awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\/]+:.*?## / {sub("\\\\n",sprintf("\n%22c"," "), $$2);printf "  ${COLOR_GREEN}%-24s${COLOR_NO_COLOR}%s\n", $$1, $$2}' $(MAKEFILE_LIST)

logs: ## Show logs (usage: make logs [service=name])
	docker compose logs $(service)

ps: ## List running services
	docker compose ps

restart: ## Restart all services
	$(MAKE) stop && $(MAKE) run

run: ## Start all services
	docker network inspect exp-bookings >/dev/null 2>&1 || docker network create exp-bookings >/dev/null
	USER_ID=${USER_ID} \
	GROUP_ID=${GROUP_ID} \
	XDEBUG_HOST=${XDEBUG_HOST} \
	XDEBUG_PORT=${XDEBUG_PORT} \
	NGINX_PORT=${NGINX_PORT} \
	docker compose up -d

ssh-php: ## Open a terminal (php container)
	docker exec -it ${DOCKER_PHP} bash

ssh-php-root: ## Open a terminal as root (php container)
	docker exec -it -u root ${DOCKER_PHP} bash

stop: ## Stop all services
	docker compose stop

test: ## Run tests (usage: make test [class=Name])
ifdef class
	docker exec -it ${DOCKER_PHP} ./vendor/bin/phpunit --filter $(class)
else
	docker exec -it ${DOCKER_PHP} ./vendor/bin/phpunit
endif
