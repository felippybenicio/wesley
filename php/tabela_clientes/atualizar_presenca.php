<?php
include '../conexao.php';
include '../login_empresa/get_id.php';

header("Content-Type: application/json");

$id = $_POST['id'] ?? null;
$presenca = $_POST['presenca'] ?? null;

// Validação básica
if (!is_numeric($id) || $presenca !== 'sim') {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos.']);
    exit;
}

// Atualiza apenas se o agendamento for da empresa logada
$sql = "UPDATE agendamento SET ja_atendido = 'sim' WHERE id = ? AND empresa_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $empresa_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Presença atualizada.']);
} else {
    echo json_encode(['erro' => 'Nenhum registro foi atualizado.']);
}

$stmt->close();
