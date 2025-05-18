<?php
require __DIR__ . '/vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Payment;

SDK::setAccessToken("TEST-4822365570526425-050519-215ba645d826f7e7eaaf08fdcb14d090-2426282036");

// Pega os dados enviados pelo webhook
$body = file_get_contents("php://input");
$data = json_decode($body, true);

// Verificação básica
if (!$data || !isset($data["type"]) || $data["type"] !== "payment") {
    http_response_code(400);
    echo "Requisição inválida";
    exit;
}

$paymentId = $data["data"]["id"];
$payment = Payment::find_by_id($paymentId);

// Verifica se o pagamento está aprovado
if ($payment && $payment->status === "approved") {
    $pagamento_id = (int)$payment->external_reference;

    // Conecta ao banco de dados
    $conn = new mysqli('localhost', 'root', 'Duk23092020$$', 'consultorio');
    if ($conn->connect_error) {
        http_response_code(500);
        echo "Erro na conexão com o banco";
        exit;
    }

    // Atualiza o status do pagamento
    $stmt = $conn->prepare("UPDATE pagamento SET status_pagamento = 'pago' WHERE id = ?");
    $stmt->bind_param("i", $pagamento_id);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    http_response_code(200);
    echo "Pagamento atualizado com sucesso";
} else {
    http_response_code(200);
    echo "Pagamento ainda não aprovado";
}
