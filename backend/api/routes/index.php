<?php
require_once __DIR__.'/../controllers/ParametrosController.php';
require_once __DIR__.'/../controllers/DistanciasController.php';
require_once __DIR__.'/../controllers/ImportacoesController.php';

$endpointParts = explode('/', trim($url, '/'));

if ($endpointParts[0] === 'api') {
  array_shift($endpointParts);
}

$resource = $endpointParts[0] ?? null;
$param = $endpointParts[1] ?? null;

switch($resource) {
  case 'parametro':
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      $controller = new ParametrosController();
  
      if ($param) {
        $controller->findByChave($param);
      } else {
        $controller->getAll();
      }
    }

    break;
  case 'distancia':
    $controller = new DistanciasController();
    
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == 'POST') {
      $data = json_decode(file_get_contents('php://input'), true);
      $controller->gravar($data);
    } else if ($method == 'GET') {
      $controller->getAll();
    }

    break;
  case 'importacao':
    $controller = new ImportacoesController();
    
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == 'POST') {
      $controller->iniciarImportacao($_FILES['arquivo']);
    } else if ($method == 'GET') {
      $controller->getAll();
    }

    break;
  default:
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint não encontrado']);
    break;
}
