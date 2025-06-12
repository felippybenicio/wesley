<?php
include '../conexao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'];
    $nome = $_POST['empresa'];
    $ramo = $_POST['ramo'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $data = date('Y-m-d');

    if (empty($id)) {
        // Cadastro novo - primeiro insere a empresa
        $stmt = $conn->prepare("INSERT INTO cadastro_empresa 
            (nome_empresa, ramo_empresa, email_profissional, senha_inicial, dia_criacao) 
            VALUES (?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Erro no prepare: " . $conn->error);
        }

        $stmt->bind_param("sssss", $nome, $ramo, $email, $senha, $data);
        $stmt->execute();

        if ($stmt->error) {
            die("Erro ao cadastrar empresa: " . $stmt->error);
        }

        $empresa_id = $conn->insert_id; // Agora temos o ID da empresa

        // Inserir valores padrão na tabela horario_config
        // $stmt = $conn->prepare("INSERT INTO horario_config 
        //     (empresa_id) 
        //     VALUES (?)");
        // $stmt->bind_param("i", $empresa_id);
        // $stmt->execute();
        // if ($stmt->error) {
        //     die("Erro ao cadastrar horário: " . $stmt->error);
        // }

        $_SESSION['empresa_id'] = $empresa_id;
        setcookie('empresa_id', $empresa_id, time() + (86400 * 7), "/"); // Cookie por 7 dias

        // Após o cadastro
        header("Location: ../../pages/login_empresa/tela_login.html");
        exit;
    } else {
        // Atualizar empresa existente
        $stmt = $conn->prepare("UPDATE cadastro_empresa 
            SET nome_empresa=?, ramo_empresa=?, email_profissional=?, dia_criacao=?
            WHERE id=?");

        if (!$stmt) {
            die("Erro no prepare (update): " . $conn->error);
        }

        $stmt->bind_param("ssssi", $nome, $ramo, $email, $data, $id);
        $stmt->execute();

        if ($stmt->error) {
            die("Erro no update: " . $stmt->error);
        }

        $_SESSION['empresa_id'] = $id;
        setcookie('empresa_id', $id, time() + (86400 * 7), "/");

        echo "Cadastro atualizado!";
    }
}
?>
