<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Consulta empresa pelo email
    $stmt = $conn->prepare("SELECT id, senha_inicial FROM cadastro_empresa WHERE email_profissional = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verifica senha (supondo senha hash, ou troque para strcmp se não)
        if (password_verify($senha, $row['senha_inicial'])) {
            // Login ok - salva o id da empresa na sessão
            $_SESSION['empresa_id'] = $row['id'];
            $_SESSION['empresa_email'] = $email;

            // Redireciona para a página inicial da empresa
            header("Location: /pagina_inicio.php");
            exit;
        } else {
            echo "Senha incorreta.";
        }
    } else {
        echo "Empresa não encontrada.";
    }
}
?>
