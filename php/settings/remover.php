<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'] ?? '';

    $conn = new mysqli("localhost', 'root', 'Duk23092020$$', 'consultorio");
    if ($conn->connect_error) {
        http_response_code(500);
        echo "Erro ao conectar ao banco.";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ?");
    $stmt->bind_param("s", $data);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Dia removido com sucesso.";
    } else {
        http_response_code(400);
        echo "Dia não encontrado.";
    }

    $stmt->close();
    $conn->close();
}
