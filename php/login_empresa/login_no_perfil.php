<?php
session_start();
include_once '../conexao.php';

// Função para limpar entrada
function limparEntrada($dado) {
    return htmlspecialchars(trim($dado));
}

// Captura e validação
$email = limparEntrada($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

// Verificações
if (empty($email) || empty($senha)) {
    echo "Preencha e-mail e senha.";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "E-mail inválido.";
    exit;
}

// Consulta protegida
$sql = "SELECT id, senha_inicial FROM cadastro_empresa WHERE email_profissional = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Erro na preparação da consulta.";
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Verificação do resultado
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($senha, $user['senha_inicial'])) {
        // Regerar sessão para evitar Session Fixation
        session_regenerate_id(true);
        $_SESSION['empresa_id'] = $user['id'];

        // Redirecionamento seguro
        header("Location: ../../php/agendamentos/tela_inicial_empresa.php");
        exit;
    } else {
        echo "Senha incorreta.";
    }
} else {
    echo "Usuário não encontrado.";
}

// Finalização
$stmt->close();
$conn->close();
?>
