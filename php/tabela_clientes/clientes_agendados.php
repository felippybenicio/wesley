<?php
include '../login_empresa/get_id.php';
include '../conexao.php';

// 1) Buscar clientes
$stmtClientes = $conn->prepare("SELECT id, empresa_id, nome, sobrenome, cpf, nascimento, email, celular, pagamento_id, cadastrado_em FROM clientes WHERE empresa_id = ?");

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
        <option value="agendado">Agendado</option>
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
    if (!$cliente) continue;

    echo "<table>";
    echo "<tr>
            <th>Cliente ID</th>
            <th>Nome</th>
            <th>Sobrenome</th>
            <th>CPF</th>
            <th>Nascimento</th>
            <th>Email</th>
            <th>Celular</th>
            <th>Dia cadastrado</th>
          </tr>";

    echo "<tr>
            <td>{$cliente['id']}</td>
            <td>{$cliente['nome']}</td>
            <td>{$cliente['sobrenome']}</td>
            <td>{$cliente['cpf']}</td>
            <td>{$cliente['nascimento']}</td>
            <td>{$cliente['email']}</td>
            <td>{$cliente['celular']}</td>
            <td>{$cliente['cadastrado_em']}</td>
          </tr>";

    // Agora agrupa os agendamentos por pagamento_id
    $agendamentosPorPagamento = [];
    foreach ($agendamentos as $ag) {
        $pagId = $ag['pagamento_id'];
        $agendamentosPorPagamento[$pagId][] = $ag;
    }

    foreach ($agendamentosPorPagamento as $pagamento_id => $listaAgs) {
        $pagamento = $pagamentos[$pagamento_id] ?? null;

        echo "<tr>
                <th colspan='3'>Total a pagar</th>
                <th>Status pagamento</th>
                <th>Dia agendamento</th>
              </tr>";

        if ($pagamento) {
            echo "<tr>
                    <td colspan='3'>R$ " . number_format($pagamento['valor_pagar'], 2, ',', '.') . "</td>
                    <td>{$pagamento['status_pagamento']}</td>
                    <td>{$pagamento['created_at']}</td>
                  </tr>";
        } else {
            echo "<tr><td colspan='5'>Pagamento não encontrado</td></tr>";
        }

        echo "<tr>
                <th>Tipo do Serviço</th>
                <th>Dia</th>
                <th>Hora</th>
                <th>Valor</th>
                <th>Duração</th>
                <th>Status de atendimento</th>
                <th>Motivo de não atendimento</th>
              </tr>";

        foreach ($listaAgs as $ag) {
            $servico = $servicos[$ag['servico_id']] ?? null;
            $foiAtendido = $ag['ja_atendido'] === 'sim';
            $temMotivo = !empty($ag['motivo_falta']);

            echo "<tr>";
            echo "<td>" . ($servico['tipo_servico'] ?? 'Desconhecido') . "</td>";
            echo "<td>{$ag['dia']}</td>";
            echo "<td>{$ag['hora']}</td>";
            echo "<td>R$ " . number_format($servico['valor'] ?? 0, 2, ',', '.') . "</td>";
            echo "<td>{$servico['duracao_servico']} min</td>";

            // Atendimento
            echo "<td>";
            if ($foiAtendido) {
                echo "✅ ATENDIDO";
            } elseif ($temMotivo) {
                echo "❌ NÃO ATENDIDO";
            } else {
                echo "⏳ AGENDADO <button class='btn-presenca' data-id='{$ag['id']}'>✅ Atendido</button>";
            }
            echo "</td>";

            // Motivo falta
            echo "<td class='motivo-falta'>";
            if ($foiAtendido) {
                echo "-";
            } elseif ($temMotivo) {
                echo "<p>" . htmlspecialchars($ag['motivo_falta']) . "</p>";
            } else {
                echo "<textarea placeholder='Motivo da falta...' data-id='{$ag['id']}' class='comentario-falta'></textarea>";
                echo "<button class='btn-salvar-comentario' data-id='{$ag['id']}'>Salvar Motivo</button>";
            }
            echo "</td>";
            echo "</tr>";
        }
    }

    echo "</table>";


}
    ?>
            </tbody>
        </table>
    <script src="../../javaScript/clientes_agendamentos.js"></script>
</body>
</html>
