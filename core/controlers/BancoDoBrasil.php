<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    use PhpOffice\PhpSpreadsheet\IOFactory;
    include_once('../helpers/Tools.php');
    
    class BB {
        private $xls_fileName;
        private $pix_valid;
        private $number_valid;
        private $compare;
        private $notFoundFileName;

        public function __construct($fileBank, $pix_valid, $number_valid, $compare) {
            $this->xls_fileName = validFileXLS($fileBank);
            $this->pix_valid = $pix_valid;
            $this->number_valid = $number_valid;
            $this->compare = $compare;
        }

        public function get_bankName() {
            return $this -> xls_fileName;
        }

        public function get_pixValid() {
            return $this -> pix_valid;
        }

        public function get_numberValid() {
            return $this -> number_valid;
        }

        public function get_compare() {
            return $this -> compare;
        }

        public static function ExtCompare(BB $bank, $dataCsv) {
            $dataBank = BB::dataBBExtract($bank);
            
            // Chama a função Comparator com os arrays
            $dataCsv = explode(",", $dataCsv);
            return Comparator($dataBank, $dataCsv, $bank->get_compare());
        }

        private static function dataBBExtract(BB $bank) {
            // Obter o caminho completo do arquivo (garantir que o caminho completo é passado)
            $filePath = $bank->get_bankName();
            $validPix = (int) $bank->get_pixValid();
        
            // Verifique se o caminho do arquivo está correto (exemplo de depuração)
            if (empty($filePath['tmp_name'])) {
                throw new Exception("O arquivo especificado não existe: " . $filePath);
            }
        
            $filePath = $filePath['tmp_name'];
        
            // Verificar se o arquivo existe no diretório temporário
            if (!is_uploaded_file($filePath)) {
                throw new Exception("O arquivo enviado não é válido.");
            }
        
            // Carregar o arquivo Excel (pode ser .xls ou .xlsx)
            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (Exception $e) {
                throw new Exception("Erro ao carregar o arquivo: " . $e->getMessage());
            }
        
            $dados = [];
        
            // Processar o arquivo carregado
            if ($spreadsheet) {
                // Obter a primeira planilha
                $sheet = $spreadsheet->getActiveSheet();
        
                $modo = '';
                $highestRow = $sheet->getHighestDataRow();
        
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cell = $sheet->getCell('G' . $row)->getValue();
        
                    if ($modo == 'LEITURA') {
                        $movimento = trim($sheet->getCell('L' . $row)->getValue() ?? '');
                        if (($validPix == 0 || $validPix == 2) && (str_contains($movimento, 'PIX'))) {
                            if ($bank->get_numberValid() == 0) {
                                $seu_numero = $sheet->getCell('G' . $row)->getValue();
                                if ($seu_numero !== null) {
                                    $dados[] = [
                                        'data' => $seu_numero,
                                        'valor' => $sheet->getCell('J' . $row)->getValue(),
                                        'descricao' => $sheet->getCell('L' . $row)->getValue()
                                    ]; 
                                }
                            } else if ($bank->get_numberValid() == 1) {
                                $nosso_numero = $sheet->getCell('F' . $row)->getValue();
                                if ($nosso_numero !== null) {
                                    $dados[] = [
                                        'data' => $nosso_numero,
                                        'valor' => $sheet->getCell('J' . $row)->getValue(),
                                        'descricao' => $sheet->getCell('L' . $row)->getValue()
                                    ];
                                }
                            }
                        } 
                        else if ($validPix != 0 && !str_contains($movimento, 'PIX')) {
                            if ($bank->get_numberValid() == 0) {
                                $seu_numero = $sheet->getCell('G' . $row)->getValue();
                                if ($seu_numero !== null) {
                                    $dados[] = [
                                        'data' => $seu_numero,
                                        'valor' => $sheet->getCell('J' . $row)->getValue(),
                                        'descricao' => $sheet->getCell('L' . $row)->getValue()
                                    ]; 
                                }
                            } else if ($bank->get_numberValid() == 1) {
                                $nosso_numero = $sheet->getCell('F' . $row)->getValue();
                                if ($nosso_numero !== null) {
                                    $dados[] = [
                                        'data' => $nosso_numero,
                                        'valor' => $sheet->getCell('J' . $row)->getValue(),
                                        'descricao' => $sheet->getCell('L' . $row)->getValue()
                                    ];
                                }
                            }
                        }
                    }

                    // Verificar se o valor da célula não é nulo antes de usar str_contains
                    if ($cell && str_contains($cell, 'Seu Número')) {
                        $modo = 'LEITURA';
                    } else if ($cell == '') {
                        $modo = 'PROCURA';
                    }
                }
                return $dados;
            } else {
                throw new Exception("Erro ao abrir o arquivo.");
            }
        }
    }
?>