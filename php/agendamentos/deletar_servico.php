<?php
include '../conexao.php';
include 'get_admin.php';

$id_servico = $_POST['id_servico'] ?? '';
$empresa_id = $empresa_id ?? null;

echo "ID SERVICO: $id_servico\n";
echo "EMPRESA_ID: $empresa_id\n";

// Verificação básica
if (empty($id_servico) || empty($empresa_id)) {
    exit("Dados incompletos");
}

// 1. Verifica se esse serviço está sendo usado por OUTRA empresa
$verificaOutroUso = $conn->prepare("
    SELECT COUNT(*) 
    FROM agendamento 
    WHERE servico_id = ? AND empresa_id != ?
");
if (!$verificaOutroUso) {
    exit("Erro na verificação de outra empresa: " . $conn->error);
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
    exit("Erro na verificação de agendamentos: " . $conn->error);
}
$verificaAgendado->bind_param("ii", $id_servico, $empresa_id);
$verificaAgendado->execute();
$verificaAgendado->bind_result($totalAgendados);
$verificaAgendado->fetch();
$verificaAgendado->close();

if ($totalAgendados > 0) {
    exit("Este serviço possui agendamentos já atendidos e não pode ser excluído.");
}

// 3. Deleta agendamentos PENDENTES dessa empresa
$stmtDeleteAgendamento = $conn->prepare("
    DELETE FROM agendamento 
    WHERE servico_id = ? AND empresa_id = ? AND ja_atendido IS NULL
");
if (!$stmtDeleteAgendamento) {
    exit("Erro ao excluir agendamentos: " . $conn->error);
}
$stmtDeleteAgendamento->bind_param("ii", $id_servico, $empresa_id);
$stmtDeleteAgendamento->execute();
$stmtDeleteAgendamento->close();

// 4. Deleta funcionários vinculados a esse serviço
$stmtDeleteFuncionarios = $conn->prepare("
    DELETE FROM funcionario 
    WHERE servico_id = ? AND empresa_id = ?
");
if (!$stmtDeleteFuncionarios) {
    exit("Erro ao excluir funcionários: " . $conn->error);
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
    exit("Erro ao excluir serviço: " . $conn->error);
}
$stmtDeleteServico->bind_param("ii", $id_servico, $empresa_id);
if ($stmtDeleteServico->execute()) {
    echo "success";
} else {
    echo "Erro ao deletar serviço: " . $stmtDeleteServico->error;
}
$stmtDeleteServico->close();
