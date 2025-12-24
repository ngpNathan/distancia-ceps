<?php
require_once __DIR__.'/../models/Importacoes.php';
require_once __DIR__.'/../models/ImportacaoItens.php';

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

      $header = fgetcsv($handle, 0, '|');
      if (!$header) {
        throw new Exception('Arquivo CSV vazio ou sem cabeçalho!');
      }

      error_log(print_r($header, true));
      $header = array_map(function ($col) {
        return strtolower(trim($col));
      }, $header);
      error_log(print_r($header, true));

      $colunasObrigatorias = ['cep origem', 'cep fim'];
      foreach ($colunasObrigatorias as $coluna) {
        if (!in_array($coluna, $header)) {
          throw new Exception("Coluna obrigatória '{$coluna}' não encontrada no CSV!");
        }
      }

      $importacaoId = $this->model->insert($arquivo['name']);

      $idxOrigem  = array_search('cep origem', $header);
      $idxDestino = array_search('cep fim', $header);

      while (($row = fgetcsv($handle, 0, ';')) !== false) {
        $cepOrigem  = preg_replace('/\D/', '', $row[$idxOrigem] ?? '');
        $cepDestino = preg_replace('/\D/', '', $row[$idxDestino] ?? '');

        if (strlen($cepOrigem) !== 8 || strlen($cepDestino) !== 8) {
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
