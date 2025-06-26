<?php
include '../conexao.php';
include '../login_empresa/get_id.php';

header('Content-Type: application/json');

// Pega parâmetros com filtro seguro
$servicoSelecionadoId = isset($_GET['servico_id']) ? intval($_GET['servico_id']) : null;
$dataConsulta = $_GET['data'] ?? date('Y-m-d');

// Função para converter "HH:MM:SS" em minutos
function timeToMinutes(string $time): int {
    sscanf($time, "%d:%d:%d", $h, $m, $s);
    return $h * 60 + $m + round($s / 60);
}

// Gera horários entre início e fim com intervalo em minutos
function gerarHorarios(string $inicio, string $fim, int $intervaloMinutos): array {
    $horarios = [];
    $start = strtotime($inicio);
    $end = strtotime($fim);

    while ($start + ($intervaloMinutos * 60) <= $end) {
        $horarios[] = date('H:i', $start);
        $start += $intervaloMinutos * 60;
    }
    return $horarios;
}

// Busca o horário de funcionamento para data ou dia da semana
function buscarHorario(mysqli $conn, int $empresa_id, string $data): array {
    $stmt = $conn->prepare("SELECT inicio_servico, termino_servico FROM horario_config WHERE empresa_id = ? AND semana_ou_data = ?");
    $stmt->bind_param("is", $empresa_id, $data);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row;

    $diaSemana = date('w', strtotime($data));
    $stmt->bind_param("is", $empresa_id, $diaSemana);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row;

    // Default se nada encontrado
    return ['inicio_servico' => '08:00', 'termino_servico' => '18:00'];
}

// Busca serviços da empresa
$servicoQuery = $conn->prepare("SELECT id, quantidade_de_funcionarios, duracao_servico, intervalo_entre_servico FROM servico WHERE empresa_id = ?");
$servicoQuery->bind_param("i", $empresa_id);
$servicoQuery->execute();
$servicoResult = $servicoQuery->get_result();

$limites = [];
$duracoes = [];
$intervalos = [];

while ($row = $servicoResult->fetch_assoc()) {
    $id = (int)$row['id'];
    $limites[$id] = (int)$row['quantidade_de_funcionarios'];
    $duracoes[$id] = $row['duracao_servico'];
    $intervalos[$id] = $row['intervalo_entre_servico'];
}

// Busca agendamentos ativos da empresa
$stmtAg = $conn->prepare("SELECT servico_id, dia, hora FROM agendamento WHERE empresa_id = ? AND (ja_atendido IS NULL OR ja_atendido = '')");
$stmtAg->bind_param("i", $empresa_id);
$stmtAg->execute();
$resultAg = $stmtAg->get_result();

$agendamentos = [];
while ($row = $resultAg->fetch_assoc()) {
    $dia = $row['dia'];
    $hora = $row['hora'];
    $servico = (int)$row['servico_id'];
    $agendamentos[$dia][$hora][$servico][] = true;
}

// Calcular horários ocupados e dias cheios
$dias_cheios = [];
$horarios_ocupados = [];

foreach ($agendamentos as $dia => $horas) {
    foreach ($limites as $servico_id => $limite) {
        $horario = buscarHorario($conn, $empresa_id, $dia);
        $inicio = $horario['inicio_servico'];
        $termino = $horario['termino_servico'];

        $duracaoMin = isset($duracoes[$servico_id]) ? timeToMinutes($duracoes[$servico_id]) : 60;
        $intervaloMin = isset($intervalos[$servico_id]) ? timeToMinutes($intervalos[$servico_id]) : 0;
        $passo = $duracaoMin + $intervaloMin;

        $horarios_possiveis = gerarHorarios($inicio, $termino, $passo);
        $total = count($horarios_possiveis);
        $cheios = 0;

        foreach ($horarios_possiveis as $hora) {
            $hora_completa = $hora . ":00";
            $ocupados = isset($agendamentos[$dia][$hora_completa][$servico_id]) ? count($agendamentos[$dia][$hora_completa][$servico_id]) : 0;

            if ($ocupados >= $limite) {
                $cheios++;
                // Guarda hora curta "HH:MM" para dia e serviço
                if (!isset($horarios_ocupados[$servico_id][$dia])) {
                    $horarios_ocupados[$servico_id][$dia] = [];
                }
                $hora_curta = substr($hora, 0, 5);
                if (!in_array($hora_curta, $horarios_ocupados[$servico_id][$dia])) {
                    $horarios_ocupados[$servico_id][$dia][] = $hora_curta;
                }
            }
        }

        if ($cheios >= $total && $total > 0) {
            $dias_cheios[$servico_id][] = $dia;
        }
    }
}

// Remove duplicatas de dias
foreach ($dias_cheios as $servico_id => $dias) {
    $dias_cheios[$servico_id] = array_values(array_unique($dias));
}

// Prepara dados para serviço selecionado
$dadosServicoSelecionado = null;
if ($servicoSelecionadoId !== null && isset($duracoes[$servicoSelecionadoId], $intervalos[$servicoSelecionadoId])) {
    $horario = buscarHorario($conn, $empresa_id, $dataConsulta);

    $duracaoMin = timeToMinutes($duracoes[$servicoSelecionadoId]);
    $intervaloMin = timeToMinutes($intervalos[$servicoSelecionadoId]);

    $dadosServicoSelecionado = [
        'servico_id_base' => $servicoSelecionadoId,
        'inicio_servico' => $horario['inicio_servico'] ?? '08:00',
        'termino_servico' => $horario['termino_servico'] ?? '18:00',
        'duracao_minutos' => $duracaoMin,
        'intervalo_minutos' => $intervaloMin,
        'intervalo_total' => $duracaoMin + $intervaloMin
    ];
}

// Envia JSON com os dados necessários
echo json_encode([
    'dias_cheios' => $dias_cheios,
    'horarios_ocupados' => $horarios_ocupados,
    'dados_servico_selecionado' => $dadosServicoSelecionado
], JSON_UNESCAPED_UNICODE);
