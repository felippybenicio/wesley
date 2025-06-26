<?php
include '../login_empresa/get_id.php';
include '../conexao.php';

header('Content-Type: application/json');

if (!$empresa_id) {
    http_response_code(400);
    echo json_encode(['erro' => 'Empresa não identificada']);
    exit;
}

$stmt = $conn->prepare("SELECT data FROM dia_indisponivel WHERE empresa_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro na preparação da consulta']);
    exit;
}

$stmt->bind_param("i", $empresa_id);
$stmt->execute();

$result = $stmt->get_result();
if ($result === false) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao obter resultados']);
    exit;
}

$datas = [];

while ($row = $result->fetch_assoc()) {
    // Validação extra opcional do formato da data
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $row['data'])) {
        $datas[] = $row['data'];
    }
}

echo json_encode($datas);
exit;
