<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    use PhpOffice\PhpSpreadsheet\IOFactory;
    include_once(__DIR__ . '/../dependence/vendor/autoload.php');
    include_once('../helpers/Tools.php');
    
    class Sicredi {
        private $xls_fileName;
        private $pix_valid;
        private $number_valid;
        private $compare;
        private $notFoundFileName;
        private $remove_barra;
        private $remove_verify_digit;

        public function __construct($fileBank, $pix_valid, $number_valid, $compare, $remove_barra, $remove_verify_digit) {
            $this->xls_fileName = validFileXLS($fileBank);
            $this->pix_valid = $pix_valid;
            $this->number_valid = $number_valid;
            $this->compare = $compare;
            $this->remove_barra = $remove_barra;
            $this->remove_verify_digit = $remove_verify_digit;
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

        public function get_remove_barra() {
            return $this -> remove_barra;
        }

        public function get_remove_verify_digit() {
            return $this -> remove_verify_digit;
        }

        public static function ExtCompare(Sicredi $bank, $dataCsv) {
            $dataBank = Sicredi::dataSicrediExtract($bank);
            
            // Chama a função Comparator com os arrays
            $dataCsv = explode(",", $dataCsv);
            return Comparator($dataBank, $dataCsv, $bank->get_compare());
        }

        private static function dataSicrediExtract(Sicredi $bank) {
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
                    $cell = $sheet->getCell('A' . $row)->getValue();
                    // Verifique se $cell não é null e é uma string antes de tentar dividir
                    if ($cell !== null && is_string($cell)) {
                        $partes = explode('/', $cell);
                        $cell = $partes[0]; // Obtém a primeira parte da string
                    } else {
                        // Se $cell for null ou não for uma string, atribua um valor padrão ou trate o erro
                        $cell = ''; // Ou outro valor default ou ação de erro
                    }
        
                    if ($modo == 'LEITURA') {
                        $movimento = trim($sheet->getCell('N' . $row)->getValue() ?? '');
                        if ($validPix == 0 && (str_contains($movimento, 'Liquidação PIX'))) {
                            if ($bank->get_numberValid() == 0) {
                                $seu_numero = $sheet->getCell('A' . $row)->getValue();
                                if ($seu_numero !== null) {
                                    $partes = explode('/', $seu_numero);
                                    $dados[] = $partes[0];
                                }
                            } else if ($bank->get_numberValid() == 1) {
                                $nosso_numero = $sheet->getCell('B' . $row)->getValue();
                                if ($nosso_numero !== null) {
                                    $partes = explode('/', $nosso_numero);
                                    $tmp_data = $partes[1];
                                    $partes = explode('-', $tmp_data);
                                    $dados[] = $partes[0];
                                }
                            } else {
                                $txid = $sheet->getCell('C' . $row)->getValue();
                                if ($txid !== null) {
                                    $dados[] = $txid;
                                }
                            }
                        } 
                        else if (!str_contains($movimento, 'Liquidação PIX')) {
                            if ($bank->get_numberValid() == 0) {
                                $seu_numero = $sheet->getCell('A' . $row)->getValue();
                                if ($seu_numero !== null) {
                                    $partes = explode('/', $seu_numero);
                                    $dados[] = $partes[0];
                                }
                            } else if ($bank->get_numberValid() == 1) {
                                $nosso_numero = $sheet->getCell('B' . $row)->getValue();
                                if ($nosso_numero !== null) {
                                    $partes = explode('/', $nosso_numero);
                                    $tmp_data = $partes[1];
                                    $partes = explode('-', $tmp_data);
                                    $dados[] = $partes[0];
                                }
                            } else {
                                $txid = $sheet->getCell('C' . $row)->getValue();
                                if ($txid !== null) {
                                    $dados[] = $txid;
                                }
                            }
                        }
                    }

                    // Verificar se o valor da célula não é nulo antes de usar str_contains
                    if ($cell && str_contains($cell, 'Seu N°')) {
                        $modo = 'LEITURA';
                    } else if ($cell == '') {
                        $modo = 'PROCURA';
                    }
                }
                //var_dump($dados);
                return $dados;
            } else {
                throw new Exception("Erro ao abrir o arquivo.");
            }
        }             
    }
?>