#!/bin/bash

set -e

cd /var/www

# composer install --no-scripts --no-autoloader --prefer-dist --no-interaction
#composer dump-autoload --optimize

exec docker-php-entrypoint "$@"
