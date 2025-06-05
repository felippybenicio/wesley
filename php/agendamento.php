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

include 'conexao.php';
$configs = $conn->query("SELECT * FROM servico")->fetch_all(MYSQLI_ASSOC);
$diaHoraDisponivel = $conn->query("SELECT dia hora FROM dados_pessoais")->fetch_all(MYSQLI_ASSOC);


$nome       = $_POST["nome"] ?? '';
$sobrenome  = $_POST["sobrenome"] ?? '';
$nascimento = $_POST["nascimento"] ?? '';
$email      = $_POST["email"] ?? '';
$cpf        = $_POST["cpf"] ?? '';
$cll        = $_POST["cll"] ?? '';
$servico    = $_POST["servico"] ?? '';
preg_match('/\d+/', $_POST["duracao"], $match);
$duracao = (int)($match[0] ?? 1);
$dia        = $_POST["dia"] ?? '';
$hora       = $_POST["hora"] ?? '';
$servico_id = isset($_POST['servico']) ? (int)$_POST['servico'] : null;



$configs = $conn->query("SELECT * FROM servico")->fetch_all(MYSQLI_ASSOC);
foreach ($configs as $config) {
    $qtdFuncionarios = $config['quantidade_de_funcionarios'];
}



$valorAtual = 0;
foreach ($configs as $config) {
    $servicoConfig = $config['tipo_servico'];
    $valor = $config['valor'];

    if ($servico === $servicoConfig) {
        $valorAtual = (float)$valor;
        break;
    }
}

// Calcula o valor total
$valor_total = $valorAtual * $duracao;

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}


$verificaCpf = $conn->prepare("SELECT id FROM dados_pessoais WHERE cpf = ?");
$verificaCpf->bind_param("s", $cpf);
$verificaCpf->execute();
$verificaCpf->store_result();

if ($verificaCpf->num_rows > 0) {
    echo "<p style='color:red;'>Já existe um agendamento com este CPF.</p>";
    $verificaCpf->close();
    $conn->close();
    exit;
}
$verificaCpf->close();








// Sanitização básica (mais robusta seria recomendada dependendo do contexto)
$servico_id = (int)$servico_id; // Garante que é um inteiro
$dia = htmlspecialchars($dia, ENT_QUOTES, 'UTF-8'); // Protege contra XSS
$hora = htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); // Protege contra XSS


// --- 2. Obter a quantidade de funcionários para o serviço ---
$stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ?");
if (!$stmt) {
    // Em produção, registre o erro em um log, não exiba diretamente ao usuário
    error_log("Erro no prepare (funcionários): " . $conn->error);
    echo "Erro interno ao buscar informações do serviço.";
    $conn->close();
    exit;
}
$stmt->bind_param("i", $servico_id); // Use $servico_id aqui, para consistência
$stmt->execute();
$stmt->bind_result($qtdFuncionarios);
$stmt->fetch();
$stmt->close();

// Se o serviço não foi encontrado ou não tem funcionários definidos
if (empty($qtdFuncionarios)) {
    echo "Serviço inválido ou sem disponibilidade definida.";
    $conn->close();
    exit;
}

// --- 3. Contar agendamentos existentes para o dia/hora/serviço ---
$stmt = $conn->prepare("SELECT COUNT(*) FROM dados_pessoais WHERE dia = ? AND hora = ? AND servico_id = ?");
if (!$stmt) {
    // Em produção, registre o erro em um log
    error_log("Erro no prepare (agendamentos): " . $conn->error);
    echo "Erro interno ao verificar agendamentos.";
    $conn->close();
    exit;
}
$stmt->bind_param("ssi", $dia, $hora, $servico_id);
$stmt->execute();
$stmt->bind_result($qtdAgendadas);
$stmt->fetch();
$stmt->close();

// --- 4. Verificar se o limite foi atingido ---
if ($qtdAgendadas >= $qtdFuncionarios) {
    echo "Horário indisponível. Já atingiu o limite de agendamentos para esse serviço.";
    $conn->close();
    exit;
} else {
    // Se chegou aqui, o horário está disponível!
    // Você pode continuar com o processo de agendamento,
    // como inserir os dados na tabela dados_pessoais.
    echo "Horário disponível! Prossiga com o agendamento.";

    // Exemplo: Inserir o agendamento (adapte conforme sua lógica de negócios)
    // $stmt_insert = $conn->prepare("INSERT INTO dados_pessoais (dia, hora, servico_id, ...) VALUES (?, ?, ?, ...)");
    // $stmt_insert->bind_param("ssi", $dia, $hora, $servico_id);
    // $stmt_insert->execute();
    // $stmt_insert->close();
}









//FLATA MARCAR AS HORAS DISONIVEIS DE ACORDO COM A SEMANA


// // Obtemos a quantidade de funcionários do serviço
// $stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ?");
// if (!$stmt) {
//     die("Erro no prepare (funcionários): " . $conn->error);
// }
// $stmt->bind_param("i", $servico);
// $stmt->execute();
// $stmt->bind_result($qtdFuncionarios);
// $stmt->fetch();
// $stmt->close();

// // Verificamos quantas pessoas já estão agendadas nesse dia/hora/serviço
// $stmt = $conn->prepare("SELECT COUNT(*) FROM dados_pessoais WHERE dia = ? AND hora = ? AND servico_id = ?");
// $servico_id = $_POST['servico_id'] ?? null;
// if (!$stmt) {
//     die("Erro no prepare (agendamentos): " . $conn->error);
// }
// $stmt->bind_param("ssi", $dia, $hora, $servico_id);
// $stmt->execute();
// $stmt->bind_result($qtdAgendadas);
// $stmt->fetch();
// $stmt->close();

// // Verificamos se o limite foi atingido
// if ($qtdAgendadas >= $qtdFuncionarios) {
//     echo "Horário indisponível. Já atingiu o limite de agendamentos para esse serviço.";
//     $conn->close();
//     exit;
// }




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
     (nome, sobrenome, nascimento, email, cpf, celular, dia, hora, pagamento_id, servico_id) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$dadosPessoais) {
    die("Erro ao preparar dados pessoais: " . $conn->error);
}
$dadosPessoais->bind_param("ssssssssii", $nome, $sobrenome, $nascimento, $email, $cpf, $cll, $dia, $hora, $pagamento_id, $servico_id);
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

$base_url = "https://3d5d-2804-7f0-b7c2-2833-28ec-b207-9d9c-aa1a.ngrok-free.app/wesley";


$preference->back_urls = [
    "success" => $base_url . "/sucesso.html",
    "failure" => $base_url . "/falha.html",
    "pending" => $base_url . "/pendente.html"
];
$preference->auto_return = "approved";
$preference->notification_url = $base_url . "/php/confirmacao_vendas/notificacao.php";

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

$i = 1;
foreach ($configs as $config) {
    // Pega os valores da linha atual
    $id = $config['id'];
    $idSecundario = $config['id_secundario'];
    $servico = $config['tipo_servico'];
    $valor = $config['valor'];
    $qtFuncionarios = $config['quantidade_de_funcionarios'];
    $duracao = $config['duracao_servico'];
    $intervalo = $config['intervalo_entre_servico'];

    $tempoentrecessao[$i] = $duracao[$i] + $intervalo[$i];
}



echo "<h1>Obrigado, " . htmlspecialchars($nome) . ", pela sua preferência!</h1>";
echo "<p>Serviço: <strong>" . htmlspecialchars($servico) . "</strong></p>";
echo "<p>Data: <strong>" . htmlspecialchars($dia) . "</strong> às <strong>" . htmlspecialchars($hora) . "</strong></p>";
echo "<p>Duração: <strong>" . htmlspecialchars($duracao) . " cessão(ões)</strong></p>";
echo "<p>Total: R$ " . number_format($valor_total, 2, ',', '.') . "</p>";
echo "<p><a href='" . htmlspecialchars($preference->init_point) . "' target='_blank' rel='noopener noreferrer'>Clique aqui para pagar</a></p>";

?>
</body>
</html>
