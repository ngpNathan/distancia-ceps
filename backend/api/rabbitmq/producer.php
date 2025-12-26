<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Producer
{
  private $channel;
  private $exchange = '';
  private $queue = 'importacoes.distancias';

  public function __construct()
  {
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

    $this->channel = $connection->channel();

    $this->channel->queue_declare(
      $this->queue,
      false,
      true,
      false,
      false
    );
  }

  public function publish(array $payload)
  {
    $msg = new AMQPMessage(
      json_encode($payload),
      [
          'content_type' => 'application/json',
          'delivery_mode' => 2
      ]
    );

    $this->channel->basic_publish(
      $msg,
      $this->exchange,
      $this->queue
    );
  }
}
