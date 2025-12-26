<?php
require_once __DIR__.'/../models/Importacoes.php';
require_once __DIR__.'/../models/ImportacaoItens.php';
require_once __DIR__ . '/../rabbitmq/producer.php';

class ImportacoesController {
  private $model;
  private $modelItem;

  public function __construct() {
    $this->model = new Importacoes();
    $this->modelItem = new ImportacaoItens();
  }

  public function iniciarImportacao($arquivo) {
    try {
      if (!$arquivo) {
        throw new Exception('Arquivo para importação não encontrado!');
      }

      $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
      if ($ext !== 'csv') {
        throw new Exception('O arquivo deve estar no formato CSV!');
      }

      $handle = fopen($arquivo['tmp_name'], 'r');
      if (!$handle) {
        throw new Exception('Não foi possível abrir o arquivo CSV!');
      }

      $header = fgetcsv($handle, 0, ';');
      if (!$header) {
        throw new Exception('Arquivo CSV vazio ou sem cabeçalho!');
      }

      $header = array_map(function ($col) {
        return strtolower(preg_replace('/^\xEF\xBB\xBF/', '', trim($col)));
      }, $header);

      $colunasObrigatorias = ['cep origem', 'cep fim'];
      foreach ($colunasObrigatorias as $coluna) {
        if (!in_array($coluna, $header, true)) {
          throw new Exception("Coluna obrigatória '{$coluna}' não encontrada no CSV!");
        }
      }

      $importacaoId = $this->model->insert($arquivo['name']);

      $idxOrigem  = array_search('cep origem', $header);
      $idxDestino = array_search('cep fim', $header);

      $totalItens = 0;

      while (($row = fgetcsv($handle, 0, ';')) !== false) {
        $cepOrigem  = preg_replace('/\D/', '', $row[$idxOrigem] ?? '');
        $cepDestino = preg_replace('/\D/', '', $row[$idxDestino] ?? '');

        $totalItens++;

        if (strlen($cepOrigem) !== 8 || strlen($cepDestino) !== 8 || $cepOrigem == $cepDestino) {
          $this->modelItem->insertComErro(
            $importacaoId,
            $cepOrigem,
            $cepDestino,
            'CEP inválido'
          );
          continue;
        }

        $this->modelItem->insert(
          $importacaoId,
          $cepOrigem,
          $cepDestino
        );
      }

      $this->model->updateTotalItens($importacaoId, $totalItens);

      $producer = new Producer();
      $producer->publish([
        'importacao_id' => $importacaoId
      ]);

      fclose($handle);

      echo json_encode([
        'importacaoId' => $importacaoId,
      ]);
      http_response_code(200);
    } catch (\Throwable $th){
      http_response_code(500);
      echo $th;
      throw $th;
    }
  }
}
