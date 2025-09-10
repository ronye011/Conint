<?php
    use PhpOffice\PhpSpreadsheet\IOFactory;
    include_once(__DIR__ . '/../dependence/vendor/autoload.php');
    include_once('../helpers/Tools.php');

    class Sicoob {
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

        public static function ExtCompare(Sicoob $bank, $dataCsv) {
            $dataBank = Sicoob::dataSicoobExtract($bank);
            
            // Chama a função Comparator com os arrays
            $dataCsv = explode(",", $dataCsv);
            return Comparator($dataBank, $dataCsv, $bank->get_compare());
        }

        private static function dataSicoobExtract(Sicoob $bank) {
            $filePath = $bank->get_bankName();

            $spreadsheet = IOFactory::load($filePath['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            $pix_valid = $bank->get_pixValid();
            $number_valid = $bank->get_numberValid();
            $remove_verify_digit = $bank->get_remove_verify_digit();

            // Sistema de pesquisa dos dados
            $modo = null;
            $pula = true;
            $dataExtract = [];
            foreach ($data as $row) {
                // Identificando o modo de leitura
                if (($row[1] === "58-LIQUIDAÇÃO - VIA COMPENSAÇÃO" || $row[1] === "215-LIQUIDAÇÃO COBRANÇA - INTERCREDIS") && $pix_valid != 0) {
                    $modo = 'leitura';
                    continue;
                }
                else if ($row[1] === "212-LIQUIDAÇÃO COBRANÇA - VIA PIX" && $pix_valid != 1) {
                    $modo = 'leitura';
                    continue;
                }

                // Pulando cabeçalhos
                if ($row[1] === "Sacado") {
                    continue;
                }

                // Pulando linhas vazias até encontrar o modo
                if ($modo === 'leitura' && $pula === true && empty($row[1])) {
                    $pula = false;
                    continue;
                } else if ($pula === false && empty($row[1])) {
                    $modo = null;
                    $pula = true;
                    continue;
                }

                // Processando os dados conforme o modo
                if ($modo === 'leitura') {
                    if ($number_valid == 0) {
                        $dataExtract[] = [
                            'data' => trim($row[11]),
                            'valor' => $row[25],
                            'mora' => $row[28],
                            'desconto' => $row[29],
                            'acrescimo' => $row[31],
                            'baixa' => $row[34],
                            'valor_baixa' => $row[35],
                            'sacado' => $row[1],
                            'nn' => $row[5],
                            'sn' => $row[11],
                            'credito' => $row[13],
                            'limitePgo' => $row[21],
                            'vencimento' => $row[18]
                        ];
                    } elseif ($number_valid == 1) {
                        $tmp_data = $row[5];
                        if ($remove_verify_digit == 0) {
                            $tmp_data = explode('-', $tmp_data)[0];
                        }
                        $dataExtract[] = [
                            'data' => trim($tmp_data),
                            'valor' => $row[25],
                            'mora' => $row[28],
                            'desconto' => $row[29],
                            'acrescimo' => $row[31],
                            'baixa' => $row[34],
                            'valor_baixa' => $row[35],
                            'sacado' => $row[1],
                            'nn' => $row[5],
                            'sn' => $row[11],
                            'credito' => $row[13],
                            'limitePgo' => $row[21],
                            'vencimento' => $row[18]
                        ];
                    }
                }
            }
            return $dataExtract;
        }
    }
?>