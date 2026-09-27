#!/bin/bash
set -e

if [ ! -f .env ]; then
  echo "Aucun fichier .env trouvé. Création d'un fichier .env temporaire..."
  touch .env
fi

echo "Génération de la clé d'application si absente..."
php artisan key:generate --force --no-interaction || true

echo "Exécution des migrations..."
php artisan migrate
# php artisan migrate:fresh --seed --force

echo "Création du lien symbolique pour le stockage (accès aux images)..."
php artisan storage:link

echo "Création de l'administrateur par défaut si absent..."
php artisan db:seed --class=AdminSeeder --force

echo "Mise en cache de la configuration..."
php artisan config:cache
php artisan route:cache

echo "Démarrage du serveur sur le port ${PORT:-10000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
