<?php
session_start();
include '../conexao.php';
include 'get_admin.php';

header('Content-Type: application/json');

$empresa_id = $_POST['id'] ?? null;
$senha = trim($_POST['senha'] ?? '');

if (!$empresa_id || !$senha || !isset($_SESSION['admin_id'])) {
    echo json_encode(['erro' => 'Dados incompletos.']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Verifica senha do admin
$stmt = $conn->prepare("SELECT senha FROM monitoramento WHERE id = ?");
if (!$stmt) {
    echo json_encode(['erro' => 'Erro interno (1).']);
    exit;
}
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($senha_hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($senha, $senha_hash)) {
    echo json_encode(['erro' => 'Acesso não autorizado.']);
    exit;
}

// Lista de tabelas com foreign key empresa_id (validadas manualmente)
$tabelas_com_empresa_id = [
    'agendamento',
    'pagamento',
    'clientes',
    'funcionario',
    'servico',
    'horario_config',
    'dia_indisponivel',
    'semana_indisponivel',
    'mes_indisponivel',
    'quantidade_servico'
];

foreach ($tabelas_com_empresa_id as $tabela) {
    // Segurança extra: só permitir nomes de tabela da lista
    if (!preg_match('/^[a-z_]+$/', $tabela)) {
        echo json_encode(['erro' => 'Tabela inválida detectada.']);
        exit;
    }

    $sql = "DELETE FROM `$tabela` WHERE empresa_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo json_encode(['erro' => "Erro ao excluir registros em $tabela."]);
        exit;
    }
}

// Deleta a empresa
$stmt = $conn->prepare("DELETE FROM cadastro_empresa WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['erro' => 'Erro ao excluir empresa.']);
}
