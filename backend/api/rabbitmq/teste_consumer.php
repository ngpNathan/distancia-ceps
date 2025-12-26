<?php

require __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection(
  'localhost',
  5672,
  'guest',
  'guest'
);

$channel = $connection->channel();

$channel->basic_qos(null, 1, null);

echo "Aguardando mensagens...\n";

$channel->basic_consume(
  'importacoes.distancias',
  '',
  false,
  false,
  false,
  false,
  function ($msg) {
    echo "Recebido: {$msg->body}\n";
    $msg->delivery_info['channel']->basic_ack(
      $msg->delivery_info['delivery_tag']
    );
  }
);


while (count($channel->callbacks)) {
  $channel->wait();
}
