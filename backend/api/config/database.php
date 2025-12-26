<?php
class Database {
  private $host;
  private $db_name;
  private $username;
  private $password;
  public $conn;

  public function __construct() {
    $this->host = getenv('DB_HOST') ?: 'db';
    $this->db_name = getenv('DB_DATABASE') ?: 'ceps';
    $this->username = getenv('DB_USERNAME') ?: 'root';
    $this->password = getenv('DB_PASSWORD') ?: '1234';
  }

  public function connect() {
    $this->conn = null;
    try {
      $this->conn = new PDO(
        "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
        $this->username,
        $this->password
      );

      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      http_response_code(500);
      echo json_encode(['error' => 'Erro ao conectar ao banco: '.$e->getMessage()]);
      exit;
    }

    return $this->conn;
  }
}
