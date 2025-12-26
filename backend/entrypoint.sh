#!/bin/bash
set -e

# Espera RabbitMQ ficar disponível
until nc -z rabbitmq 5672; do
  echo "Esperando RabbitMQ..."
  sleep 2
done

# Executa o setup das filas (uma vez)
php /app/api/rabbitmq/setup.php

# Inicia o servidor PHP
exec php -S 0.0.0.0:8081 api/router.php
