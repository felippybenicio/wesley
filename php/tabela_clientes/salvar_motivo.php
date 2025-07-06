<?php
include '../conexao.php';
include '../login_empresa/get_id.php';

header('Content-Type: application/json');

// Verifica se empresa está logada
if (!$empresa_id) {
    http_response_code(403);
    echo json_encode(['erro' => 'Empresa não logada.']);
    exit;
}

// Captura e valida os dados
$id = $_POST['id'] ?? null;
$motivo = $_POST['motivo'] ?? null;

if (!is_numeric($id) || trim($motivo) === '') {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos.']);
    exit;
}

$id = intval($id);

// Atualiza motivo e marca como "não atendido"
$sql = "UPDATE agendamento SET motivo_falta = ?, ja_atendido = 'nao' WHERE id = ? AND empresa_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao preparar a query: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sii", $motivo, $id, $empresa_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Motivo salvo e presença marcada como não.']);
} else {
    http_response_code(404);
    echo json_encode(['erro' => 'Agendamento não encontrado ou já estava atualizado.']);
}

$stmt->close();
$conn->close();
