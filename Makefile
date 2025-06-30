DOCKER_COMPOSE_V1 := $(shell docker-compose version 2>/dev/null)
DOCKER_COMPOSE_V2 := $(shell docker compose version 2>/dev/null)

DOCKER_COMPOSE := docker compose

ifndef DOCKER_COMPOSE_V2
    ifdef DOCKER_COMPOSE_V1
        DOCKER_COMPOSE := docker-compose
    endif
endif

DOCKER_PHP_SERVICE := php
EXEC_IN_PHP_CONTAINER := $(DOCKER_COMPOSE) exec -it $(DOCKER_PHP_SERVICE)
ARGS = $(filter-out $@,$(MAKECMDGOALS))

.PHONY: up
up:
	$(DOCKER_COMPOSE) up -d

.PHONY: down
down:
	$(DOCKER_COMPOSE) down

.PHONY: build
build: vendor

.PHONY: vendor
vendor:
	$(EXEC_IN_PHP_CONTAINER) composer install --no-interaction

.PHONY: exec
exec:
	$(EXEC_IN_PHP_CONTAINER) $(ARGS)

.PHONY: ssh-into-container
ssh-into-container:
	$(DOCKER_COMPOSE) exec -it $(PHP_CONTAINER) /bin/bash
