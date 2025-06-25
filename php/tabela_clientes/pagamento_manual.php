<?php
include '../conexao.php';
include '../login_empresa/get_id.php';

if (!$empresa_id) {
    echo "erro: empresa não logada";
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo "erro: id inválido";
    exit;
}

$id_pagamento = intval($_POST['id']);

// Verifica se a preparação da query funcionou
$sql = "
    UPDATE pagamento 
    SET status_pagamento = 'pago', created_at = NOW() 
    WHERE id = ? AND empresa_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "erro: prepare falhou - " . $conn->error;
    exit;
}

$stmt->bind_param("ii", $id_pagamento, $empresa_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo "ok";
} else {
    echo "erro: pagamento não encontrado ou não pertence à empresa";
}
