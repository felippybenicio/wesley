<?php
$mes = $_GET['mes'];
$ano = $_GET['ano'];

$conn = new mysqli('localhost', 'root', 'Duk23092020$$', 'consultorio');
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$sql = "SELECT DAY(dia) AS dia, COUNT(*) AS total 
        FROM dados_pessoais 
        WHERE MONTH(dia) = ? AND YEAR(dia) = ? 
        GROUP BY dia 
        HAVING total >= 8";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro no prepare: " . $conn->error);
}

$stmt->bind_param("ii", $mes, $ano);
$stmt->execute();

$result = $stmt->get_result();

$dias = [];
while ($row = $result->fetch_assoc()) {
    $dias[] = $row['dia'];  // só o número do dia
}

echo json_encode(['dias_cheios' => $dias]);
?>
