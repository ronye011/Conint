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

        public function __construct($fileBank, $fileCSV, $pix_valid, $number_valid, $compare, $csvSeparate) {
            creanTempFiles();
            $this->csv_fileName = validFileCSV($fileCSV);
            $this->txt_fileName = validFileBankTXT($fileBank);
            $this->pix_valid = $pix_valid;
            $this->number_valid = $number_valid;
            $this->compare = $compare;
            $this->csvSeparate = $csvSeparate;
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

        public static function ExtCompare(Santander $bank) {
            $dataCsv = dataCsvExtract($bank->get_csvName(), $bank->get_csvSeparate());
            $dataBank = dataSantanderExtract($bank->get_bankName(), $bank->get_pixValid(), $bank->get_numberValid());
            
            // Chama a função Comparator com os arrays
            $notFound = Comparator($dataCsv, $dataBank, $bank->get_compare());
            return createResultCsvFile($notFound);
        }
    }
?>