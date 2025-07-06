<?php
session_start();
include_once '../conexao.php';

// Função para limpar entrada
function limparEntrada($dado) {
    return htmlspecialchars(trim($dado));
}

$email = limparEntrada($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo "Preencha e-mail e senha.";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "E-mail inválido.";
    exit;
}

//////////////////////////////////////////
// 1. Verificar se é um CLIENTE
//////////////////////////////////////////
$sql = "SELECT id, senha_inicial FROM cadastro_empresa WHERE email_profissional = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($senha, $user['senha_inicial'])) {
        session_regenerate_id(true);
        $_SESSION['empresa_id'] = $user['id'];
        header("Location: ../../php/agendamentos/tela_inicial_empresa.php");
        exit;
    }
}

//////////////////////////////////////////
// 2. Verificar se é um ADMIN
//////////////////////////////////////////
$sqlAdmin = "SELECT id, senha FROM monitoramento WHERE email = ?";
$stmtAdmin = $conn->prepare($sqlAdmin);
$stmtAdmin->bind_param("s", $email);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();

if ($resultAdmin && $resultAdmin->num_rows === 1) {
    $admin = $resultAdmin->fetch_assoc();

    if (password_verify($senha, $admin['senha'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: ../../php/monitoramentos/lista_empresas.php");
        exit;
    }
}

echo "Usuário ou senha incorretos.";

$stmt->close();
$stmtAdmin->close();
$conn->close();
?>
