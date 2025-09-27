#!/bin/sh
set -ex

echo "Waiting for database..."
sleep 5

php bin/console doctrine:query:sql \
  "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = 'app' AND pid <> pg_backend_pid();" || true

php bin/console doctrine:database:create --if-not-exists || true
php bin/console doctrine:migrations:migrate --no-interaction || true
php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction || true

exec php -S 0.0.0.0:8337 -t public public/index.php
