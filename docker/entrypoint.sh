#!/bin/sh
set -e

# Sync built assets into shared public volume
cp -a /app/public-build/. /app/public/

mkdir -p var/cache var/log var/storage/default var/storage/documents
chown -R www-data:www-data var/

echo "Waiting for database..."
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
    sleep 2
done
echo "Database is ready."

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Running app:setup..."
php bin/console app:setup

exec "$@"
