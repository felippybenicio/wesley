<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Confirmação de Agendamento</title>
</head>
<body>
<?php
require __DIR__ . '/vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;

SDK::setAccessToken("TEST-4822365570526425-050519-215ba645d826f7e7eaaf08fdcb14d090-2426282036");


$nome       = $_POST["nome"] ?? '';
$sobrenome  = $_POST["sobrenome"] ?? '';
$nascimento = $_POST["nascimento"] ?? '';
$email      = $_POST["email"] ?? '';
$cpf        = $_POST["cpf"] ?? '';
$cll        = $_POST["cll"] ?? '';
$servico    = $_POST["servico"] ?? '';
$duracao    = (int)($_POST["duracao"] ?? 1);
$dia        = $_POST["dia"] ?? '';
$hora       = $_POST["hora"] ?? '';

switch ($servico) {
    case 'opção 1': $valor = 250.00; break;
    case 'opção 2': $valor = 500.00; break;
    default: $valor = 1000.00; break;
}
$valor_total = $valor * $duracao;

$conn = new mysqli('localhost', 'root', 'Duk23092020$$', 'consultorio');
if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}


$verifica = $conn->prepare("SELECT id FROM dados_pessoais WHERE cpf = ? OR (dia = ? AND hora = ?)");
$verifica->bind_param("sss", $cpf, $dia, $hora);
$verifica->execute();
$verifica->store_result();

if ($verifica->num_rows > 0) {
    echo "<p style='color:red;'>Já existe um agendamento com este CPF ou este horário já está ocupado.</p>";
    $verifica->close();
    $conn->close();
    exit;
}

$verifica->close();



$pagamento = $conn->prepare(
    "INSERT INTO pagamento (tipo_de_servico, duracao, valor_pago, status_pagamento) 
     VALUES (?, ?, ?, 'pendente')"
);
if (!$pagamento) {
    die("Erro ao preparar pagamento: " . $conn->error);
}
$pagamento->bind_param("sss", $servico, $duracao, $valor_total);
$pagamentoOk = $pagamento->execute();
$pagamento_id = $conn->insert_id;

if (!$pagamentoOk) {
    die("Erro ao salvar pagamento: " . $pagamento->error);
}


$dadosPessoais = $conn->prepare(
    "INSERT INTO dados_pessoais 
     (nome, sobrenome, nascimento, email, cpf, celular, dia, hora, pagamento_id) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$dadosPessoais) {
    die("Erro ao preparar dados pessoais: " . $conn->error);
}
$dadosPessoais->bind_param("ssssssssi", $nome, $sobrenome, $nascimento, $email, $cpf, $cll, $dia, $hora, $pagamento_id);
$dadosPessoaisOk = $dadosPessoais->execute();

if ($pagamentoOk && $dadosPessoaisOk) {
    echo "<p>Dados salvos com sucesso!</p>";
} else {
    echo "<p>Erro ao salvar dados: " . $dadosPessoais->error . " e " . $pagamento->error . "</p>";
}


$item = new Item();
$item->title = $servico;
$item->quantity = $duracao;
$item->unit_price = $valor;

$preference = new Preference();
$preference->items = [$item];
$preference->external_reference = $pagamento_id; 

$base_url = "https://e07c-2804-7f0-b7c1-8d01-246f-bc69-9684-4b1a.ngrok-free.app/wesley";

$preference->back_urls = [
    "success" => $base_url . "/sucesso.html",
    "failure" => $base_url . "/falha.html",
    "pending" => $base_url . "/pendente.html"
];
$preference->auto_return = "approved";
$preference->notification_url = $base_url . "/notificacao.php";

try {
    $preference->save();
    if (!$preference->init_point) {
        throw new Exception("Erro ao gerar link de pagamento.");
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}


$dadosPessoais->close();
$pagamento->close();
$conn->close();


echo "<h1>Obrigado, " . htmlspecialchars($nome) . ", pela sua preferência!</h1>";
echo "<p>Serviço: <strong>" . htmlspecialchars($servico) . "</strong></p>";
echo "<p>Data: <strong>" . htmlspecialchars($dia) . "</strong> às <strong>" . htmlspecialchars($hora) . "</strong></p>";
echo "<p>Duração: <strong>" . htmlspecialchars($duracao) . " hora(s)</strong></p>";
echo "<p>Total: R$ " . number_format($valor_total, 2, ',', '.') . "</p>";
echo "<p><a href='" . htmlspecialchars($preference->init_point) . "' target='_blank' rel='noopener noreferrer'>Clique aqui para pagar</a></p>";

?>

</script>
</body>
</html>
