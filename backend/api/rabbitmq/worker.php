<?php

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../models/Parametros.php';
require_once __DIR__ . '/../models/Distancias.php';
require_once __DIR__ . '/../models/Importacoes.php';
require_once __DIR__.'/../models/ImportacaoItens.php';

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

$queue = 'importacoes.distancias';

$channel->queue_declare(
  $queue,
  false,
  true,
  false,
  false
);

echo " [*] Worker aguardando mensagens...\n";

/**
 * Fórmula de Haversine que considera curvatura da terra e retorna distância em KM
 */
function calcularDistanciaHaversine(
  float $latitude1,
  float $longitude1,
  float $latitude2,
  float $longitude2
): float {
  $raioTerra = 6371; // Raio da Terra em km

  $dLat = deg2rad($latitude2 - $latitude1);
  $dLon = deg2rad($longitude2 - $longitude1);

  $a = sin($dLat / 2) * sin($dLat / 2) +
       cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) *
       sin($dLon / 2) * sin($dLon / 2);

  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

  $distancia = $raioTerra * $c;

  return round($distancia, 2);
}

function buscarCepBrasilApi(string $cep): array
{
  $url = "https://brasilapi.com.br/api/cep/v2/{$cep}";

  $ch = curl_init($url);

  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_HTTPHEADER => [
      'Accept: application/json'
    ],
  ]);

  $response = curl_exec($ch);

  if ($response === false) {
    throw new Exception('Erro cURL: ' . curl_error($ch));
  }

  $json = json_decode($response, true);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  curl_close($ch);

  switch ($httpCode) {
    case 200:
      return $json;
    case 400:
      throw new InvalidArgumentException(
        $json['message'] ?? 'CEP inválido'
      );
    case 404:
      throw new RuntimeException(
        $json['message'] ?? 'CEP não encontrado'
      );
    case 500:
      throw new RuntimeException(
        $json['message'] ?? "Erro BrasilAPI ({$httpCode})"
      );
    default:
      throw new RuntimeException(
        "Erro BrasilAPI ({$httpCode})"
      );
  }
}

function validarCoordenadas(array $cepResponse, string $cep, string $tipo) {
  if (
    !isset(
      $cepResponse['location'],
      $cepResponse['location']['coordinates'],
      $cepResponse['location']['coordinates']['latitude'],
      $cepResponse['location']['coordinates']['longitude']
    )
  ) {
    throw new RuntimeException(
      "CEP {$cep} de {$tipo} não possui coordenadas geográficas"
    );
  }

  $latitude  = $cepResponse['location']['coordinates']['latitude'];
  $longitude = $cepResponse['location']['coordinates']['longitude'];

  return [(float) $latitude, (float) $longitude];
}

function calcularDistancia($cepOrigem, $cepDestino, $urlBrasilApi) {
  try {
    $responseCepOrigem = buscarCepBrasilApi($cepOrigem);
    $responseCepDestino = buscarCepBrasilApi($cepDestino);

    [$latitudeOrigem, $longitudeOrigem] = validarCoordenadas($responseCepOrigem, $cepOrigem, 'origem');
    [$latitudeDestino, $longitudeDestino] = validarCoordenadas($responseCepDestino, $cepDestino, 'destino');

    $distancia = calcularDistanciaHaversine($latitudeOrigem, $longitudeOrigem, $latitudeDestino, $longitudeDestino);

    return [
      'distancia' => $distancia,
      'cepOrigemJson' => json_encode($responseCepOrigem),
      'cepDestinoJson' => json_encode($responseCepDestino)
    ];
  } catch (\Throwable $th) {
    throw $th;
  }
}

$callback = function ($msg) {
  $payload = json_decode($msg->body, true);
  $importacaoId = $payload['importacao_id'] ?? null;

  if (!$importacaoId) {
    echo "Mensagem inválida\n";
    $msg->delivery_info['channel']->basic_ack(
      $msg->delivery_info['delivery_tag']
    );
    return;
  }

  $model = new Importacoes();
  $modelItem = new ImportacaoItens();
  $modelDistancia = new Distancias();
  $modelParametros = new Parametros();

  try {
    $urlBrasilApi = $modelParametros->getByChave('URLBrasilApi');
    $qtdLmtReqApi = $modelParametros->getByChave('QtdLmtReqAPI');

    $model->updateToProcessando($importacaoId);

    while ($item = $modelItem->getNextItemPendente($importacaoId)) {
      try {
        $resultCalcDistancia = calcularDistancia(
          $item['cepOrigem'],
          $item['cepDestino'],
          $urlBrasilApi['valor']
        );

        $modelDistancia->insert([
          'cepOrigem' => $item['cepOrigem'],
          'cepOrigemJson' => $resultCalcDistancia['cepOrigemJson'],
          'cepDestino' => $item['cepDestino'],
          'cepDestinoJson' => $resultCalcDistancia['cepDestinoJson'],
          'distancia' => $resultCalcDistancia['distancia']
        ]);

        $modelItem->updateStatus($item['id'], 'PROCESSADO');
      } catch (\Throwable $e) {
        $modelItem->updateStatus($item['id'], 'ERRO', $e->getMessage());

        echo $e->getMessage();
      }

      $modelItem->updateDataFim($item['id']);
      $model->updateProcessados($importacaoId);

      
      sleep(intval($qtdLmtReqApi['valor']));
    }

    $model->finalizarImportacao($importacaoId);

    echo "Importação {$importacaoId} finalizada\n";

    $msg->delivery_info['channel']->basic_ack(
      $msg->delivery_info['delivery_tag']
    );
  } catch (\Throwable $e) {
    echo "Erro crítico na importação {$importacaoId}: {$e->getMessage()}\n";

    $msg->delivery_info['channel']->basic_nack(
        $msg->delivery_info['delivery_tag'],
        false,
        true
    );
  }
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume(
  $queue,
  '',
  false,
  false,
  false,
  false,
  $callback
);

while (count($channel->callbacks)) {
  $channel->wait();
}
