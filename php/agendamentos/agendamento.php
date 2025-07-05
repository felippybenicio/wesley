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
    include '../monitoramentos/pausar_temporariamente.php';
    require_once __DIR__ . '/../vendor/autoload.php';

    $configs = $conn->query("SELECT * FROM servico")->fetch_all(MYSQLI_ASSOC);
    $diaHoraDisponivel = $conn->query("SELECT dia hora FROM agendamento")->fetch_all(MYSQLI_ASSOC);


    $nome       = $_POST["nome"] ?? '';
    $sobrenome  = $_POST["sobrenome"] ?? '';
    $nascimento = $_POST["nascimento"] ?? '';
    $email      = $_POST["email"] ?? '';
    $cpf        = $_POST["cpf"] ?? '';
    $cll        = $_POST["cll"] ?? '';
    $servico    = $_POST["servico"] ?? '';
    preg_match('/\d+/', $_POST["qtdagendamentos"] ?? '', $match);
    $qtdagendamentos = (int)($match[0] ?? 1);
    $dia        = $_POST["dia"] ?? '';
    $hora       = $_POST["hora"] ?? '';
    $servico_id = isset($_POST['servico']) ? (int)$_POST['servico'] : null;



    // Obtemos a quantidade de funcionários do serviço
    $stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ?");
    if (!$stmt) {
        die("Erro no prepare (funcionários): " . $conn->error);
    }
    $stmt->bind_param("i", $servico_id);
    $stmt->execute();
    $stmt->bind_result($qtdFuncionarios);
    $stmt->fetch();
    $stmt->close();


$configs = $conn->query("SELECT * FROM servico")->fetch_all(MYSQLI_ASSOC);
foreach ($configs as $config) {
    $qtdFuncionarios = $config['quantidade_de_funcionarios'];
}

$valorAtual = 0;

$stmt = $conn->prepare("SELECT valor FROM servico WHERE id = ?");
if (!$stmt) {
    die("Erro no prepare (valor): " . $conn->error);
}
$stmt->bind_param("i", $servico_id);
$stmt->execute();
$stmt->bind_result($valorAtual);
$stmt->fetch();
$stmt->close();

$valorAtual = (float)$valorAtual;



if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}



$valor_total = 0;
$qtdagendamentos = isset($_POST['agendamentos']) && is_array($_POST['agendamentos']) 
    ? count($_POST['agendamentos']) 
    : 0;


// Já calcula o valor total antes
if (isset($_POST['agendamentos']) && is_array($_POST['agendamentos'])) {
    foreach ($_POST['agendamentos'] as $ag) {
        $servico_id = (int)($ag['servico_id'] ?? 0);

        if ($servico_id > 0) {
            $stmt = $conn->prepare("SELECT valor FROM servico WHERE empresa_id = ? AND id = ?");
            $stmt->bind_param("ii", $empresa_id, $servico_id);
            $stmt->execute();
            $stmt->bind_result($valor_unitario);
            $stmt->fetch();
            $stmt->close();

            $valor_total += (float)$valor_unitario;
        }
    }
} else {
    // Evita erro: array não veio
    $_POST['agendamentos'] = []; // (opcional)
    $valor_total = 0;
}

$dias = $_POST['dia'];               
$horas = $_POST['hora'];    
$servicos = $_POST['agendamentos'];
$hoje = date('Y-m-d');


$hoje = date('Y-m-d');
$cpfLimpo = preg_replace('/\D/', '', $cpf);


$sql = "
    SELECT COUNT(*) 
    FROM agendamento a
    JOIN clientes c ON a.cliente_id = c.id
    WHERE c.empresa_id = ? 
      AND REPLACE(REPLACE(TRIM(c.cpf), '.', ''), '-', '') = ?
      AND a.ja_atendido IS NULL
";

$verificaAgendamento = $conn->prepare($sql);
$verificaAgendamento->bind_param("is", $empresa_id, $cpfLimpo);
$verificaAgendamento->execute();
$verificaAgendamento->bind_result($qtdEmAberto);
$verificaAgendamento->fetch();
$verificaAgendamento->close();

if ($qtdEmAberto > 0) {
    echo "<p style='color:red;'>Você já possui um agendamento em andamento. Finalize ou cancele antes de marcar outro.</p>";
    $conn->close();
    exit;
}


$sql = "
    SELECT COUNT(*) 
    FROM agendamento a
    JOIN pagamento p ON a.pagamento_id = p.id
    JOIN clientes c ON a.cliente_id = c.id
    WHERE c.empresa_id = ?
      AND REPLACE(REPLACE(TRIM(c.cpf), '.', ''), '-', '') = ?
      AND p.status_pagamento = 'pendente'
";

$verificaPagamento = $conn->prepare($sql);
$verificaPagamento->bind_param("is", $empresa_id, $cpfLimpo);
$verificaPagamento->execute();
$verificaPagamento->bind_result($qtdPendentes);
$verificaPagamento->fetch();
$verificaPagamento->close();

if ($qtdPendentes > 0) {
    echo "<p style='color:red;'>Você possui pagamento pendente. Regularize antes de agendar novamente.</p>";
    $conn->close();
    exit;
}



// 3) Se chegou aqui, libera verificar disponibilidade por serviço e inserir agendamento
foreach ($servicos as $index => $servicoData) {
    $servico_id = (int) $servicoData['servico_id'];
    $dia = htmlspecialchars($dias[$index], ENT_QUOTES, 'UTF-8');
    $hora = htmlspecialchars($horas[$index], ENT_QUOTES, 'UTF-8');

    // Verificar limite de funcionários para o serviço
    $stmt = $conn->prepare("SELECT quantidade_de_funcionarios FROM servico WHERE id = ?");
    $stmt->bind_param("i", $servico_id);
    $stmt->execute();
    $stmt->bind_result($qtdFuncionarios);
    $stmt->fetch();
    $stmt->close();

    // Verificar agendamentos ocupados no mesmo dia/hora/serviço, que não foram atendidos ainda
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM agendamento 
        WHERE dia = ? 
        AND hora = ? 
        AND servico_id = ? 
        AND (ja_atendido IS NULL OR ja_atendido = '')
    ");
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
// Verificar se o cliente já existe
$cliente_id = null;

$verificaCliente = $conn->prepare("
    SELECT id FROM clientes 
    WHERE empresa_id = ? AND cpf = ?
");
$verificaCliente->bind_param("is", $empresa_id, $cpf);
$verificaCliente->execute();
$verificaCliente->bind_result($cliente_id_existente);
$verificaCliente->fetch();
$verificaCliente->close();

if ($cliente_id_existente) {
    // Cliente já existe, usar o ID dele
    $cliente_id = $cliente_id_existente;
} else {
    // Cliente ainda não existe, então inserir
    $dadosPessoais = $conn->prepare("
        INSERT INTO clientes (empresa_id, nome, sobrenome, nascimento, email, cpf, celular) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$dadosPessoais) {
        die("Erro ao preparar cliente: " . $conn->error);
    }

    $dadosPessoais->bind_param("issssss", $empresa_id, $nome, $sobrenome, $nascimento, $email, $cpf, $cll);
    
    if (!$dadosPessoais->execute()) {
        die("Erro ao executar cliente: " . $dadosPessoais->error);
    }

    $cliente_id = $conn->insert_id;
    $dadosPessoais->close();

    if (!$cliente_id) {
        die("Erro: cliente_id não foi gerado corretamente.");
    }
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

    $tipoPagamento = '';
    $pixAcesskey = '';
    $nomeEmpresa = '';
    $cidade = '';

    $stmt = $conn->prepare("SELECT tipo_pagamento, pix_acesskey, nome_empresa, cidade FROM cadastro_empresa WHERE id = ?");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $stmt->bind_result($tipoPagamento, $pixAcesskey, $nomeEmpresa, $cidade);
    $stmt->fetch();
    $stmt->close();

    use MercadoPago\SDK;
    use MercadoPago\Preference;
    use MercadoPago\Item;
    
    

    


 

switch ($tipoPagamento) {
   
    case 'Sem Vinculo':
        
       break;

    case 'Mercado Pago':
        error_reporting(E_ERROR | E_PARSE); // mostra só erros fatais e parse errors
        ini_set('display_errors', 0);

        SDK::setAccessToken("$pixAcesskey"); //TEST-4822365570526425-050519-215ba645d826f7e7eaaf08fdcb14d090-2426282036

        $item = new Item();
        $item->title = $servico;
        $item->quantity = 1;
        $item->unit_price = $valor_total;

        $preference = new Preference();
        $preference->items = [$item];
        $preference->external_reference = $pagamento_id;

        $base_url = "https://72ac-2804-7f0-b7c3-3550-4cdc-32f-ed54-d378.ngrok-free.app/sistema-agendamento/pages";

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
                throw new Exception("Erro ao gerar link de pagamento. verifique se o token esta correto");
            }
        } catch (Exception $e) {
            echo "<p style='color:red;'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
            exit;
        }
        break;
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

foreach ($servicos as $index => $servicoData) {
    $servico_id = (int) $servicoData['servico_id'];
    $dia = htmlspecialchars($dias[$index] ?? '', ENT_QUOTES, 'UTF-8');
    $hora = htmlspecialchars($horas[$index] ?? '', ENT_QUOTES, 'UTF-8');

    // Buscar os dados do serviço no banco
    $sql = "SELECT tipo_servico, duracao_servico, valor FROM servico WHERE empresa_id = ? AND id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $empresa_id, $servico_id);
    $stmt->execute();
    $stmt->bind_result($tipo_servico, $duracao_servico, $valor);
    $stmt->fetch();
    $stmt->close();

    // Exibir as informações do serviço
    echo "<div style='margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px;'>";
    echo "<p>Serviço: <strong>" . htmlspecialchars($tipo_servico) . "</strong></p>";
    echo "<p>Data: <strong>$dia</strong> às <strong>$hora</strong></p>";
    echo "<p>Duração unitária: <strong>" . htmlspecialchars($duracao_servico) . "</strong></p>";
    echo "<p>Valor: R$ " . number_format($valor, 2, ',', '.') . "</p>";
    echo "</div>";

    echo "<p>valor total</p>";
    echo "R$" . number_format($valor_total, 2, ',', '.');
}


switch ($tipoPagamento) {
    case 'Sem Vinculo':
        
        break;
    case 'Mercado Pago': 
        echo "<p><a href='" . htmlspecialchars($preference->init_point) . "' target='_blank' rel='noopener noreferrer'>Clique aqui para pagar</a></p>";
        break;
}


?>

<div id="pix-area" style="display: none; text-align: center; margin-top: 20px;">
    <h3>Escaneie com seu app de banco:</h3>
    <img src="<?= $qrCodeUrl ?>" alt="QR Code Pix">
    <p><strong>Código Pix:</strong></p>
    <textarea id="codigoPix" rows="4" cols="50" readonly><?= $codePix ?></textarea><br>
    <button onclick="copiarPix()">Copiar código Pix</button>
</div>

<script>
function mostrarPix() {
    document.getElementById('pix-area').style.display = 'block';
}

function copiarPix() {
    const codigo = document.getElementById('codigoPix'); 
    codigo.select();
    codigo.setSelectionRange(0, 99999); // para mobile
    document.execCommand("copy");
    alert("Código Pix copiado!");
}
</script>


</body>
</html>
