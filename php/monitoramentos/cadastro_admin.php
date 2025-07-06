<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Exibir erros apenas em localhost
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Gera token CSRF se ainda não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include '../conexao.php';

    // Validação CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        exit("Falha de segurança no envio do formulário.");
    }

    // Sanitização e validação de entrada
    $nome = htmlspecialchars(trim($_POST['nome'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (!$email) {
        echo "E-mail inválido.";
        exit;
    }

    if (empty($nome)) {
        echo "Nome é obrigatório.";
        exit;
    }

    if ($senha !== $confirmar) {
        echo "As senhas não coincidem.";
        exit;
    }

    if (strlen($senha) < 6) {
        echo "A senha deve ter no mínimo 6 caracteres.";
        exit;
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO monitoramento (nome, email, senha) VALUES (?, ?, ?)");
    if (!$stmt) {
        error_log("Erro prepare: " . $conn->error);
        exit("Erro interno.");
    }

    $stmt->bind_param("sss", $nome, $email, $hash);

    if ($stmt->execute()) {
        header("Location: lista_empresas.php?status=ok");
        exit;
    } else {
        error_log("Erro cadastro admin: " . $stmt->error);
        echo "Erro ao cadastrar. E-mail já usado?";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- Formulário HTML seguro -->
<form method="POST" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <input type="text" name="nome" placeholder="Nome" maxlength="100" required><br>
    <input type="email" name="email" placeholder="Seu email" maxlength="150" required><br>
    <input type="password" name="senha" placeholder="Senha (mín. 6 caracteres)" minlength="6" required><br>
    <input type="password" name="confirmar" placeholder="Confirmar senha" minlength="6" required><br>
    <button type="submit">Cadastrar admin</button>
</form>

<a href="../../pages/login_empresa/tela_login.html">Voltar</a>
