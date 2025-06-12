<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes Agendados</title>
</head>
<body>
    <h1>Clientes Agendados</h1>
    <?php
        $conexao = new mysqli('localhost', 'root', 'Duk23092020$$', 'consultorio');

        

        $sql = "
            SELECT dp.*, pg.tipo_de_servico, pg.duracao, pg.valor_pago, pg.status_pagamento
            FROM dados_pessoais dp
            JOIN pagamento pg ON dp.pagamento_id = pg.id";
        $resultado = $conexao->query($sql);

    ?>
    <main>
        <table>
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Sobrenome</th>
                    <th scope="col">CPF</th>
                    <th scope="col">Nascimento</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Celular</th>
                    <th scope="col">Data</th>
                    <th scope="col">Hora</th>
                    <th scope="col">Serviço</th>
                    <th scope="col">Tempo da Sessão</th>
                    <th scope="col">Valor</th>
                    <th scope="col">Status do Pagamento</th>

                </tr>
            </thead>
        
    </main>
    <?php
        
        while ($dados = mysqli_fetch_assoc($resultado)) {
            echo "<tbody>";
                echo "<tr>";
                    echo "<td>" . $dados['id'] . "</td>";
                    echo "<td>" . $dados['nome'] . "</td>";
                    echo "<td>" . $dados['sobrenome'] . "</td>";
                    echo "<td>" . $dados['cpf'] . "</td>";
                    echo "<td>" . $dados['nascimento'] . "</td>";
                    echo "<td>" . $dados['email'] . "</td>";
                    echo "<td>" . $dados['celular'] . "</td>";
                    echo "<td>" . $dados['dia'] . "</td>";
                    echo "<td>" . $dados['hora'] . "</td>";
                    echo "<td>" . $dados['tipo_de_servico'] . "</td>";
                    echo "<td>" . $dados['duracao'] . "</td>";
                    echo "<td>" . $dados['valor_pago'] . "</td>";
                    echo "<td>" . $dados['status_pagamento'] . "</td>";
                echo "</tr>";
            echo "</tbody>";
        echo "</teble>";

        }
    ?>
</body>
</html>