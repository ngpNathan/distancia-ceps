<?php
require_once __DIR__.'/../models/Parametros.php';

class ParametrosController {
  private $model;

  public function __construct() {
    $this->model = new Parametro();
  }

  public function index() {
    $data = $this->model->getAll();
    echo json_encode($data);
  }

  public function show($nome_parametro) {
    $data = $this->model->getByName($nome_parametro);

    if ($data) {
      echo json_encode($data);
    } else {
      http_response_code(404);
      echo json_encode(['error' => 'Parâmetro não encontrado']);
    }
  }
}
