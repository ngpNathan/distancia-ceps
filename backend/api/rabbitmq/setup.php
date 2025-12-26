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

/**
 * Exchange
 */
$channel->exchange_declare(
  'importacoes',   // nome
  'direct',        // tipo
  false,           // passive
  true,            // durable
  false            // auto_delete
);

/**
 * Queue
 */
$channel->queue_declare(
  'importacoes.distancias',
  false,
  true,
  false,
  false
);

/**
 * Bind
 */
$channel->queue_bind(
  'importacoes.distancias',
  'importacoes',
  'distancia.calcular'
);

echo "Exchange e fila criadas com sucesso!\n";

$channel->close();
$connection->close();
