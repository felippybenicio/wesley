<?php
    include '../login_empresa/get_id.php';
    include '../conexao.php';

    $sql = "
    SELECT 
        ce.id AS empresa_id,
        ce.nome_empresa,
        ce.ramo_empresa,
        ce.email_profissional,
        ce.dia_cadastrado,
        qs.quantidade_de_servico,
        qs.agendamentos_por_clientes,
        s.tipo_servico,
        s.valor,
        s.quantidade_de_funcionarios,
        s.duracao_servico,
        s.intervalo_entre_servico
    FROM cadastro_empresa ce
    LEFT JOIN quantidade_servico qs ON ce.id = qs.empresa_id
    LEFT JOIN servico s ON ce.id = s.empresa_id
    ORDER BY ce.nome_empresa ASC, s.tipo_servico ASC
";

$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

// Agrupa os dados por empresa_id
$empresas = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['empresa_id'];
    if (!isset($empresas[$id])) {
        // Cria um array para cada empresa com os dados principais e um array vazio de serviços
        $empresas[$id] = [
            'nome_empresa' => $row['nome_empresa'],
            'ramo_empresa' => $row['ramo_empresa'],
            'email_profissional' => $row['email_profissional'],
            'dia_cadastrado' => $row['dia_cadastrado'],
            'quantidade_de_servico' => $row['quantidade_de_servico'],
            'agendamentos_por_clientes' => $row['agendamentos_por_clientes'],
            'servicos' => []
        ];
    }

    // Se existir tipo_servico, adiciona à lista de serviços da empresa
    if ($row['tipo_servico']) {
        $empresas[$id]['servicos'][] = [
            'tipo_servico' => $row['tipo_servico'],
            'valor' => $row['valor'],
            'quantidade_de_funcionarios' => $row['quantidade_de_funcionarios'],
            'duracao_servico' => $row['duracao_servico'],
            'intervalo_entre_servico' => $row['intervalo_entre_servico']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Empresas e Serviços</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .empresa-titulo {
            background: #007BFF;
            color: white;
            padding: 10px;
            margin-top: 40px;
            font-weight: bold;
            border-radius: 4px;
        }
        .empresa-dados {
            margin-bottom: 10px;
            font-size: 14px;
        }
        table.servicos {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 30px;
        }
        table.servicos th, table.servicos td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 14px;
            text-align: left;
        }
        table.servicos th {
            background-color: #f2f2f2;
        }
        table.servicos tr:nth-child(even) {
            background-color: #fafafa;
        }
    </style>
</head>
<body>
<a href="../..\pages\login_empresa\tela_cadastro_login.html">cadastrar novas empresas</a>
<?php foreach ($empresas as $empresa_id => $empresa): ?>
    <div class="empresa-titulo">
        <?= htmlspecialchars($empresa['nome_empresa']) ?> (ID: <?= $empresa_id ?>)
    </div>
    <div class="empresa-dados">
        <strong>Ramo:</strong> <?= htmlspecialchars($empresa['ramo_empresa']) ?> |
        <strong>Email:</strong> <?= htmlspecialchars($empresa['email_profissional']) ?> |
        <strong>Cadastrado em:</strong> <?= htmlspecialchars($empresa['dia_cadastrado']) ?> |
        <strong>Qtd Serviços:</strong> <?= htmlspecialchars($empresa['quantidade_de_servico']) ?> |
        <strong>Agend. por Cliente:</strong> <?= htmlspecialchars($empresa['agendamentos_por_clientes']) ?>
    </div>

    <?php if (count($empresa['servicos']) > 0): ?>
    <table class="servicos">
        <thead>
            <tr>
                <th>Tipo de Serviço</th>
                <th>Valor (R$)</th>
                <th>Funcionários</th>
                <th>Duração</th>
                <th>Intervalo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empresa['servicos'] as $servico): ?>
            <tr>
                <td><?= htmlspecialchars($servico['tipo_servico']) ?></td>
                <td><?= number_format((float)$servico['valor'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($servico['quantidade_de_funcionarios']) ?></td>
                <td><?= htmlspecialchars($servico['duracao_servico']) ?></td>
                <td><?= htmlspecialchars($servico['intervalo_entre_servico']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p><em>Sem serviços cadastrados para esta empresa.</em></p>
    <?php endif; ?>
<?php endforeach; ?>

</body>
</html>