<?php
session_start();

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

// ✅ Protege contra acesso direto sem login
if (!isset($_SESSION['empresa_id'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado.']);
    exit;
}

$data = $_POST['data'] ?? '';
if (!$data) {
    http_response_code(400);
    echo json_encode(['erro' => 'Data não fornecida.']);
    exit;
}

include '../conexao.php'; // ❗Assumindo que já tem as credenciais seguras ali

$stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ? AND empresa_id = ?");
$stmt->bind_param("si", $data, $_SESSION['empresa_id']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['erro' => 'Dia não encontrado ou não pertence à empresa.']);
}

$stmt->close();
$conn->close();
