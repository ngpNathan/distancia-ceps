<?php
require_once __DIR__.'/../controllers/ParametrosController.php';

$endpointParts = explode('/', trim($url, '/'));


$resource = $endpointParts[0] ?? null;
$param = $endpointParts[1] ?? null;

switch($resource) {
    case 'parametro':
    $controller = new ParametrosController();

    if ($param) {
      $controller->show($param);
    } else {
      $controller->index();
    }

    break;
  default:
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint não encontrado']);
    break;
}
