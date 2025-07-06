<?php
session_start();
include '../conexao.php';
header('Content-Type: application/json');

// Mostrar erros apenas em desenvolvimento
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Verifica se admin está logado
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['erro' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

// Validação dos dados recebidos
if (!isset($_POST['id'], $_POST['senha'])) {
    echo json_encode(['erro' => 'Parâmetros insuficientes.']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$idEmpresa = intval($_POST['id']);
$senha = trim($_POST['senha']);

// Busca senha do admin
$stmt = $conn->prepare("SELECT senha FROM monitoramento WHERE id = ?");
if (!$stmt) {
    echo json_encode(['erro' => 'Erro interno ao preparar consulta.']);
    exit;
}
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($senhaCorreta);

if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(['erro' => 'Admin não encontrado.']);
    exit;
}
$stmt->close();

// Verifica senha
if (!password_verify($senha, $senhaCorreta)) {
    echo json_encode(['erro' => 'Senha incorreta.']);
    exit;
}

// Pausa a empresa (altera status)
$stmt = $conn->prepare("UPDATE cadastro_empresa SET status = 'pausado' WHERE id = ?");
if (!$stmt) {
    echo json_encode(['erro' => 'Erro ao preparar update.']);
    exit;
}
$stmt->bind_param("i", $idEmpresa);

if ($stmt->execute()) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['erro' => 'Erro ao pausar empresa.']);
}
$stmt->close();
