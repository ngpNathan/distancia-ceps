<?php
require_once __DIR__.'/../models/Parametros.php';

class ParametrosController {
  private $model;

  public function __construct() {
    $this->model = new Parametros();
  }

  public function getAll() {
    $data = $this->model->getAll();
    echo json_encode($data);
  }

  public function findByChave($chave_parametro) {
    try {
      $data = $this->model->getByChave($chave_parametro);

      if ($data) {
        echo json_encode($data);
      } else {
        throw new Exception('Parâmetro não encontrado!');
      }
    } catch (\Throwable $th) {
      http_response_code(500);
      echo $th;
      throw $th;
    }
  }
}
