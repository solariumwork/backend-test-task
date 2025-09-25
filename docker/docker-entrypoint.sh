#!/bin/sh
set -e

echo "Waiting for database..."
sleep 5

php bin/console doctrine:migrations:migrate --no-interaction || true
php bin/console doctrine:fixtures:load --no-interaction || true

exec php -S 0.0.0.0:8337 -t public public/index.php
