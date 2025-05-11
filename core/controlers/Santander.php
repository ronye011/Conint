<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    include_once('../helpers/Tools.php');
    
    class Santander {
        private $txt_fileName;
        private $pix_valid;
        private $number_valid;
        private $compare;
        private $notFoundFileName;
        private $remove_barra;
        private $remove_verify_digit;

        public function __construct($fileBank, $pix_valid, $number_valid, $compare, $remove_barra, $remove_verify_digit) {
            $this->txt_fileName = validFileTXT($fileBank);
            $this->pix_valid = $pix_valid;
            $this->number_valid = $number_valid;
            $this->compare = $compare;
            $this->remove_barra = $remove_barra;
            $this->remove_verify_digit = $remove_verify_digit;
        }

        public function get_bankName() {
            return $this -> txt_fileName;
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

        public static function ExtCompare(Santander $bank, $dataCsv) {
            $dataBank = Santander::dataSantanderExtract($bank->get_bankName(), $bank->get_pixValid(), $bank->get_numberValid(), $bank->get_remove_barra(), $bank->get_remove_verify_digit());
            
            // Chama a função Comparator com os arrays
            $dataCsv = explode(",", $dataCsv);
            return Comparator($dataBank, $dataCsv, $bank->get_compare());
        }

        private static function dataSantanderExtract($caminhoArquivoTmp, $validPix, $compare, $remove_barra, $remove_verify_digit) {
            $validPix = (int) $validPix;
            $compare = (int) $compare;
            $dados = [];
            $posicoes = [];
            $modo = null;
            $cabecalhoEncontrado = false;
        
            $handle = fopen($caminhoArquivoTmp, 'r');
            if (!$handle) {
                throw new Exception("Erro ao abrir o arquivo.");
            }
        
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line);
                $linhaLimpa = trim($line);
        
                if ($linhaLimpa === '' || substr($line, 0, 1) === '1' || str_contains(substr($line, 1, 5), 'TOTAL')) {
                    $modo = null;
                    $cabecalhoEncontrado = false;
                    continue;
                }
        
                $substring = substr($line, 61, 11);
        
                // Detectar modo
                if ($validPix != 0 && str_contains($substring, 'LIQUIDACOES')) {
                    $modo = 'LIQUIDACOES';
                    $cabecalhoEncontrado = false;
                    continue;
                }
                if (($validPix == 0 || $validPix == 2) && str_contains($substring, 'BAIXAS')) {
                    $modo = 'BAIXAS';
                    $cabecalhoEncontrado = false;
                    continue;
                }
        
                if ($modo === 'LIQUIDACOES' || $modo === 'BAIXAS') {
                    if (!$cabecalhoEncontrado && self::detectarCabecalho($line, $modo, $posicoes)) {
                        $cabecalhoEncontrado = true;
                        continue;
                    }
        
                    if ($cabecalhoEncontrado && $linhaLimpa !== '') {
                        $dados[] = self::extrairLinhaSantander($line, $modo, $posicoes, $compare, $remove_barra, $remove_verify_digit);
                    }
                }
            }
        
            fclose($handle);
            return array_filter($dados); // remove entradas nulas
        }
        
        private static function detectarCabecalho($line, $modo, &$posicoes) {
            if ($modo === 'LIQUIDACOES') {
                if (str_contains($line, 'SEU NUMERO') && str_contains($line, 'NOSSO NUMERO') && str_contains($line, 'VENCTO')) {
                    $posicoes = [
                        'seu_numero' => strpos($line, 'SEU NUMERO'),
                        'nosso_numero' => strpos($line, 'NOSSO NUMERO') - 1,
                        'valor_titulo' => strpos($line, 'VALOR TITULO'),
                        'juros_mora' => strpos($line, 'JUROS/MORA'),
                        'vencimento' => strpos($line, 'VENCTO'),
                        'evento' => strpos($line, 'EVENTO'),
                    ];
                    $posicoes += [
                        'larguraEvento' => $posicoes['evento'] + 7,
                        'larguraSeuNumero' => $posicoes['nosso_numero'] - $posicoes['seu_numero'],
                        'larguraNossoNumero' => $posicoes['vencimento'] - $posicoes['nosso_numero'],
                        'larguraValorTitulo' => $posicoes['juros_mora'] - $posicoes['valor_titulo'],
                    ];
                    return true;
                }
            } elseif ($modo === 'BAIXAS') {
                if (str_contains($line, 'SEU NUMERO') && str_contains($line, 'NOSSO NUMERO') &&
                    str_contains($line, 'VENCIMENTO') && str_contains($line, 'EVENTO')) {
        
                    $posicoes = [
                        'seu_numero' => strpos($line, 'SEU NUMERO'),
                        'nosso_numero' => strpos($line, 'NOSSO NUMERO'),
                        'vencimento' => strpos($line, 'VENCIMENTO'),
                        'evento' => strpos($line, 'EVENTO'),
                        'valor_titulo' => strpos($line, 'VALOR TITULO'),
                        'tarifas' => strpos($line, 'TARIFAS'),
                    ];
                    $posicoes += [
                        'larguraSeuNumero' => $posicoes['nosso_numero'] - $posicoes['seu_numero'],
                        'larguraNossoNumero' => $posicoes['vencimento'] - $posicoes['nosso_numero'],
                        'larguraEvento' => $posicoes['evento'] + 16,
                        'larguraValorTitulo' => $posicoes['tarifas'] - $posicoes['valor_titulo'],
                    ];
                    return true;
                }
            }
            return false;
        }
        
        private static function extrairLinhaSantander($line, $modo, $pos, $compare, $remove_barra, $remove_verify_digit) {
            $evento = trim(substr($line, $pos['evento'], $pos['larguraEvento']));
            $valor = trim(substr($line, $pos['valor_titulo'], $pos['larguraValorTitulo']));
            $seuNumero = trim(substr($line, $pos['seu_numero'], $pos['larguraSeuNumero']));
            $nossoNumero = trim(substr($line, $pos['nosso_numero'], $pos['larguraNossoNumero']));
        
            if ($modo === 'BAIXAS' && !str_contains($evento, 'BX PGTO PIX')) {
                return null;
            }
        
            if ($compare == 1 && $nossoNumero !== '') {
                $data = ($remove_verify_digit == 0) ? substr($nossoNumero, 0, -1) : $nossoNumero;
                return [
                    'data' => $data,
                    'valor' => $valor,
                    'descricao' => $evento
                ];
            }
        
            if ($compare == 0 && $seuNumero !== '') {
                if ($remove_barra == 0 && strpos($seuNumero, '/') !== false) {
                    $seuNumero = explode('/', $seuNumero)[0];
                }
                return [
                    'data' => $seuNumero,
                    'valor' => $valor,
                    'descricao' => $evento
                ];
            }
        
            return null;
        }             
    }
?>