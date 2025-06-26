<?php
include '../login_empresa/get_id.php';
include '../conexao.php';

header('Content-Type: application/json');

// Validação básica e sanitização das entradas
$servico_id = isset($_POST['servico_id']) ? intval($_POST['servico_id']) : null;
$dia = isset($_POST['dia']) ? trim($_POST['dia']) : null;
$hora = isset($_POST['hora']) ? trim($_POST['hora']) : null;

if (!$servico_id || !$dia || !$hora) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos.']);
    exit;
}

// Validação simples do formato da data e hora (pode ser aprimorada)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dia) || !preg_match('/^\d{2}:\d{2}$/', $hora)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Formato inválido para dia ou hora.']);
    exit;
}

// Buscar quantidade de funcionários para o serviço
$stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ? AND empresa_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno no servidor (prepare 1).']);
    exit;
}
$stmt->bind_param("ii", $servico_id, $empresa_id);
$stmt->execute();
$stmt->bind_result($qtdFuncionarios);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['erro' => 'Serviço não encontrado.']);
    $stmt->close();
    exit;
}
$stmt->close();

// Contar agendamentos para este serviço, dia e hora
$stmt = $conn->prepare("SELECT COUNT(*) FROM agendamento WHERE servico_id = ? AND dia = ? AND hora = ? AND empresa_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno no servidor (prepare 2).']);
    exit;
}
$stmt->bind_param("issi", $servico_id, $dia, $hora, $empresa_id);
$stmt->execute();
$stmt->bind_result($qtdAgendadas);
$stmt->fetch();
$stmt->close();

if ($qtdAgendadas >= $qtdFuncionarios) {
    echo json_encode(['disponivel' => false, 'mensagem' => 'Horário indisponível. Já atingiu o limite de agendamentos para esse serviço.']);
    $conn->close();
    exit;
}

// Horário disponível
echo json_encode(['disponivel' => true, 'mensagem' => 'Horário disponível.']);
$conn->close();
