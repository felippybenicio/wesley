<?php
    if (isset($_POST['diaSemana'])) {
        $diaSemana = $_POST['diaSemana']; // Ex: 0, 1, 2...

        // Conecte ao banco
       include 'php/conexao.php';

        // Busque os horários para o dia da semana recebido
        $stmt = $conn->prepare("SELECT inicio_servico, termino_servico FROM horario_config WHERE semana_ou_data = ?");
        $stmt->execute([$diaSemana]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retorna em JSON
        echo json_encode($result);
    }
?>