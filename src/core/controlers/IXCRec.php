<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
    include_once(__DIR__ . '/../dependence/vendor/autoload.php');
    include_once('../controlers/Xlsx.php');
    include_once('../helpers/Tools.php');

    class IXCRec {
        private $ixcRecFile;

        public function __construct($ixcRecFile) {
            $this->ixcRecFile = validFilePDF($ixcRecFile);
        }

        public function dataExtractIXCRec(IXCRec $ixcRec) {
            $pdfContent = file_get_contents($this->ixcRecFile['tmp_name']);

            // Verifica se o arquivo .PDF existe
            if (!file_exists($this->ixcRecFile['tmp_name'])) {
                throw new Exception("Arquivo PDF não encontrado.");
            }

            $process = proc_open(
                '/opt/fcsv-venv/bin/python3 /var/www/src/core/helpers/Convert.py',
                [
                    0 => ['pipe', 'r'],  // stdin
                    1 => ['pipe', 'w'],  // stdout
                    2 => ['pipe', 'w']   // stderr
                ],
                $pipes
            );

            if (is_resource($process)) {
                fwrite($pipes[0], $pdfContent);
                fclose($pipes[0]);

                $excelData = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                $errorOutput = stream_get_contents($pipes[2]);
                fclose($pipes[2]);

                $exitCode = proc_close($process);

                if ($exitCode === 0) {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment; filename="convertido.xlsx"');
                    header('Content-Length: ' . strlen($excelData));

                    $tmpXlsx = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';
                    file_put_contents($tmpXlsx, $excelData);

                    // Simula um $_FILES-like array
                    $fakeUploadedFile = [
                        'name' => 'convertido.xlsx',
                        'full_path' => 'convertido.xlsx',
                        'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'tmp_name' => $tmpXlsx,
                        'error' => 0,
                        'size' => filesize($tmpXlsx),
                    ];

                    //Extração dos dados XLSX
                    $xlsx = new XLSX($fakeUploadedFile, "ID Rec.");
                    $result = $xlsx->dataExtracIXC($xlsx);
                    unlink($tmpXlsx);
                    return $result;
                } else {
                    echo "<h3>Erro na execução do script Python:</h3><pre>$errorOutput</pre>";
                }
            }
        }
    }
?>