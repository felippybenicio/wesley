<?php
    include '../login_empresa/get_id.php';
    include '../conexao.php';

    if (!$servico_id || !$dia || !$hora) {
        die("Dados incompletos.");
    }

    // Obtemos a quantidade de funcionários do serviço
    $stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ?");
    if (!$stmt) {
        die("Erro no prepare (funcionários): " . $conn->error);
    }
    $stmt->bind_param("i", $servico_id);
    $stmt->execute();
    $stmt->bind_result($qtdFuncionarios);
    $stmt->fetch();
    $stmt->close();

    // Verificamos quantas pessoas já estão agendadas nesse dia/hora/serviço
    $stmt = $conn->prepare("SELECT COUNT(*) FROM clientes WHERE dia = ? AND hora = ? AND servico_id = ?");
    if (!$stmt) {
        die("Erro no prepare (agendamentos): " . $conn->error);
    }
    $stmt->bind_param("ssi", $dia, $hora, $servico_id);
    $stmt->execute();
    $stmt->bind_result($qtdAgendadas);
    $stmt->fetch();
    $stmt->close();

    // Verificamos se o limite foi atingido
    if ($qtdAgendadas >= $qtdFuncionarios) {
        echo "Horário indisponível. Já atingiu o limite de agendamentos para esse serviço.";
        $conn->close();
        exit;
    }

    // Se passou, pode seguir com o agendamento...
    echo "Horário disponível.";

    $conn->close();
?>
