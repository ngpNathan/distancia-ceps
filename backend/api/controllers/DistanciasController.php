<?php
require_once __DIR__.'/../models/Distancias.php';

class DistanciasController {
  private $model;

  public function __construct() {
    $this->model = new Distancias();
  }

  public function getAll() {
    $data = $this->model->getAll();

    $resultado = array_map(function ($item) {
      $jsonOrigem = json_decode($item['cepOrigemJson'], true);
      $jsonDestino = json_decode($item['cepDestinoJson'], true);

      $dt = new DateTime($item['dataInclusao']);
      $dataFormatada = $dt->format('d/m/Y H:i:s');

      return [
        'cepOrigem' => $item['cepOrigem'],
        'ruaOrigem' => $jsonOrigem['street'],
        'cidadeOrigem' => $jsonOrigem['city'],
        'estadoOrigem' => $jsonOrigem['state'],
        'cepDestino' => $item['cepDestino'],
        'ruaDestino' => $jsonDestino['street'],
        'cidadeDestino' => $jsonDestino['city'],
        'estadoDestino' => $jsonDestino['state'],
        'distancia' => $item['distancia'],
        'dataInclusao' => $dataFormatada,
      ];
    }, $data);

    echo json_encode($resultado);
  }

  public function gravar($data) {
    try {
      $idDistancia = $this->model->insert($data);

      if ($idDistancia) {
        echo json_encode($idDistancia);
      } else {
        throw new Exception('Ocorreu um erro ao gravar distância!');
      }
    } catch (\Throwable $th){
      http_response_code(500);
      echo $th;
      throw $th;
    }
  }
}
