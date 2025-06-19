<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Confirmação de Agendamento</title>
</head>
<body>
<?php
    //include 'restringir_agendamentos.php';
    include '../login_empresa/get_id.php';
    include '../conexao.php';
    require __DIR__ . '../../vendor/autoload.php';

    use MercadoPago\SDK;
    use MercadoPago\Preference;
    use MercadoPago\Item;

    SDK::setAccessToken("TEST-4822365570526425-050519-215ba645d826f7e7eaaf08fdcb14d090-2426282036");




    $configs = $conn->query("SELECT * FROM servico")->fetch_all(MYSQLI_ASSOC);
    $diaHoraDisponivel = $conn->query("SELECT dia hora FROM agendamento")->fetch_all(MYSQLI_ASSOC);


    $nome       = $_POST["nome"] ?? '';
    $sobrenome  = $_POST["sobrenome"] ?? '';
    $nascimento = $_POST["nascimento"] ?? '';
    $email      = $_POST["email"] ?? '';
    $cpf        = $_POST["cpf"] ?? '';
    $cll        = $_POST["cll"] ?? '';
    $servico    = $_POST["servico"] ?? '';
    preg_match('/\d+/', $_POST["qtdagendamentos"], $match);
    $qtdagendamentos = (int)($match[0] ?? 1);
    $dia        = $_POST["dia"] ?? '';
    $hora       = $_POST["hora"] ?? '';
    $servico_id = isset($_POST['servico']) ? (int)$_POST['servico'] : null;



    // Obtemos a quantidade de funcionários do serviço
    $stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ?");
    if (!$stmt) {
        die("Erro no prepare (funcionários): " . $conn->error);
    }
    $stmt->bind_param("i", $servico);
    $stmt->execute();
    $stmt->bind_result($qtdFuncionarios);
    $stmt->fetch();
    $stmt->close();


$configs = $conn->query("SELECT * FROM servico")->fetch_all(MYSQLI_ASSOC);
foreach ($configs as $config) {
    $qtdFuncionarios = $config['quantidade_de_funcionarios'];
}

$valorAtual = 0;
foreach ($configs as $config) {
    $servicoConfig = $config['tipo_servico'];
    $valor = $config['valor'];

    if ( $servicoConfig) {
        $valorAtual = (float)$valor;
        break;
    }
}


if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}


$verificaCpf = $conn->prepare("SELECT id FROM clientes WHERE empresa_id = ? AND cpf = ?");
$verificaCpf->bind_param("is", $empresa_id, $cpf);
$verificaCpf->execute();
$verificaCpf->store_result();

if ($empresa_id && $verificaCpf->num_rows > 0) {
    echo "<p style='color:red;'>Já existe um agendamento com este CPF.</p>";
    $verificaCpf->close();
    $conn->close();
    exit;
}
$verificaCpf->close();


$valor_total = 0;
$qtdagendamentos = count($_POST['agendamentos']);

foreach ($_POST['agendamentos'] as $ag) {
    $servico_id = (int)$ag['servico_id'];

    $stmt = $conn->prepare("SELECT valor FROM servico WHERE empresa_id = ? AND id = ?");
    $stmt->bind_param("ii", $empresa_id, $servico_id);
    $stmt->execute();
    $stmt->bind_result($valor_unitario);
    $stmt->fetch();
    $stmt->close();

    $valor_total += (float)$valor_unitario;
}


$dias = $_POST['dia'];               
$horas = $_POST['hora'];    
$servicos = $_POST['agendamentos'];

// // Inserir pagamento
// $pagamento = $conn->prepare(
//     "INSERT INTO pagamento (empresa_id, qtdagendamentos, valor_pagar, status_pagamento) 
//      VALUES (?, ?, ?, 'pendente')"
// );
// if (!$pagamento) {
//     die("Erro ao preparar pagamento: " . $conn->error);
// }
// $pagamento->bind_param("iis", $empresa_id, $qtdagendamentos, $valor_total);
// if (!$pagamento->execute()) {
//     die("Erro ao executar pagamento: " . $pagamento->error);
// }
// $pagamento_id = $conn->insert_id;
// $pagamento->close();

// // Inserir cliente
// $dadosPessoais = $conn->prepare(
//     "INSERT INTO clientes (empresa_id, nome, sobrenome, nascimento, email, cpf, celular, pagamento_id) 
//      VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
// );
// if (!$dadosPessoais) {
//     die("Erro ao preparar cliente: " . $conn->error);
// }
// $dadosPessoais->bind_param("issssssi", $empresa_id, $nome, $sobrenome, $nascimento, $email, $cpf, $cll, $pagamento_id);
// if (!$dadosPessoais->execute()) {
//     die("Erro ao executar cliente: " . $dadosPessoais->error);
// }
// $cliente_id = $conn->insert_id;
// $dadosPessoais->close();

// if (!$cliente_id) {
//     die("Erro: cliente_id não foi gerado corretamente.");
// }


// // Inserir agendamentos com verificação de limite
// foreach ($servicos as $index => $servicoData) {
//     $servico_id = (int) $servicoData['servico_id'];
//     $dia = htmlspecialchars($dias[$index], ENT_QUOTES, 'UTF-8');
//     $hora = htmlspecialchars($horas[$index], ENT_QUOTES, 'UTF-8');

//     // Obter quantidade de funcionários do serviço
//     $stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ?");
//     $stmt->bind_param("i", $servico_id);
//     $stmt->execute();
//     $stmt->bind_result($qtdFuncionarios);
//     $stmt->fetch();
//     $stmt->close();

//     // Contar agendamentos existentes nesse dia/hora/serviço
//     $stmt = $conn->prepare("SELECT COUNT(*) FROM agendamento WHERE dia = ? AND hora = ? AND servico_id = ?");
//     $stmt->bind_param("ssi", $dia, $hora, $servico_id);
//     $stmt->execute();
//     $stmt->bind_result($qtdAgendadas);
//     $stmt->fetch();
//     $stmt->close();

//     // Se estiver cheio, cancelar o processo
//     if ($qtdAgendadas >= $qtdFuncionarios) {
//         echo "<p style='color:red;'>Horário $hora de $dia está indisponível para o serviço selecionado. Agendamento cancelado.</p>";
//         $conn->close();
//         exit;
//     }

//     // Se não estiver cheio, inserir normalmente
//     $agendamento = $conn->prepare(
//         "INSERT INTO agendamento (empresa_id, cliente_id, servico_id, dia, hora, pagamento_id) 
//          VALUES (?, ?, ?, ?, ?, ?)"
//     );
//     if (!$agendamento) {
//         die("Erro ao preparar agendamento: " . $conn->error);
//     }

//     $agendamento->bind_param("iiissi", $empresa_id, $cliente_id, $servico_id, $dia, $hora, $pagamento_id);
//     if (!$agendamento->execute()) {
//         die("Erro ao salvar agendamento: " . $agendamento->error);
//     }
//     $agendamento->close();

    
// }





// 1. Verificação de todos os horários antes de inserir qualquer coisa
foreach ($servicos as $index => $servicoData) {
    $servico_id = (int) $servicoData['servico_id'];
    $dia = htmlspecialchars($dias[$index], ENT_QUOTES, 'UTF-8');
    $hora = htmlspecialchars($horas[$index], ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ?");
    $stmt->bind_param("i", $servico_id);
    $stmt->execute();
    $stmt->bind_result($qtdFuncionarios);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM agendamento WHERE dia = ? AND hora = ? AND servico_id = ?");
    $stmt->bind_param("ssi", $dia, $hora, $servico_id);
    $stmt->execute();
    $stmt->bind_result($qtdAgendadas);
    $stmt->fetch();
    $stmt->close();

    if ($qtdAgendadas >= $qtdFuncionarios) {
        echo "<p style='color:red;'>Horário $hora de $dia está indisponível para o serviço selecionado. Agendamento cancelado.</p>";
        $conn->close();
        exit;  // cancela a execução antes de inserir pagamento/cliente/agendamento
    }
}

// 2. Se chegou aqui, todos os horários estão disponíveis — inserir pagamento
$pagamento = $conn->prepare(
    "INSERT INTO pagamento (empresa_id, qtdagendamentos, valor_pagar, status_pagamento) 
     VALUES (?, ?, ?, 'pendente')"
);
if (!$pagamento) {
    die("Erro ao preparar pagamento: " . $conn->error);
}
$pagamento->bind_param("iis", $empresa_id, $qtdagendamentos, $valor_total);
if (!$pagamento->execute()) {
    die("Erro ao executar pagamento: " . $pagamento->error);
}
$pagamento_id = $conn->insert_id;
$pagamento->close();

// 3. Inserir cliente
$dadosPessoais = $conn->prepare(
    "INSERT INTO clientes (empresa_id, nome, sobrenome, nascimento, email, cpf, celular, pagamento_id) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$dadosPessoais) {
    die("Erro ao preparar cliente: " . $conn->error);
}
$dadosPessoais->bind_param("issssssi", $empresa_id, $nome, $sobrenome, $nascimento, $email, $cpf, $cll, $pagamento_id);
if (!$dadosPessoais->execute()) {
    die("Erro ao executar cliente: " . $dadosPessoais->error);
}
$cliente_id = $conn->insert_id;
$dadosPessoais->close();

if (!$cliente_id) {
    die("Erro: cliente_id não foi gerado corretamente.");
}

// 4. Inserir os agendamentos
foreach ($servicos as $index => $servicoData) {
    $servico_id = (int) $servicoData['servico_id'];
    $dia = htmlspecialchars($dias[$index], ENT_QUOTES, 'UTF-8');
    $hora = htmlspecialchars($horas[$index], ENT_QUOTES, 'UTF-8');

    $agendamento = $conn->prepare(
        "INSERT INTO agendamento (empresa_id, cliente_id, servico_id, dia, hora, pagamento_id) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    if (!$agendamento) {
        die("Erro ao preparar agendamento: " . $conn->error);
    }
    $agendamento->bind_param("iiissi", $empresa_id, $cliente_id, $servico_id, $dia, $hora, $pagamento_id);
    if (!$agendamento->execute()) {
        die("Erro ao salvar agendamento: " . $agendamento->error);
    }
    $agendamento->close();
}









// MercadoPago
$item = new Item();
$item->title = $servico;
$item->quantity = 1;
$item->unit_price = $valor_total;

$preference = new Preference();
$preference->items = [$item];
$preference->external_reference = $pagamento_id;

$base_url = "https://91d5-2804-7f0-b7c2-9926-2db3-d39c-7cf6-bded.ngrok-free.app/wesley/pages";

$preference->back_urls = [
    "success" => $base_url . "/sucesso.html",
    "failure" => $base_url . "/falha.html",
    "pending" => $base_url . "/pendente.html"
];
$preference->auto_return = "approved";
$preference->notification_url = $base_url . "/confirmacao_vendas/notificacao.php";

try {
    $preference->save();
    if (!$preference->init_point) {
        throw new Exception("Erro ao gerar link de pagamento.");
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

if (isset($dadosPessoais) && $dadosPessoais instanceof mysqli_stmt) {
    try {
        $dadosPessoais->close();
    } catch (Throwable $e) {
        // já estava fechado, ignora
    }
}

if (isset($pagamento) && $pagamento instanceof mysqli_stmt) {
    try {
        $pagamento->close();
    } catch (Throwable $e) {
        // já estava fechado, ignora
    }
}


// Dados do serviço (exibir na tela)
$sql = "SELECT tipo_servico, duracao_servico, valor FROM servico WHERE empresa_id = ? AND id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação: " . $conn->error);
}
$stmt->bind_param("ii", $empresa_id, $servico_id);
$stmt->execute();
$stmt->bind_result($tipo_servico, $duracao_servico, $valor);
$stmt->fetch();
$stmt->close();

// Exibição final
echo "<h1>Obrigado, " . htmlspecialchars($nome) . ", pela sua preferência!</h1>";
echo "<p>Serviço: <strong>" . htmlspecialchars($tipo_servico) . "</strong></p>";
echo "<p>Data: <strong>" . htmlspecialchars($dia) . "</strong> às <strong>" . htmlspecialchars($hora) . "</strong></p>";
echo "<p>Duração unitária: <strong>" . htmlspecialchars($duracao_servico) . " sessão(ões)</strong></p>";
echo "<p>Total: R$ " . number_format($valor_total, 2, ',', '.') . "</p>";
echo "<p><a href='" . htmlspecialchars($preference->init_point) . "' target='_blank' rel='noopener noreferrer'>Clique aqui para pagar</a></p>";
?>
</body>
</html>
