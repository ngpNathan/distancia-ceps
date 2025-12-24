<?php
require_once __DIR__.'/../config/database.php';

class Distancias {
  private $conn;
  private $table = 'distancias';

  public function __construct() {
    $database = new Database();
    $this->conn = $database->connect();
  }

  public function getAll() {
    $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY dataInclusao DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getByCepOrigemAndCepDestino($cepOrigem, $cepDestino) {
    $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE cepOrigem = :cepOrigem AND cepDestino = :cepDestino LIMIT 1");

    $stmt->bindParam(':cepOrigem', $cepOrigem);
    $stmt->bindParam(':cepDestino', $cepDestino);

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function insert($data) {
    $existe = $this->getByCepOrigemAndCepDestino($data['cepOrigem'], $data['cepDestino']);

    if ($existe) {
      throw new Exception('Distância entre CEPs já existente!');
    }

    $stmt = $this->conn->prepare(
      "INSERT INTO {$this->table}
       (cepOrigem, cepOrigemJson, cepDestino, cepDestinoJson, distancia)
       VALUES (:cepOrigem, :cepOrigemJson, :cepDestino, :cepDestinoJson, :distancia)"
    );

    $stmt->bindParam(':cepOrigem', $data['cepOrigem']);
    $stmt->bindParam(':cepOrigemJson', $data['cepOrigemJson']);
    $stmt->bindParam(':cepDestino', $data['cepDestino']);
    $stmt->bindParam(':cepDestinoJson', $data['cepDestinoJson']);
    $stmt->bindParam(':distancia', floatval($data['distancia']));

    $stmt->execute();

    return $this->conn->lastInsertId();
  }
}
