<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    use PhpOffice\PhpSpreadsheet\IOFactory;
    include_once(__DIR__ . '/../dependence/vendor/autoload.php');
    include_once('../helpers/Tools.php');

    class ModoBank {
        private $bank_fileName;
        private $compare;

        public function __construct($fileBank, $compare) {
            $this->bank_fileName = validFileCSV($fileBank);
            $this->compare = $compare;
        }

        public function get_bank_fileName() {
            return $this->bank_fileName;
        }

        public function get_compare() {
            return $this->compare;
        }

        public static function ExtCompare(ModoBank $bank, $dataCsv) {
            $dataBank = ModoBank::dataModoBankExtract($bank);
            
            // Chama a função Comparator com os arrays
            $dataCsv = explode(",", $dataCsv);
            return Comparator($dataBank, $dataCsv, $bank->get_compare());
        }

        private static function dataModoBankExtract(ModoBank $bank) {
            // Abre o arquivo temporário diretamente
            if (($handle = fopen($bank->get_bank_fileName()['tmp_name'], 'r')) !== false) {
                $cabecalho = fgetcsv($handle, 1000, ';');
            
                $dados = [];
            
                // Defina as palavras que você quer procurar
                $palavrasProcuradas = ["Referência 2", "Valor", "Referência 3"];
                $indices = [];

                // Localiza os índices das colunas
                foreach ($palavrasProcuradas as $palavra) {
                    $indiceColuna = array_search($palavra, $cabecalho);
        
                    if ($indiceColuna === false) {
                        throw new Exception("Coluna '$palavra' não encontrada no cabeçalho.");
                    }
        
                    $indices[$palavra] = $indiceColuna;
                }
            
                while (($linha = fgetcsv($handle, 1000, ";")) !== false) {
                    if (count($linha) !== count($cabecalho)) {
                        throw new Exception('O número de colunas na linha não corresponde ao cabeçalho.');
                    }
        
                    // Usa os índices corretos
                    $dados[] = [
                        'data' => $linha[$indices["Referência 2"]],
                        'valor' => $linha[$indices["Valor"]],
                        'descricao' => $linha[$indices["Referência 3"]],
                    ];
                }
            
                fclose($handle);
                return $dados;
            } else {
                throw new Exception("Erro ao abrir o arquivo.");
            }  
        }
    }
?>