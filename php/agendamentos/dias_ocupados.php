<?php
    include '../conexao.php';
include '../login_empresa/get_id.php';

$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');

// 1. Buscar horários da tabela servico (duração e intervalo)
$servicoQuery = $conn->prepare("SELECT id, quantidade_de_funcionarios, duracao_servico, intervalo_entre_servico FROM servico WHERE empresa_id = ?");
$servicoQuery->bind_param("i", $empresa_id);
$servicoQuery->execute();
$servicoResult = $servicoQuery->get_result();

$limites = [];
$menorIntervalo = null;
function timeToMinutes($time) {
    sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
    return $hours * 60 + $minutes + round($seconds / 60);
}

while ($row = $servicoResult->fetch_assoc()) {
    $limites[$row['id']] = (int)$row['quantidade_de_funcionarios'];

    $duracao = timeToMinutes($row['duracao_servico']);
    $intervaloServico = timeToMinutes($row['intervalo_entre_servico']);
    $total = $duracao + $intervaloServico;

    error_log("Serviço {$row['id']}: duração = $duracao minutos, intervalo = $intervaloServico minutos, total = $total minutos");

    if ($menorIntervalo === null || $total < $menorIntervalo) {
        $menorIntervalo = $total;
    }
}


error_log("Menor intervalo calculado: $menorIntervalo");


$intervalo = $menorIntervalo ?? 60;



// 2. Buscar horário da semana
$sqlHorario = "SELECT inicio_servico, termino_servico FROM horario_config 
               WHERE empresa_id = ? AND semana_ou_data = '1' LIMIT 1"; // exemplo: segunda-feira
$stmtHorario = $conn->prepare($sqlHorario);
$stmtHorario->bind_param("i", $empresa_id);
$stmtHorario->execute();
$resultHorario = $stmtHorario->get_result()->fetch_assoc();


function gerarHorarios($inicio, $fim, $intervaloMinutos) {
    $horarios = [];
    $start = strtotime($inicio);
    $end = strtotime($fim);

    while ($start + ($intervaloMinutos * 60) <= $end) {
        $horarios[] = date('H:i', $start);
        $start += $intervaloMinutos * 60;  // aqui o incremento deve ser em segundos
    }

    return $horarios;
}


function buscarHorario($conn, $empresa_id, $data) {
    // 1. Tenta buscar por data específica
    $sql = "SELECT inicio_servico, termino_servico FROM horario_config 
            WHERE empresa_id = ? AND semana_ou_data = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $empresa_id, $data);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($row = $resultado->fetch_assoc()) {
        return $row;
    }

    // 2. Se não encontrou, busca pelo dia da semana
    $diaSemana = date('w', strtotime($data)); // 0 (domingo) até 6 (sábado)
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $empresa_id, $diaSemana);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($row = $resultado->fetch_assoc()) {
        return $row;
    }

    // 3. Se ainda não encontrou, retorna horário padrão
    return ['inicio_servico' => '08:00', 'termino_servico' => '18:00'];
}


// 4. Verificar dias e horários cheios
$dias_cheios = [];
$horarios_ocupados = [];
// Buscar todos os agendamentos do mês
$sql = "SELECT dia, hora, servico_id FROM agendamento 
        WHERE empresa_id = ? AND MONTH(dia) = ? AND YEAR(dia) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $empresa_id, $mes, $ano);
$stmt->execute();
$result = $stmt->get_result();

$agendamentos = [];
while ($row = $result->fetch_assoc()) {
    $dia = $row['dia'];
    $hora = substr($row['hora'], 0, 5); // formata para H:i
    $servico_id = $row['servico_id'];

    $agendamentos[$dia][$hora][$servico_id][] = true;
}

foreach ($agendamentos as $dia => $horas) {
    
    $horariosDoDia = buscarHorario($conn, $empresa_id, $dia);
    $inicio = $horariosDoDia['inicio_servico'];
    $fim = $horariosDoDia['termino_servico'];

    if ($intervalo <= 0) {
        continue;
    }

    $horariosPossiveis = gerarHorarios($inicio, $fim, $intervalo);
    if (!is_array($horariosPossiveis) || count($horariosPossiveis) === 0) {
        continue;
    }

    if (count($horariosPossiveis) > 200) {
        $horariosPossiveis = array_slice($horariosPossiveis, 0, 200);
    }

    $horariosNoDia = []; // <- MOVER PRA CÁ!!

    foreach ($horas as $hora => $servicos) {
        $horaLotada = false;

        foreach ($servicos as $servico_id => $agds) {
            $qtd = count($agds);
            $limite = $limites[$servico_id] ?? PHP_INT_MAX;

            if ($qtd >= $limite) {
                $horaLotada = true;
                break;
            }
        }

        if ($horaLotada) {
            $horariosNoDia[$hora] = true;
            $horarios_ocupados[] = ['dia' => $dia, 'hora' => $hora];
        }
    }

    $todasOcupadas = true;
    foreach ($horariosPossiveis as $horaPossivel) {
        if (!isset($horariosNoDia[$horaPossivel])) {
            $todasOcupadas = false;
            break;
        }
    }

    if ($todasOcupadas) {
        $dias_cheios[] = $dia;
    }
}


// 5. Retornar JSON
echo json_encode([
    'dias_cheios' => $dias_cheios,
    'horarios_ocupados' => $horarios_ocupados
]);
if (strpos($dia, '2025-06-03') !== false) {
    echo "<pre>";
    echo "Horários possíveis em $dia: " . implode(', ', $horariosPossiveis) . "\n";
    echo "Horários ocupados em $dia: " . implode(', ', array_keys($horariosNoDia)) . "\n";
    echo "</pre>";
}


?>