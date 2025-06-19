<?php
include '../conexao.php';
include '../login_empresa/get_id.php';

$id = $_POST['id'] ?? null;
$motivo = $_POST['motivo'] ?? null;

if (!$id || trim($motivo) === '') {
    echo "Dados inválidos";
    exit;
}

// Aqui ele SALVA o motivo E marca como "nao"
$sql = "UPDATE agendamento SET motivo_falta = ?, ja_atendido = 'nao' WHERE id = ? AND empresa_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $motivo, $id, $empresa_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "ok";
} else {
    echo "Erro ao atualizar";
}
