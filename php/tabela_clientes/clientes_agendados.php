<?php
include '../login_empresa/get_id.php';
include '../conexao.php';

// 1) Buscar clientes
$stmtClientes = $conn->prepare("SELECT id, empresa_id, nome, sobrenome, cpf, nascimento, email, celular, pagamento_id FROM clientes WHERE empresa_id = ?");

if (!$stmtClientes) {
    die("Erro no prepare dos clientes: " . $conn->error);
}

$stmtClientes->bind_param("i", $empresa_id);

$stmtClientes->execute();
$resultClientes = $stmtClientes->get_result();

// Criar array clientes por id para consulta rápida
$clientes = [];
while ($row = $resultClientes->fetch_assoc()) {
    $clientes[$row['id']] = $row;
}

// 2) Buscar agendamentos
$stmtAgend = $conn->prepare("SELECT id, empresa_id, cliente_id, servico_id, dia, hora, ja_atendido, motivo_falta, pagamento_id FROM agendamento WHERE empresa_id = ?");
$stmtAgend->bind_param("i", $empresa_id);
$stmtAgend->execute();
$resultAgend = $stmtAgend->get_result();

// 3) Buscar pagamentos
$stmtPag = $conn->prepare("SELECT id, empresa_id, qtdagendamentos, valor_pagar, status_pagamento, created_at FROM pagamento WHERE empresa_id = ?");
$stmtPag->bind_param("i", $empresa_id);
$stmtPag->execute();
$resultPag = $stmtPag->get_result();

// Criar array pagamentos por id para consulta rápida
$pagamentos = [];
while ($row = $resultPag->fetch_assoc()) {
    $pagamentos[$row['id']] = $row;
}

// 4) Buscar serviços
$stmtServ = $conn->prepare("SELECT id, tipo_servico, valor, duracao_servico FROM servico WHERE empresa_id = ?");
$stmtServ->bind_param("i", $empresa_id);
$stmtServ->execute();
$resultServ = $stmtServ->get_result();

// Criar array de serviços por id
$servicos = [];
while ($row = $resultServ->fetch_assoc()) {
    $servicos[$row['id']] = $row; // <- Certifique-se que isso seja 'id', não 'servico_id'
}


?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"/>
    <title>Clientes, Agendamentos e Pagamentos</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <a href="../agendamentos/tela_inicial_empresa.php">voltar</a>
    <select id="filtro-agendamentos">
        <option value="todos">Todos</option>
        <option value="pagos">Pagos</option>
        <option value="nao-pagos">Não pagos</option>
        <option value="compareceu">Compareceram</option>
        <option value="nao-compareceu">Não compareceram</option>
        <option value="Em espera">Em espera</option>
    </select>
    <input type="text" id="pesquisa-geral" placeholder="Pesquisar por qualquer informação..." style="margin-bottom:10px; padding:5px; width: 300px;">
    <button id="botao-pesquisa" style="padding: 5px 10px; cursor: pointer;">🔍</button>



    <h1>Clientes, Agendamentos e Pagamentos</h1>
    <?php
    
    // Agrupar agendamentos por cliente_id
    $agendamentos_por_cliente = [];
    while ($agendamento = $resultAgend->fetch_assoc()) {
        $agendamentos_por_cliente[$agendamento['cliente_id']][] = $agendamento;
    }

    // Percorrer clientes com seus agendamentos
    foreach ($agendamentos_por_cliente as $cliente_id => $agendamentos) {
        $cliente = $clientes[$cliente_id] ?? null;

        if ($cliente) {
            $pagamento_id = $cliente['pagamento_id'];
            $pagamento = $pagamentos[$pagamento_id] ?? null;

            // Linha com dados do cliente e pagamento
            echo "
            <table>      
                <tbody>  
                    <tr>
                        <tr id='cabecalho-cliente-{$cliente['id']}' class='cabecalho-cliente'>
                            <th>Cliente ID</th>
                            <th>Nome</th>
                            <th>Sobrenome</th>
                            <th>CPF</th>
                            <th>Nascimento</th>
                            <th>E-mail</th>
                            <th>Celular</th>
                            <th>Valor a Pagar (Pagamento)</th>
                            <th>Status Pagamento</th>
                            <th>Pagamento Criado Em</th>
                        </tr>
            ";
                        echo "<td>" . htmlspecialchars($cliente['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($cliente['nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($cliente['sobrenome']) . "</td>";
                        echo "<td>" . htmlspecialchars($cliente['cpf']) . "</td>";
                        echo "<td>" . htmlspecialchars($cliente['nascimento']) . "</td>";
                        echo "<td>" . htmlspecialchars($cliente['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($cliente['celular']) . "</td>";

                        if ($pagamento) {
                            echo "<td>R$ " . number_format($pagamento['valor_pagar'], 2, ',', '.') . "</td>";
                            echo "<td>" . htmlspecialchars($pagamento['status_pagamento']) . "</td>";
                            echo "<td>" . htmlspecialchars($pagamento['created_at']) . "</td>";
                        } else {
                            echo "<td colspan='3'>Pagamento não encontrado</td>";
                        }
                    echo "</tr>";

                        // Linhas com os agendamentos (abaixo do cliente)
                        
                            echo "<tr id='cabecalho-agendamento-{$cliente['id']}' class='cabecalho-agendamento'>
                                <th>Tipo do Serviço</th>
                                <th>Dia</th>
                                <th>Hora</th>
                                <th>Valor</th>
                                <th>Duração</th>
                                <th>Status de atendimento</th>
                                <th class='coluna-motivo'>Motivo de não atendimento</th>
                            </tr>";

                            foreach ($agendamentos as $ag) {
                                $servico = $servicos[$ag['servico_id']] ?? null;

                    echo "<tr>";
                                echo "<td>" . htmlspecialchars($servico['tipo_servico']) . "</td>";
                                echo "<td>" . htmlspecialchars($ag['dia']) . "</td>";
                                echo "<td>" . htmlspecialchars($ag['hora']) . "</td>";

                               if ($servico) {
    echo "<td>R$ " . number_format($servico['valor'], 2, ',', '.') . "</td>";
    echo "<td>" . htmlspecialchars($servico['duracao_servico']) . " min</td>";

    // Coluna de status de atendimento
    echo "<td>";
    $foiAtendido = $ag['ja_atendido'] === 'sim';
    $naoAtendido = $ag['ja_atendido'] === 'nao';
    $pendente = !$foiAtendido && !$naoAtendido; // NULL ou outro valor

    if ($ag['ja_atendido'] === 'sim') {
        echo "✅ ATENDIDO";
    } elseif ($ag['ja_atendido'] === 'nao') {
        echo "❌ NÃO ATENDIDO";
    } else {
        echo "⏳ EM ESPERA";
        echo "<button class='btn-presenca' data-id='{$ag['id']}' data-presenca='sim'>✅ Atendido</button>";
    }
    echo "</td>";

    // Coluna motivo da falta
    echo "<td class='motivo-falta' data-id='{$ag['id']}'>";

    $motivo = htmlspecialchars($ag['motivo_falta'] ?? '');

    if ($foiAtendido) {
        echo "-";
    } elseif ($naoAtendido && !empty($motivo)) {
        echo "<p>$motivo</p>";
    } elseif ($pendente) {
        // ainda em espera, mostra textarea
        echo "<textarea placeholder='Motivo da falta...' data-id='{$ag['id']}' class='comentario-falta'></textarea>";
        echo "<button class='btn-salvar-comentario' data-id='{$ag['id']}'>Salvar Motivo</button>";
    }

    echo "</td>";
} else {
    echo "<td colspan='3'>Serviço não encontrado</td>";
}


                    echo "</tr>";
                            }

                                    
        }
    }
    ?>
            </tbody>
        </table>
    <script src="../../javaScript/clientes_agendamentos.js"></script>
</body>
</html>
