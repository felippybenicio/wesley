<?php
header('Content-Type: application/json');

// Conexão com o banco
$conn = new mysqli('localhost', 'root', 'Duk23092020$$', 'consultorio');
if ($conn->connect_error) {
    die(json_encode(['erro' => "Falha na conexão: " . $conn->connect_error]));
}

// Se a data completa for enviada, retorna as horas ocupadas desse dia
if (isset($_GET['data'])) {
    $data = $_GET['data'];

    $stmt = $conn->prepare("SELECT hora FROM dados_pessoais WHERE dia = ?");
    if (!$stmt) {
        die(json_encode(['erro' => "Erro no prepare: " . $conn->error]));
    }

    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result();

    $horas = [];
    while ($row = $result->fetch_assoc()) {
        $horas[] = $row['hora'];
    }

    echo json_encode($horas);
    exit;
}

// Se mês e ano forem enviados, retorna os dias com 8 agendamentos
if (isset($_GET['mes']) && isset($_GET['ano'])) {
    $mes = $_GET['mes'];
    $ano = $_GET['ano'];

    $sql = "SELECT DAY(dia) AS dia, COUNT(*) AS total 
            FROM dados_pessoais 
            WHERE MONTH(dia) = ? AND YEAR(dia) = ? 
            GROUP BY dia 
            HAVING total >= 8";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die(json_encode(['erro' => "Erro no prepare: " . $conn->error]));
    }

    $stmt->bind_param("ii", $mes, $ano);
    $stmt->execute();
    $result = $stmt->get_result();

    $dias = [];
    while ($row = $result->fetch_assoc()) {
        $dias[] = $row['dia'];
    }

    echo json_encode(['dias_cheios' => $dias]);
    exit;
}

// Se nenhum parâmetro válido for enviado
echo json_encode(['erro' => 'Parâmetros inválidos']);
?>
