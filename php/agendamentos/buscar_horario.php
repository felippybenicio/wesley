<?php
include '../login_empresa/get_id.php';
include '../conexao.php';


if (!$empresa_id || !isset($_POST['dataSelecionada'])) {
    echo json_encode(['erro' => 'Faltam dados']);
    exit;
}

$dataSelecionada = $_POST['dataSelecionada'];
$timestamp = strtotime($dataSelecionada);
$diaSemana = date('w', $timestamp); // 0 = domingo, ..., 6 = sábado

$sql = "SELECT * FROM horario_config WHERE empresa_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $empresa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$inicio = null;
$termino = null;

while ($horario = mysqli_fetch_assoc($result)) {
    // Prioridade 1: data específica
    if ($horario['semana_ou_data'] === $dataSelecionada) {
        $inicio = $horario['inicio_servico'];
        $termino = $horario['termino_servico'];
        break;
    }
}

// Reinicia o result para verificar os dias da semana, se ainda não encontrou
if (is_null($inicio) && is_null($termino)) {
    mysqli_data_seek($result, 0); // volta ao início do resultado
    while ($horario = mysqli_fetch_assoc($result)) {
        if ($horario['semana_ou_data'] === (string)$diaSemana) {
            $inicio = $horario['inicio_servico'];
            $termino = $horario['termino_servico'];
            break;
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'inicio' => $inicio,
    'termino' => $termino
]);
