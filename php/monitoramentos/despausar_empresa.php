<?php
session_start();
include '../conexao.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

if (!isset($_POST['id'], $_POST['senha'])) {
    echo json_encode(['erro' => 'Parâmetros insuficientes.']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$idEmpresa = intval($_POST['id']);
$senha = $_POST['senha'];

// Busca a senha do admin logado
$stmt = $conn->prepare("SELECT senha FROM monitoramento WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($senhaCorreta);

if (!$stmt->fetch()) {
    echo json_encode(['erro' => 'Admin não encontrado.']);
    exit;
}
$stmt->close();

// Verifica a senha
if (!password_verify($senha, $senhaCorreta)) {
    echo json_encode(['erro' => 'Senha incorreta.']);
    exit;
}

// Atualiza o status para ativo
$stmt = $conn->prepare("UPDATE cadastro_empresa SET status = 'ativo' WHERE id = ?");
$stmt->bind_param("i", $idEmpresa);

if ($stmt->execute()) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['erro' => 'Erro ao despausar empresa.']);
}
$stmt->close();
