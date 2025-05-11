<?php
    header('Content-Type: application/json');
    include_once('../controlers/BancoDoBrasil.php');
    include_once('../controlers/Santander.php');
    include_once('../controlers/Sicredi.php');
    include_once('../controlers/ModoBank.php');
    include_once('../controlers/IXCRec.php');
    include_once('../controlers/Xlsx.php');
    include_once('../controlers/Csv.php');
    $method = $_SERVER['REQUEST_METHOD'];
    $route = $_GET['route'] ?? '';
    
    // Verifica qual requisição foi feita
    switch ($route) {

        // Extrator de dados bancários -------------------------------------------------------------------------------------------------------------------------------------------------------------

        case '0':
            if ($method === 'POST') {
                try {
                    // Pegando os dados do formulário
                    $compare = $_POST['compare'] ?? null;
                    $number_valid = $_POST['number_valid'] ?? null;
                    $pix_valid = $_POST['pix_valid'] ?? null;
                    $fileBank = $_FILES['fileBank'] ?? null;
                    $remove_barra = $_POST['remove_barra'] ?? null;
                    $remove_verify_digit = $_POST['remove_verify_digit'] ?? null;
                    $dataCsv = $_POST['dataCSV'] ?? null;

                    $bank = new Santander($fileBank, $pix_valid, $number_valid, $compare, $remove_barra, $remove_verify_digit);
                    $result = $bank::ExtCompare($bank, $dataCsv);
                    echo json_encode(["success" => true, "data" => $result]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            }
            break;

        case '1':
            if ($method === 'POST') {
                try {
                    // Pegando os dados do formulário
                    $compare = $_POST['compare'] ?? null;
                    $number_valid = $_POST['number_valid'] ?? null;
                    $pix_valid = $_POST['pix_valid'] ?? null;
                    $fileBank = $_FILES['fileBank'] ?? null;
                    $remove_barra = $_POST['remove_barra'] ?? null;
                    $remove_verify_digit = $_POST['remove_verify_digit'] ?? null;
                    $dataCsv = $_POST['dataCSV'] ?? null;

                    $bank = new Sicredi($fileBank, $pix_valid, $number_valid, $compare, $remove_barra, $remove_verify_digit);
                    $result = $bank::ExtCompare($bank, $dataCsv);
                    echo json_encode(["success" => true, "data" => $result]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            }
            break;

        case '2':
            if ($method === 'POST') {
                try {
                    // Pegando os dados do formulário
                    $compare = $_POST['compare'] ?? null;
                    $number_valid = $_POST['number_valid'] ?? null;
                    $pix_valid = $_POST['pix_valid'] ?? null;
                    $fileBank = $_FILES['fileBank'] ?? null;
                    $dataCsv = $_POST['dataCSV'] ?? null;

                    $bank = new BB($fileBank, $pix_valid, $number_valid, $compare);
                    $result = $bank::ExtCompare($bank, $dataCsv);
                    echo json_encode(["success" => true, "data" => $result]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            }
            break;

        case '3':
            if ($method === 'POST') {
                try {
                    // Pegando os dados do formulário
                    $fileBank = $_FILES['fileBank'] ?? null;
                    $dataCsv = $_POST['dataCSV'] ?? null;
                    $compare = $_POST['compare'] ?? null;

                    $bank = new ModoBank($fileBank, $compare);
                    $result = $bank::ExtCompare($bank, $dataCsv);
                    echo json_encode(["success" => true, "data" => $result]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            }
            break;

        // Extrator de dados para planilhas ----------------------------------------------------------------------------------------------------------------------------------------------

        case '998':
            if ($method === 'POST') {
                try {
                    // Pegando os dados do formulário
                    $file = $_FILES['file'] ?? null;
                    $nameColumnFile = $_POST['nameColumnFile'] ?? null;
                    $csvSeparate = $_POST['csvSeparate'] ?? null;

                    $csv = new CSV($file, $csvSeparate, $nameColumnFile);
                    $result = $csv->dataExtractCSV($csv); 
                    echo json_encode(["success" => true, "data" => $result]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            }
            break;

        case '999':
            if ($method === 'POST') {
                try {
                    // Pegando os dados do formulário
                    $file = $_FILES['file'] ?? null;
                    $nameColumnFile = $_POST['nameColumnFile'] ?? null;

                    $xlsx = new XLSX($file, $nameColumnFile);
                    $result = $xlsx->dataExtractXLSX($xlsx); 
                    echo json_encode(["success" => true, "data" => $result]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            }
            break;

        // Relatório de recebimentos IXC Provedor -------------------------------------------------------------------------------------------------------------------------------------------------

        case '1000':
            if ($method === 'POST') {
                try {
                    // Pegando os dados do formulário
                    $file = $_FILES['file'] ?? null;

                    $ixcRec = new IXCRec($file);
                    $result = $ixcRec->dataExtractIXCRec($ixcRec); 
                    echo json_encode(["success" => true, "data" => $result]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "message" => $e->getMessage()]);
                }
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Rota inválida."]);
            break;
    }
?>