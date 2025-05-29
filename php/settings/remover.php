<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'] ?? '';

    // ✅ Mostre o que chegou (debug temporário)
    file_put_contents("debug.txt", "DATA RECEBIDA: " . $data . PHP_EOL, FILE_APPEND);

    // Corrige a string de conexão
    $conn = new mysqli("localhost", "root", "Duk23092020$$", "consultorio");
    if ($conn->connect_error) {
        http_response_code(500);
        echo "Erro ao conectar ao banco.";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ?");
    $stmt->bind_param("s", $data);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "OK";
    } else {
        echo "Dia não encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>
