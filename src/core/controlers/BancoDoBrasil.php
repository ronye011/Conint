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
            $fileInfo = $bank->get_bankName();
            $validPix = (int) $bank->get_pixValid();
            $numberValid = (int) $bank->get_numberValid();
        
            if (empty($fileInfo['tmp_name'])) {
                throw new Exception("O arquivo especificado não existe.");
            }
        
            $filePath = $fileInfo['tmp_name'];
        
            if (!is_uploaded_file($filePath)) {
                throw new Exception("O arquivo enviado não é válido.");
            }
        
            try {
                $spreadsheet = IOFactory::load($filePath);
            } catch (Exception $e) {
                throw new Exception("Erro ao carregar o arquivo: " . $e->getMessage());
            }
        
            $dados = [];
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $modo = '';
        
            for ($row = 1; $row <= $highestRow; $row++) {
                $colG = trim((string) $sheet->getCell("G$row")->getValue());
                $colF = trim((string) $sheet->getCell("F$row")->getValue());
                $colJ = trim((string) $sheet->getCell("J$row")->getValue());
                $colL = trim((string) $sheet->getCell("L$row")->getValue());
        
                if ($modo === 'LEITURA') {
                    $isPix = str_contains($colL, 'PIX');
        
                    if (
                        ($validPix == 0 || $validPix == 2) && $isPix ||
                        ($validPix != 0 && !$isPix)
                    ) {
                        $numero = $numberValid === 0 ? $colG : $colF;
        
                        if (!empty($numero)) {
                            $dados[] = [
                                'data' => $numero,
                                'valor' => $colJ,
                                'descricao' => $colL
                            ];
                        }
                    }
                }
        
                if (!empty($colG) && str_contains($colG, 'Seu Número')) {
                    $modo = 'LEITURA';
                } else if ($colG === '') {
                    $modo = 'PROCURA';
                }
            }
        
            return $dados;
        }        
    }
?>