<?php

require __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$port = getenv('RABBITMQ_PORT') ?: 5672;
$user = getenv('RABBITMQ_USER') ?: 'guest';
$pass = getenv('RABBITMQ_PASSWORD') ?: 'guest';

$connection = new AMQPStreamConnection(
  $host,
  $port,
  $user,
  $pass
);

$channel = $connection->channel();

$channel->exchange_declare(
  'importacoes',
  'direct',
  false,
  true,
  false
);

$channel->queue_declare(
  'importacoes.distancias',
  false,
  true,
  false,
  false
);

$channel->queue_bind(
  'importacoes.distancias',
  'importacoes',
  'distancia.calcular'
);

echo "Exchange e fila criadas com sucesso!\n";

$channel->close();
$connection->close();
