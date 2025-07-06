<?php
    include '../login_empresa/get_id.php';
    include '../conexao.php';

    // Valida e sanitiza entrada
    $id_servico = filter_input(INPUT_POST, 'id_servico', FILTER_VALIDATE_INT);
    $empresa_id = $empresa_id ?? null;

    if (!$id_servico || !$empresa_id) {
        exit("Dados inválidos ou incompletos.");
    }

    // 1. Verifica se esse serviço está sendo usado por OUTRA empresa
    $verificaOutroUso = $conn->prepare("
        SELECT COUNT(*) 
        FROM agendamento 
        WHERE servico_id = ? AND empresa_id != ?
    ");
    if (!$verificaOutroUso) {
        error_log("Erro na verificação de outra empresa: " . $conn->error);
        exit("Erro interno");
    }
    $verificaOutroUso->bind_param("ii", $id_servico, $empresa_id);
    $verificaOutroUso->execute();
    $verificaOutroUso->bind_result($emUsoOutraEmpresa);
    $verificaOutroUso->fetch();
    $verificaOutroUso->close();

    if ($emUsoOutraEmpresa > 0) {
        exit("Este serviço está sendo usado por outra empresa. Não pode ser apagado.");
    }

    // 2. Verifica se há agendamentos já atendidos para esta empresa
    $verificaAgendado = $conn->prepare("
        SELECT COUNT(*) 
        FROM agendamento 
        WHERE servico_id = ? AND empresa_id = ? AND ja_atendido IS NOT NULL
    ");
    if (!$verificaAgendado) {
        error_log("Erro na verificação de agendamentos: " . $conn->error);
        exit("Erro interno");
    }
    $verificaAgendado->bind_param("ii", $id_servico, $empresa_id);
    $verificaAgendado->execute();
    $verificaAgendado->bind_result($totalAgendados);
    $verificaAgendado->fetch();
    $verificaAgendado->close();

    if ($totalAgendados > 0) {
        exit("Este serviço possui agendamentos já atendidos e não pode ser excluído.");
    }

    // 3. Deleta agendamentos pendentes
    $stmtDeleteAgendamento = $conn->prepare("
        DELETE FROM agendamento 
        WHERE servico_id = ? AND empresa_id = ? AND ja_atendido IS NULL
    ");
    if (!$stmtDeleteAgendamento) {
        error_log("Erro ao excluir agendamentos: " . $conn->error);
        exit("Erro interno");
    }
    $stmtDeleteAgendamento->bind_param("ii", $id_servico, $empresa_id);
    $stmtDeleteAgendamento->execute();
    $stmtDeleteAgendamento->close();

    // 4. Deleta funcionários vinculados
    $stmtDeleteFuncionarios = $conn->prepare("
        DELETE FROM funcionario 
        WHERE servico_id = ? AND empresa_id = ?
    ");
    if (!$stmtDeleteFuncionarios) {
        error_log("Erro ao excluir funcionários: " . $conn->error);
        exit("Erro interno");
    }
    $stmtDeleteFuncionarios->bind_param("ii", $id_servico, $empresa_id);
    $stmtDeleteFuncionarios->execute();
    $stmtDeleteFuncionarios->close();

    // 5. Deleta o serviço
    $stmtDeleteServico = $conn->prepare("
        DELETE FROM servico 
        WHERE id = ? AND empresa_id = ?
    ");
    if (!$stmtDeleteServico) {
        error_log("Erro ao excluir serviço: " . $conn->error);
        exit("Erro interno");
    }
    $stmtDeleteServico->bind_param("ii", $id_servico, $empresa_id);
    if ($stmtDeleteServico->execute()) {
        echo "success";
    } else {
        error_log("Erro ao deletar serviço: " . $stmtDeleteServico->error);
        echo "Erro ao deletar serviço.";
    }
    $stmtDeleteServico->close();
