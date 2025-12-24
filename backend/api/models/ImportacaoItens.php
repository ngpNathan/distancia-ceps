<?php
require_once __DIR__.'/../config/database.php';

class ImportacaoItens {
  private $conn;
  private $table = 'importacao_itens';

  public function __construct() {
    $database = new Database();
    $this->conn = $database->connect();
  }

  public function getAll() {
    $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY importacao_id, dataInclusao");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function insertComErro(
    $importacaoId,
    $cepOrigem,
    $cepDestino,
    $mensagemErro
  ) {
    $stmt = $this->conn->prepare(
      "INSERT INTO {$this->table} (importacaoId, cepOrigem, cepDestino, mensagemErro)
      VALUES (:importacaoId, :cepOrigem, :cepDestino, :mensagemErro)"
    );

    $stmt->bindParam(':importacaoId', $importacaoId);
    $stmt->bindParam(':cepOrigem', $cepOrigem);
    $stmt->bindParam(':cepDestino', $cepDestino);
    $stmt->bindParam(':mensagemErro', $mensagemErro);

    $stmt->execute();

    return $this->conn->lastInsertId();
  }

  public function insert(
    $importacaoId,
    $cepOrigem,
    $cepDestino
  ) {
    $stmt = $this->conn->prepare(
      "INSERT INTO {$this->table} (importacaoId, cepOrigem, cepDestino)
      VALUES (:importacaoId, :cepOrigem, :cepDestino)"
    );

    $stmt->bindParam(':importacaoId', $importacaoId);
    $stmt->bindParam(':cepOrigem', $cepOrigem);
    $stmt->bindParam(':cepDestino', $cepDestino);

    $stmt->execute();

    return $this->conn->lastInsertId();
  }
}
