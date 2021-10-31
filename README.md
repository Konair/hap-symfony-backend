# Hexagonal architecture playground: Backend with [Symfony](https://symfony.com/)

## What is it?
It is a playground for testing the [hexagonal architecture](https://herbertograca.com/2017/11/16/explicit-architecture-01-ddd-hexagonal-onion-clean-cqrs-how-i-put-it-all-together/).
The core logic does not depend on any framework. Symfony only calls use cases, so it is only an entry point for an HTTP request or a CLI command.

## Documentation

### Requirements
- docker

### Run
1. clone this repository
1. create your own `docker-compose.override.yml` (don't forget to change the github_composer_auth argument)
1. `docker-compose up -d`
1. `docker-compose exec php php bin/console doctrine:migration:migrate`

### Parts of the project
- [Shared package](https://github.com/Konair/hap-shared-package)
- [Payment package](https://github.com/Konair/hap-payment-package)
- Backend with Symfony

### More information
- see in the [composer.json](composer.json) file
- see in the [routes](config/routes) file

## Licence
[GPLv3](LICENSE)
