<?php
    include '../login_empresa/get_id.php';
    include '../conexao.php';

    header('Content-Type: application/json');

    // Verifica se empresa_id está definido e dataSelecionada foi enviada
    if (!$empresa_id || empty($_POST['dataSelecionada'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Parâmetros insuficientes']);
        exit;
    }

    $dataSelecionada = $_POST['dataSelecionada'];

    // Valida formato da data (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataSelecionada)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Formato de data inválido']);
        exit;
    }

    $timestamp = strtotime($dataSelecionada);
    if ($timestamp === false) {
        http_response_code(400);
        echo json_encode(['erro' => 'Data inválida']);
        exit;
    }

    $diaSemana = date('w', $timestamp); // 0 = domingo ... 6 = sábado

    // Prepara consulta para evitar SQL Injection
    $sql = "SELECT semana_ou_data, inicio_servico, termino_servico FROM horario_config WHERE empresa_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro na preparação da consulta']);
        exit;
    }
    $stmt->bind_param('i', $empresa_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $inicio = null;
    $termino = null;

    // Primeiro, verifica se existe horário para a data exata
    while ($row = $result->fetch_assoc()) {
        if ($row['semana_ou_data'] === $dataSelecionada) {
            $inicio = $row['inicio_servico'];
            $termino = $row['termino_servico'];
            break;
        }
    }

    // Se não encontrou para data específica, procura pelo dia da semana
    if ($inicio === null && $termino === null) {
        $result->data_seek(0); // volta ao início do resultado
        while ($row = $result->fetch_assoc()) {
            if ($row['semana_ou_data'] === (string)$diaSemana) {
                $inicio = $row['inicio_servico'];
                $termino = $row['termino_servico'];
                break;
            }
        }
    }

    // Se ainda não encontrou, pode definir um horário padrão (opcional)
    if ($inicio === null || $termino === null) {
        $inicio = '08:00';
        $termino = '18:00';
    }

    echo json_encode([
        'inicio' => $inicio,
        'termino' => $termino
    ]);
    exit;
?>