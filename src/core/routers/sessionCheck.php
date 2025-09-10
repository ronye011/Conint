<?php
// CORS Headers
header("Access-Control-Allow-Origin: http://localhost:5173"); // ou * se não for usar cookies
header("Access-Control-Allow-Credentials: true"); // necessário para enviar cookies (sessão)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Se for uma requisição OPTIONS (pré-vôo), encerra aqui:
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

session_start();
include_once('../controlers/Login.php');

// Verifica se a sessão está ativa
if (Login::validLogin() === true) {
    echo json_encode([
        'authenticated' => true,
    ]);
} else {
    http_response_code(401); // não autorizado
    echo json_encode(['authenticated' => false]);
}
exit;