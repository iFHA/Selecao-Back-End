#!/usr/bin/env bash

set -e

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

if [ "$role" = "app" ]; then

  exec php-fpm

elif [ "$role" = "vite" ]; then

    exec npm run dev

elif [ "$role" = "fila" ]; then

    exec php artisan queue:work --verbose --tries=3 --timeout=90

else
    echo "Role \"$role\" inválido"
    exit 1
fi
