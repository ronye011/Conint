<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
    use PhpOffice\PhpSpreadsheet\RichText\RichText;
    include_once(__DIR__ . '/../dependence/vendor/autoload.php');
    include_once('../helpers/Tools.php');

    class XLSX {
        private $xlsxFile;
        private $nameColumn;

        public function __construct($xlsxFile, $nameColumn) {
            $this->xlsxFile = validFileXLSX($xlsxFile);
            $this->nameColumn = $nameColumn;
        }

        private function get_xlsxFile() {
            return $this -> xlsxFile;
        }

        private function get_nameColumn() {
            return $this -> nameColumn;
        }

        public function dataExtractXLSX(XLSX $xlsx) {
            $filePath = $this->xlsxFile['tmp_name'];

            // Verifica se o arquivo .XLSX existe
            if (!file_exists($filePath)) {
                throw new Exception("Arquivo XLSX não encontrado.");
            }

            // Tenta carregar o arquivo .XLSX
            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                throw new Exception("Erro ao carregar o arquivo .XLSX: " . $e->getMessage());
            }

            if (empty($xlsx->get_nameColumn())) {
                throw new Exception("Preencha qual é o nome da coluna");
            }

            // Obtém a primeira planilha
            $sheet = $spreadsheet->getActiveSheet();
            
            // Obtém o cabeçalho da planilha (primeira linha)
            $cabecalho = [];
            $highestColumn = $sheet->getHighestColumn();
            $highestRow = $sheet->getHighestRow();
            
            // Leitura da primeira linha (cabeçalho)
            for ($col = 1; $col <= Coordinate::columnIndexFromString($highestColumn); $col++) {
                $cabecalho[] = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . '1')->getValue();
            }

            // Verifica se a coluna existe no cabeçalho
            $indiceColuna = array_search($xlsx->get_nameColumn(), $cabecalho);
            if ($indiceColuna === false) {
                throw new Exception("Coluna '{$xlsx->get_nameColumn()}' não encontrada no cabeçalho.");
            }

            // Extrair os dados da coluna específica
            for ($row = 2; $row <= $highestRow; $row++) {
                $value = $sheet->getCell(Coordinate::stringFromColumnIndex($indiceColuna + 1) . $row)->getValue();
                // Adiciona o valor ao array de dados
                $dados[] = $value;
            }

            return $dados;
        }      

        public function dataExtracIXC(XLSX $xlsx) {
            $filePath = $this->xlsxFile['tmp_name'];
        
            if (!file_exists($filePath)) {
                throw new Exception("Arquivo XLSX não encontrado.");
            }
        
            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                throw new Exception("Erro ao carregar o arquivo .XLSX: " . $e->getMessage());
            }
        
            $sheet = $spreadsheet->getActiveSheet();
            $highestColumn = $sheet->getHighestColumn();
            $highestRow = $sheet->getHighestRow();
        
            $indiceColuna = null;
            $linhaCabecalho = null;
        
            // Procura pela coluna alvo em todas as linhas do topo até encontrar
            for ($row = 1; $row <= $highestRow; $row++) {
                for ($col = 1; $col <= Coordinate::columnIndexFromString($highestColumn); $col++) {
                    $cellValue = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue();
                    if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                        $cellValue = $cellValue->getPlainText();
                    }
        
                    if (trim($cellValue) === trim($xlsx->get_nameColumn())) {
                        $indiceColuna = $col - 1; // índice 0-based
                        $linhaCabecalho = $row;
                        break 2; // para ambos os loops
                    }
                }
            }
        
            if ($indiceColuna === null) {
                throw new Exception("Coluna '{$xlsx->get_nameColumn()}' não encontrada.");
            }
        
            $dados = [];
            for ($row = $linhaCabecalho + 1; $row <= $highestRow; $row++) {
                $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($indiceColuna + 1) . $row);
                $value = $cell->getValue();
        
                if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $value = $value->getPlainText();
                }
        
                if (trim($value) === "ID Rec.") {
                    continue;
                }
        
                $dados[] = $value;
            }
        
            return $dados;
        }        
    }
?>