#!/bin/sh
set -ex

echo "Waiting for database..."
until pg_isready -h database -p 5432 -U app; do
  echo "Database not ready yet..."
  sleep 1
done

echo "Running database migrations..."
php bin/console doctrine:database:create --if-not-exists || true
php bin/console doctrine:migrations:migrate --no-interaction || true

echo "Loading fixtures..."
php bin/console doctrine:fixtures:load --purge-with-truncate --no-interaction || true