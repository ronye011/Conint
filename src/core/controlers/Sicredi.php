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
            $filePath = $bank->get_bankName();
            $validPix = (int) $bank->get_pixValid();
        
            if (empty($filePath['tmp_name']) || !is_uploaded_file($filePath['tmp_name'])) {
                throw new Exception("Arquivo inválido.");
            }
        
            $spreadsheet = IOFactory::load($filePath['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true); // 'A' => coluna A, etc.
        
            $dados = [];
            $modo = '';
        
            foreach ($rows as $row) {
                $cellA = $row['A'] ?? '';
                $cellN = trim($row['N'] ?? '');
                $cellL = $row['L'] ?? '';
                $cellB = $row['B'] ?? '';
                $cellC = $row['C'] ?? '';
        
                // Trata o valor de A (que pode ter '/')
                $partes = explode('/', (string) $cellA);
                $valA = $partes[0];
        
                if ($modo === 'LEITURA') {
                    $isPix = str_contains($cellN, 'Liquidação PIX');
        
                    if (($validPix == 0 || $validPix == 2) && $isPix) {
                        $dados[] = self::extrairDadosLinha($bank, $valA, $cellB, $cellC, $cellL, $cellN);
                    } elseif ($validPix != 0 && !$isPix) {
                        $dados[] = self::extrairDadosLinha($bank, $valA, $cellB, $cellC, $cellL, $cellN);
                    }
                }
        
                if ($valA && str_contains($valA, 'Seu N°')) {
                    $modo = 'LEITURA';
                } elseif ($valA === '') {
                    $modo = 'PROCURA';
                }
            }
        
            return array_filter($dados); // remove nulls
        }
        
        private static function extrairDadosLinha($bank, $valA, $valB, $valC, $valor, $descricao) {
            if ($bank->get_numberValid() == 0) {
                if ($bank->get_remove_barra() == 0) {
                    $valA = explode('/', $valA)[0];
                }
                return [
                    'data' => $valA,
                    'valor' => $valor,
                    'descricao' => $descricao
                ];
            } elseif ($bank->get_numberValid() == 1) {
                $tmp_data = explode('/', $valB)[1] ?? '';
                if ($bank->get_remove_verify_digit() == 0) {
                    $tmp_data = explode('-', $tmp_data)[0];
                }
                return [
                    'data' => $tmp_data,
                    'valor' => $valor,
                    'descricao' => $descricao
                ];
            } else {
                return $valC ? [
                    'data' => $valC,
                    'valor' => $valor,
                    'descricao' => $descricao
                ] : null;
            }
        }                     
    }
?>