<?php

session_start(); // começa sessão limpa


include '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, senha_inicial FROM cadastro_empresa WHERE email_profissional = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        $empresa_id = $row['id'];
        $senha_hash = $row['senha_inicial'];

        if (password_verify($senha, $senha_hash)) {
            $_SESSION['empresa_id'] = $empresa_id;
            header("Location: ../agendamentos/tela_inicial_empresa.php");
            exit;
        } else {
            echo "Email ou senha inválidos.";
        }
    } else {
        echo "Email ou senha inválidos.";
    }
}
?>
