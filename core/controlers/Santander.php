<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    include_once('../helpers/Tools.php');
    
    class Santander {
        private $csv_fileName;
        private $txt_fileName;
        private $pix_valid;
        private $number_valid;
        private $compare;
        private $csvSeparate;
        private $notFoundFileName;
        private $remove_barra;
        private $remove_verify_digit;

        public function __construct($fileBank, $fileCSV, $pix_valid, $number_valid, $compare, $csvSeparate, $remove_barra, $remove_verify_digit) {
            $this->csv_fileName = $fileCSV;
            $this->txt_fileName = validFileBankTXT($fileBank);
            $this->pix_valid = $pix_valid;
            $this->number_valid = $number_valid;
            $this->compare = $compare;
            $this->csvSeparate = $csvSeparate;
            $this->remove_barra = $remove_barra;
            $this->remove_verify_digit = $remove_verify_digit;
        }

        public function get_csvName() {
            return $this -> csv_fileName;
        }

        public function set_notFoundFileName($notFoundFileName) {
            $this -> notFoundFileName = $notFoundFileName;
        }

        public function get_notFoundFileName() {
            return $this -> notFoundFileName;
        }

        public function get_bankName() {
            return $this -> txt_fileName;
        }

        public function get_csvSeparate() {
            return $this -> csvSeparate;
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

        public static function ExtCompare(Santander $bank) {
            $dataCsv = processCsvDirect($bank->get_csvName(), $bank->get_csvSeparate());
            $dataBank = Santander::dataSantanderExtract($bank->get_bankName(), $bank->get_pixValid(), $bank->get_numberValid(), $bank->get_remove_barra(), $bank->get_remove_verify_digit());
            
            // Chama a função Comparator com os arrays
            return Comparator($dataCsv, $dataBank, $bank->get_compare());
        }

        private static function dataSantanderExtract($caminhoArquivoTmp, $validPix, $compare, $remove_barra, $remove_verify_digit) {
            $validPix = (int) $validPix;
            $compare = (int) $compare;
            $dados = [];
            $posicoes = [];
            $modo = null;
            $cabecalhoEncontrado = false;
        
            $handle = fopen($caminhoArquivoTmp, 'r');
        
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $line = rtrim($line);
        
                    if ((substr($line, 0, 1) === '1') || (substr($line, 1, 5) === 'TOTAL')) {
                        $modo = null;
                        $cabecalhoEncontrado = false;
                    }
        
                    $substring = substr($line, 61, 11);
                    if (str_contains($substring, 'LIQUIDACOES')) {
                        $modo = 'LIQUIDACOES';
                    }
        
                    if ($modo === 'LIQUIDACOES') {
                        if (!$cabecalhoEncontrado && str_contains($line, 'SEU NUMERO') && str_contains($line, 'NOSSO NUMERO') && str_contains($line, 'VENCTO')) {
                            $cabecalhoEncontrado = true;
                            $posicoes['seu_numero'] = strpos($line, 'SEU NUMERO');
                            $posicoes['nosso_numero'] = strpos($line, 'NOSSO NUMERO') - 1;
                            $posicoes['vencimento'] = strpos($line, 'VENCTO');
                            $posicoes['larguraSeuNumero'] = $posicoes['nosso_numero'] - $posicoes['seu_numero'];
                            $posicoes['larguraNossoNumero'] = $posicoes['vencimento'] - $posicoes['nosso_numero'];
                            continue;
                        }
        
                        if ($cabecalhoEncontrado && trim($line) !== '') {
                            $seuNumero = trim(substr($line, $posicoes['seu_numero'], $posicoes['larguraSeuNumero']));
                            $nossoNumero = trim(substr($line, $posicoes['nosso_numero'], $posicoes['larguraNossoNumero']));
        
                            if ($compare == 1) {
                                if ($nossoNumero !== '') {
                                    $valor = $remove_verify_digit == 0 ? substr($nossoNumero, 0, -1) : $nossoNumero;
                                    $dados[] = $valor;
                                }
                            } else {
                                if ($seuNumero !== '') {
                                    if ($remove_barra == 0 && strpos($seuNumero, '/') !== false) {
                                        $partes = explode('/', $seuNumero);
                                        $dados[] = $partes[0];
                                    } else {
                                        $dados[] = $seuNumero;
                                    }
                                }
                            }
                        }
                    }
        
                    if ($validPix == 0 && str_contains($substring, 'BAIXAS')) {
                        $modo = 'BAIXAS';
                    }
        
                    if ($modo === 'BAIXAS') {
                        if (!$cabecalhoEncontrado && str_contains($line, 'SEU NUMERO') &&
                            str_contains($line, 'NOSSO NUMERO') &&
                            str_contains($line, 'VENCIMENTO') &&
                            str_contains($line, 'EVENTO')) {
        
                            $cabecalhoEncontrado = true;
                            $posicoes['seu_numero'] = strpos($line, 'SEU NUMERO');
                            $posicoes['nosso_numero'] = strpos($line, 'NOSSO NUMERO');
                            $posicoes['vencimento'] = strpos($line, 'VENCIMENTO');
                            $posicoes['evento'] = strpos($line, 'EVENTO');
                            $posicoes['larguraSeuNumero'] = $posicoes['nosso_numero'] - $posicoes['seu_numero'];
                            $posicoes['larguraNossoNumero'] = $posicoes['vencimento'] - $posicoes['nosso_numero'];
                            $posicoes['larguraEvento'] = $posicoes['evento'] + 16;
                            continue;
                        }
        
                        if ($cabecalhoEncontrado && trim($line) !== '') {
                            $seuNumero = trim(substr($line, $posicoes['seu_numero'], $posicoes['larguraSeuNumero']));
                            $evento = trim(substr($line, $posicoes['evento'], $posicoes['larguraEvento']));
                            $nossoNumero = trim(substr($line, $posicoes['nosso_numero'], $posicoes['larguraNossoNumero']));
        
                            if (str_contains($evento, 'BX PGTO PIX')) {
                                if ($compare == 1 && $nossoNumero !== '') {
                                    $valor = $remove_verify_digit == 0 ? substr($nossoNumero, 0, -1) : $nossoNumero;
                                    $dados[] = $valor;
                                } elseif ($compare == 0 && $seuNumero !== '') {
                                    if ($remove_barra == 0 && strpos($seuNumero, '/') !== false) {
                                        $partes = explode('/', $seuNumero);
                                        $dados[] = $partes[0];
                                    } else {
                                        $dados[] = $seuNumero;
                                    }
                                }
                            }
                        }
                    }
                }
        
                fclose($handle);
                return $dados;
            } else {
                throw new Exception("Erro ao abrir o arquivo.");
            }
        }        
    }
?>