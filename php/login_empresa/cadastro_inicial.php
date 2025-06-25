<?php
include_once '../conexao.php';
session_start();

// Função para limpar entrada
function limparEntrada($dado) {
    return htmlspecialchars(trim($dado));
}

// Captura e sanitiza os dados
$empresa = limparEntrada($_POST['empresa'] ?? '');
$ramo = limparEntrada($_POST['ramo'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$senha = $_POST['senha'] ?? '';
$dataCadastro = date('Y-m-d H:i:s');

// ⚠️ Verificação de campos obrigatórios
if (empty($empresa) || empty($ramo) || empty($email) || empty($senha)) {
    echo "Preencha todos os campos obrigatórios.";
    exit;
}

// ⚠️ Verificação de e-mail válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "E-mail inválido.";
    exit;
}

// ⚠️ Verificação mínima da senha (ex: 8 caracteres)
if (strlen($senha) < 8) {
    echo "A senha deve ter no mínimo 8 caracteres.";
    exit;
}

// 🔐 Criptografia da senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

// SQL preparado para evitar SQL Injection
$sql = "INSERT INTO cadastro_empresa 
        (nome_empresa, ramo_empresa, email_profissional, senha_inicial, dia_cadastrado)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Erro na preparação da query: " . $conn->error;
    exit;
}

$stmt->bind_param("sssss", $empresa, $ramo, $email, $senhaHash, $dataCadastro);

// Execução segura
if ($stmt->execute()) {
    
} else {
    echo "Erro ao cadastrar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
