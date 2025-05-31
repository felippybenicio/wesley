<?php
$servername = "localhost";
$username = "root";         // Altere se necessário
$password = "Duk23092020$$";             // Altere se tiver senha
$database = "consultorio";    // Altere para o nome real do seu banco

$conn = new mysqli($servername, $username, $password, $database);

// Verifica conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>
