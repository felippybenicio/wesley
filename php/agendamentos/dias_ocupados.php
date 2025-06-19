<?php
include '../conexao.php';
include '../login_empresa/get_id.php';

$servico_id_filtro = isset($_GET['servico_id']) ? intval($_GET['servico_id']) : null;

// Buscar serviços com duração e intervalo
$servicoQuery = $conn->prepare("SELECT id, quantidade_de_funcionarios, duracao_servico, intervalo_entre_servico FROM servico WHERE empresa_id = ?");
$servicoQuery->bind_param("i", $empresa_id);
$servicoQuery->execute();
$servicoResult = $servicoQuery->get_result();

$limites = [];
$duracoes = [];
$intervalos = [];

function timeToMinutes($time) {
    sscanf($time, "%d:%d:%d", $h, $m, $s);
    return $h * 60 + $m + round($s / 60);
}

while ($row = $servicoResult->fetch_assoc()) {
    $id = $row['id'];
    $limites[$id] = (int)$row['quantidade_de_funcionarios'];
    $duracoes[$id] = $row['duracao_servico'];
    $intervalos[$id] = $row['intervalo_entre_servico'];
}

// Buscar todos os agendamentos (remover filtro por mês)
$sqlAg = "SELECT servico_id, dia, hora FROM agendamento WHERE empresa_id = ?";
$stmtAg = $conn->prepare($sqlAg);
$stmtAg->bind_param("i", $empresa_id);
$stmtAg->execute();
$resultAg = $stmtAg->get_result();

$agendamentos = [];
while ($row = $resultAg->fetch_assoc()) {
    $dia = $row['dia'];
    $hora = $row['hora'];
    $servico = $row['servico_id'];
    $agendamentos[$dia][$hora][$servico][] = true;
}

function gerarHorarios($inicio, $fim, $intervaloMinutos) {
    $horarios = [];
    $start = strtotime($inicio);
    $end = strtotime($fim);

    while ($start + ($intervaloMinutos * 60) <= $end) {
        $horarios[] = date('H:i', $start);
        $start += $intervaloMinutos * 60;
    }

    return $horarios;
}

function buscarHorario($conn, $empresa_id, $data) {
    $sql = "SELECT inicio_servico, termino_servico FROM horario_config 
            WHERE empresa_id = ? AND semana_ou_data = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $empresa_id, $data);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row;

    $diaSemana = date('w', strtotime($data));
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $empresa_id, $diaSemana);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row;

    return ['inicio_servico' => '08:00', 'termino_servico' => '18:00'];
}

// Dias cheios
$dias_cheios = [];
$horarios_ocupados = [];

foreach ($agendamentos as $dia => $horas) {
    foreach ($limites as $servico_id => $limite) {
        // Buscar horário de trabalho para esse dia
        $horario = buscarHorario($conn, $empresa_id, $dia);
        $inicio = $horario['inicio_servico'];
        $termino = $horario['termino_servico'];

        $duracaoMin = timeToMinutes($duracoes[$servico_id] ?? '01:00');
        $intervaloMin = timeToMinutes($intervalos[$servico_id] ?? '00:00');
        $passo = $duracaoMin + $intervaloMin;

        $horarios_possiveis = gerarHorarios($inicio, $termino, $passo);
        $total = count($horarios_possiveis);
        $cheios = 0;

        foreach ($horarios_possiveis as $hora) {
            $hora_completa = $hora . ":00";
            $ocupados = isset($agendamentos[$dia][$hora_completa][$servico_id]) ? count($agendamentos[$dia][$hora_completa][$servico_id]) : 0;

            if ($ocupados >= $limite) {
                $cheios++;
                $horarios_ocupados[$servico_id][] = [
                    'dia' => $dia,
                    'hora' => substr($hora, 0, 5)
                ];
            }
        }
        
        if ($cheios >= $total && $total > 0) {
            $dias_cheios[$servico_id][] = $dia;
        }
    }
}


// Remover duplicatas
foreach ($dias_cheios as $servico_id => $dias) {
    $dias_cheios[$servico_id] = array_values(array_unique($dias));
}

header('Content-Type: application/json');
echo json_encode([
    'dias_cheios' => $dias_cheios,
    'horarios_ocupados' => $horarios_ocupados
]);

?>