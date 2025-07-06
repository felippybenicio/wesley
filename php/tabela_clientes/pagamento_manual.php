<?php
include '../conexao.php';
include '../login_empresa/get_id.php';

header('Content-Type: application/json');

if (!$empresa_id) {
    http_response_code(403);
    echo json_encode(['erro' => 'Empresa não logada.']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID inválido.']);
    exit;
}

$id_pagamento = intval($_POST['id']);

$sql = "
    UPDATE pagamento 
    SET status_pagamento = 'pago', created_at = NOW() 
    WHERE id = ? AND empresa_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro na preparação da query: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $id_pagamento, $empresa_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Pagamento atualizado com sucesso.']);
} else {
    http_response_code(404);
    echo json_encode(['erro' => 'Pagamento não encontrado ou não pertence à empresa.']);
}

$stmt->close();
$conn->close();
