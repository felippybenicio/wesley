<?php
session_start();
include '../conexao.php';
include 'get_admin.php';

header('Content-Type: application/json');

$empresa_id = $_POST['id'] ?? null;
$senha = $_POST['senha'] ?? '';

if (!$empresa_id || !$senha || !isset($_SESSION['admin_id'])) {
    echo json_encode(['erro' => 'Dados incompletos']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Busca a senha do admin logado
$stmt = $conn->prepare("SELECT senha FROM monitoramento WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($senha_correta);
$stmt->fetch();
$stmt->close();

if (!password_verify($senha, $senha_correta)) {
    echo json_encode(['erro' => 'Acesso não autorizado']);
    exit;
}

// Deletar empresa e registros relacionados
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
    $sql = "DELETE FROM $tabela WHERE empresa_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo json_encode(['erro' => "Erro ao preparar exclusão na tabela $tabela"]);
        exit;
    }
}

// Por fim, deleta a própria empresa
$stmt = $conn->prepare("DELETE FROM cadastro_empresa WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['erro' => 'Erro ao excluir empresa']);
}
