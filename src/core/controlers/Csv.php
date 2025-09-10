<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    include_once('../helpers/Tools.php');

    class CSV {
        private $csvFile;
        private $csvSeparator;
        private $nameColumn;

        public function __construct($csvFile, $csvSeparator, $nameColumn) {
            $this->csvFile = validFileCSV($csvFile);
            $this->csvSeparator = $csvSeparator;
            $this->nameColumn = $nameColumn;
        }

        private function get_nameColumn() {
            return $this -> nameColumn;
        }

        public function dataExtractCSV(CSV $csv) {
            // Configurações de separadores
            $csvConfig = [
                "0" => ',',
                "1" => ';'
            ];
        
            if (!isset($csvConfig[$this->csvSeparator])) {
                throw new Exception("Separador inválido.");
            }
        
            $separator = $this->csvSeparator;

            // Abre o arquivo temporário diretamente
            if (($handle = fopen($this->csvFile['tmp_name'], 'r')) !== false) {
                $cabecalho = fgetcsv($handle, 1000, $csvConfig[$separator]);
                $cabecalho = preg_replace('/[\x00-\x1F\x7F\xA0\xAD\x{200B}-\x{200F}\x{FEFF}]/u', '', $cabecalho);
            
                $dados = [];

                if (empty($csv->get_nameColumn())) {
                    throw new Exception("Preencha qual é o nome da coluna");
                }
            
                // Procura o índice da coluna no cabeçalho
                $indiceColuna = array_search($csv->get_nameColumn(), $cabecalho);
            
                if ($indiceColuna === false) {
                    throw new Exception("Coluna '{$csv->get_nameColumn()}' não encontrada no cabeçalho.");
                }
            
                while (($linha = fgetcsv($handle, 1000, $csvConfig[$separator])) !== false) {
                    if (count($linha) !== count($cabecalho)) {
                        throw new Exception('O número de colunas na linha não corresponde ao cabeçalho.');
                    }
            
                    // Pega o valor da coluna específica
                    $dados[] = $linha[$indiceColuna];
                }
            
                fclose($handle);
                return $dados;
            } else {
                throw new Exception("Erro ao abrir o arquivo.");
            }            
        }
    }
?>