<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include '../conexao.php';

    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($senha !== $confirmar) {
        echo "As senhas não coincidem.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "E-mail inválido.";
        exit;
    }

    if (empty($nome)) {
        echo "Nome é obrigatório.";
        exit;
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO monitoramento (nome, email, senha) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Erro no prepare: " . $conn->error);
    }

    $stmt->bind_param("sss", $nome, $email, $hash);

    if ($stmt->execute()) {
        echo "Administrador cadastrado com sucesso.";
        header("Location: lista_empresas.php");
        exit;
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- Formulário HTML -->
<form method="POST">
    <input type="text" name="nome" placeholder="Nome" required><br>
    <input type="email" name="email" placeholder="Seu email" required><br>
    <input type="password" name="senha" placeholder="Senha" required><br>
    <input type="password" name="confirmar" placeholder="Confirmar senha" required><br>
    <button type="submit">Cadastrar admin</button>
</form>
<a href="login_admin.php">Voltar</a>
