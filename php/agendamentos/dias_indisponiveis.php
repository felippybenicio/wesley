<?php
include '../login_empresa/get_id.php';
include '../conexao.php';

if (!$empresa_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT data FROM dia_indisponivel WHERE empresa_id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();

$result = $stmt->get_result();
$datas = [];

while ($row = $result->fetch_assoc()) {
    $datas[] = $row['data']; // formato yyyy-mm-dd
}

echo json_encode($datas);
