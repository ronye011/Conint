<?php
    header('Content-Type: application/json');
    include_once('../controlers/Santander.php');
    $method = $_SERVER['REQUEST_METHOD'];
    $route = $_GET['route'] ?? '';
    
    // Verifica qual requisição foi feita
    switch ($route) {
        case '0':
            if ($method === 'POST') {
                try {
                    // Pegando os dados do formulário
                    $compare = $_POST['compare'] ?? null;
                    $number_valid = $_POST['number_valid'] ?? null;
                    $pix_valid = $_POST['pix_valid'] ?? null;
                    $fileCSV = $_FILES['fileCSV'] ?? null;
                    $fileBank = $_FILES['fileBank'] ?? null;
                    $csvSeparate = $_POST['csvSeparate'] ?? null;
                    $remove_barra = $_POST['remove_barra'] ?? null;
                    $remove_verify_digit = $_POST['remove_verify_digit'] ?? null;

                    $bank = new Santander($fileBank, $fileCSV, $pix_valid, $number_valid, $compare, $csvSeparate, $remove_barra, $remove_verify_digit);
                    $result = $bank::ExtCompare($bank);
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