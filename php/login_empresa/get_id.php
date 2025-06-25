<?php
session_start();

if (!isset($_SESSION['empresa_id'])) {
    http_response_code(401); // não autorizado
    echo json_encode(['erro' => 'Acesso não autorizado']);
    exit;
}

$empresa_id = $_SESSION['empresa_id'];
