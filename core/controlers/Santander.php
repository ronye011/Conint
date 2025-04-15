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

        public function __construct($fileBank, $fileCSV, $pix_valid, $number_valid, $compare, $csvSeparate, $remove_barra) {
            creanResultFiles();
            creanTempFiles();
            $this->csv_fileName = validFileCSV($fileCSV);
            $this->txt_fileName = validFileBankTXT($fileBank);
            $this->pix_valid = $pix_valid;
            $this->number_valid = $number_valid;
            $this->compare = $compare;
            $this->csvSeparate = $csvSeparate;
            $this->remove_barra = $remove_barra;
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

        public static function ExtCompare(Santander $bank) {
            $dataCsv = dataCsvExtract($bank->get_csvName(), $bank->get_csvSeparate());
            $dataBank = Santander::dataSantanderExtract($bank->get_bankName(), $bank->get_pixValid(), $bank->get_numberValid(), $bank->get_remove_barra());
            creanTempFiles();
            
            // Chama a função Comparator com os arrays
            $notFound = Comparator($dataCsv, $dataBank, $bank->get_compare());
            return createResultCsvFile($notFound);
        }

        private static function dataSantanderExtract($nameBank, $validPix, $compare, $remove_barra) {
            $caminhoArquivo = realpath(__DIR__ . "/../../filesTemp/") . "/" . $nameBank;
    
            $validPix = (int) $validPix;
            $compare = (int) $compare;
    
            $dados = [];
            $posicoes = [];
            $modo = null;
            $cabecalhoEncontrado = false;
    
            $handle = fopen($caminhoArquivo, 'r');
    
            if ($handle) {
                // Lê o arquivo linha por linha
                while (($line = fgets($handle)) !== false) {
                    $line = rtrim($line);
    
                    // Detecta fim de seção (linha que começa com "1")
                    if ((substr($line, 0, 1) === '1') || (substr($line, 1, 5) === 'TOTAL')) {
                        $modo = null;
                        $cabecalhoEncontrado = false;
                    }
    
                    $substring = substr($line, 61, 11); // Extrai 11 caracteres a partir da posição 61
                    // Identifica se entrou na seção de LIQUIDACOES
                    if (str_contains($substring, 'LIQUIDACOES')) {
                        $modo = 'LIQUIDACOES';
                    }
    
                    // Captura LIQUIDACOES
                    if ($modo === 'LIQUIDACOES') {
                        // Encontrar o cabeçalho com os termos "SEU NUMERO" e "NOSSO NUMERO" e "VENCIMENTO"
                        if (!$cabecalhoEncontrado && str_contains($line, 'SEU NUMERO') && str_contains($line, 'NOSSO NUMERO') && str_contains($line, 'VENCTO')) {
                            $cabecalhoEncontrado = true;
                            // Detecta posições de início das colunas dinamicamente
                            $posicoes['seu_numero'] = strpos($line, 'SEU NUMERO');
                            $posicoes['nosso_numero'] = strpos($line, 'NOSSO NUMERO') - 1;
                            $posicoes['vencimento'] = strpos($line, 'VENCTO');
    
                            // Também define larguras com base na distância entre os campos
                            $posicoes['larguraSeuNumero'] = $posicoes['nosso_numero'] - $posicoes['seu_numero'];
                            $posicoes['larguraNossoNumero'] = $posicoes['vencimento'] - $posicoes['nosso_numero'];
                            continue;
                        }
    
                        if ($cabecalhoEncontrado && trim($line) !== '') {
                            // Extrai a substring para SEU NUMERO
                            $seuNumero = trim(substr($line, $posicoes['seu_numero'], $posicoes['larguraSeuNumero']));
                            // Extrai a substring para NOSSO NUMERO
                            $nossoNumero = trim(substr($line, $posicoes['nosso_numero'], $posicoes['larguraNossoNumero']));
                            if ($compare == 1) {
                                if ($nossoNumero !== '' && $nossoNumero != null) {
                                    array_push($dados, $nossoNumero);
                                }
                            }
                            else {
                                if ($seuNumero !== '' && $seuNumero != null) {
                                    if ($remove_barra == 0) {
                                        if (strpos($seuNumero, '/') !== false) {
                                            // Pega tudo após a barra "/"
                                            $partes = explode('/', $seuNumero);
                                            array_push($dados, $partes[0]);
                                        } else {
                                            array_push($dados, $seuNumero);
                                        }
                                    }
                                    else {
                                        array_push($dados, $seuNumero);
                                    }
                                }
                            }
                        }
                    }
    
                    if ($validPix == 0) {
                        // Identifica se entrou na seção de BAIXAS
                        if (str_contains($substring, 'BAIXAS')) {
                            $modo = 'BAIXAS';
                        }
    
                        // Captura BAIXAS com "BX PGTO PIX"
                        if ($modo === 'BAIXAS') {
                            // Encontrar o cabeçalho com os termos "SEU NUMERO" e "NOSSO NUMERO" e "VENCIMENTO"
                            if (!$cabecalhoEncontrado && str_contains($line, 'SEU NUMERO') && 
                                str_contains($line, 'NOSSO NUMERO') && 
                                str_contains($line, 'VENCIMENTO') &&
                                str_contains($line, 'EVENTO')) {
                                $cabecalhoEncontrado = true;
                                // Detecta posições de início das colunas dinamicamente
                                $posicoes['seu_numero'] = strpos($line, 'SEU NUMERO');
                                $posicoes['nosso_numero'] = strpos($line, 'NOSSO NUMERO');
                                $posicoes['vencimento'] = strpos($line, 'VENCIMENTO');
                                $posicoes['evento'] = strpos($line, 'EVENTO');
    
                                // Também define larguras com base na distância entre os campos
                                $posicoes['larguraSeuNumero'] = $posicoes['nosso_numero'] - $posicoes['seu_numero'];
                                $posicoes['larguraNossoNumero'] = $posicoes['vencimento'] - $posicoes['nosso_numero'];
                                $posicoes['larguraEvento'] = $posicoes['evento'] + 16;
                                continue;
                            }
    
                            if ($cabecalhoEncontrado && trim($line) !== '') {
    
                                $seuNumero = trim(substr($line, $posicoes['seu_numero'], $posicoes['larguraSeuNumero']));
                                $evento = trim(substr($line, $posicoes['evento'], $posicoes['larguraEvento']));
                                $nossoNumero = trim(substr($line, $posicoes['nosso_numero'], $posicoes['larguraNossoNumero']));
                                if ($compare == 1) {
                                    if ($nossoNumero !== '' && $nossoNumero != null && str_contains($evento, 'BX PGTO PIX')) {
                                        array_push($dados, $nossoNumero);
                                    }
                                }
                            
                                else {
                                    if ($seuNumero !== '' && $seuNumero != null && str_contains($evento, 'BX PGTO PIX')) {
                                        if ($remove_barra == 0) {
                                            if (strpos($seuNumero, '/') !== false) {
                                                // Pega tudo após a barra "/"
                                                $partes = explode('/', $seuNumero);
                                                array_push($dados, $partes[0]);
                                            } else {
                                                array_push($dados, $seuNumero);
                                            }
                                        }
                                        else {
                                            array_push($dados, $seuNumero);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
    
                // Fecha o arquivo
                fclose($handle);
                return $dados;
            } else {
                throw new Exception("Erro ao abrir o arquivo.");
            }
        }
    }
?>