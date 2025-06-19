<?php
include '../conexao.php';
include '../login_empresa/get_id.php';

$id = $_POST['id'] ?? null;
$presenca = $_POST['presenca'] ?? null;

if (!$id || $presenca !== 'sim') {
    echo "Dados inválidos";
    exit;
}

$sql = "UPDATE agendamentos SET ja_atendido = 'sim' WHERE id = ? AND empresa_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $empresa_id);
$stmt->execute();

echo "Presença atualizada";
