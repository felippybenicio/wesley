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
    $endereco = limparEntrada($_POST['endereco'] ?? '');
    $cidade = limparEntrada($_POST['cidade'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmacao'] ?? '';
    $dataCadastro = date('Y-m-d H:i:s');

    // //⚠️ Verificação de campos obrigatórios
    // if (empty($empresa) || empty($ramo) || empty($email) || empty($senha) || empty($edereco) || empty($cidade)) {
    //     echo "Preencha todos os campos obrigatórios.";
    //     exit;
    // }

    // ⚠️ Verificação de e-mail válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "E-mail inválido.";
        exit;
    }

    // ⚠️ Verificação mínima da senha (ex: 8 caracteres)
    if (strlen($senha) < 4) {
        echo "A senha deve ter no mínimo 8 caracteres.";
        exit; 
    } 

    if (empty($senha) || $senha !== $confirmacao) {
        echo "confirmação negada.";
        exit;
    }

    // 🔐 Criptografia da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // SQL preparado para evitar SQL Injection
    $sql = "INSERT INTO cadastro_empresa 
            (nome_empresa, ramo_empresa, email_profissional, senha_inicial, endereco, cidade, dia_cadastrado)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Erro na preparação da query: " . $conn->error;
        exit;
    }

    $stmt->bind_param("sssssss", $empresa, $ramo, $email, $senhaHash, $endereco, $cidade, $dataCadastro);

    // Execução segura
    if ($stmt->execute()) {
        $idEmpresaRecemCriado = $stmt->insert_id;
        $_SESSION['empresa_id'] = $idEmpresaRecemCriado;
        header("Location: /sistema-agendamento/php/agendamentos/tela_inicial_empresa.php");
        exit;
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
        echo "empresa ja cadastrada";
        exit;
    }

    $stmt->close();
    $conn->close();
?>
