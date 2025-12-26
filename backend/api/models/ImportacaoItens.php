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

  public function getByImportacaoId($importacaoId) {
    $stmt = $this->conn->prepare(
        "SELECT *
           FROM importacao_itens
          WHERE importacaoId = :importacaoId
          ORDER BY dataInclusao ASC"
    );

    $stmt->bindParam(':importacaoId', $importacaoId);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getNextItemPendente($importacaoId) {
    $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE importacaoId = :importacaoId AND status = 'PENDENTE' AND dataFim IS NULL ORDER BY id ASC LIMIT 1");

    $stmt->bindParam(':importacaoId', $importacaoId);

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
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

  public function updateStatus($itemId, $status, $mensagemErro = null) {
    $stmt = $this->conn->prepare(
      "UPDATE {$this->table} SET status = :status, mensagemErro = :mensagemErro WHERE id = :id"
    );

    $stmt->bindParam(':id', $itemId);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':mensagemErro', $mensagemErro);

    $stmt->execute();
  }

  public function updateDataFim($itemId) {
    $stmt = $this->conn->prepare(
      "UPDATE {$this->table} SET dataFim = NOW() WHERE id = :id"
    );

    $stmt->bindParam(':id', $itemId);

    $stmt->execute();
  }
}
