<?php

require __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection(
  'localhost',
  5672,
  'guest',
  'guest'
);

$channel = $connection->channel();

$msg = new AMQPMessage(
  json_encode([
      'teste' => true,
      'msg' => 'Mensagem enviada do PHP'
  ]),
  ['delivery_mode' => 2]
);

$channel->basic_publish(
  $msg,
  'importacoes',
  'distancia.calcular'
);

echo "Mensagem publicada!\n";

$channel->close();
$connection->close();
