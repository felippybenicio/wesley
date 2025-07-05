<?php
session_start(); // SEMPRE no topo, antes de qualquer saída

include '../conexao.php';

$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $stmt = $conn->prepare("SELECT id, nome, senha FROM monitoramento WHERE email = ?");
    if (!$stmt) {
        die("Erro no prepare(): " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $nome, $senha_hash);
        $stmt->fetch();

        if (password_verify($senha, $senha_hash)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_nome'] = $nome; // SALVA o nome para usar depois
            header("Location: lista_empresas.php");
            exit;
        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "Email não encontrado.";
    }
    $stmt->close();
    $conn->close();
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Seu email" required><br>
    <input type="password" name="senha" placeholder="Sua senha" required><br>
    <button type="submit">Entrar</button>
</form>

<a href="cadastro_admin.php">cadastrar</a>

<?php if ($erro): ?>
    <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>
