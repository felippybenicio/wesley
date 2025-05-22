<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>configuração</title>
</head>
<body>

<?php


$quantidadeServicos = $_POST['quantidadeServicos'] ?? 1;

$servicos = [];

for ($i = 2; $i <= $quantidadeServicos; $i++) {
    $tipo = $_POST["tipo$i"] ?? '';
    $valor = $_POST["valor$i"] ?? '';
    $qtFunc = $_POST["qtFuncionario$i"] ?? 1;

    $funcionarios = [];
    for ($j = 1; $j <= $qtFunc; $j++) {
        $funcionarios[] = $_POST["funcionario{$i}_{$j}"] ?? '';
    }

    $servicos[$i] = [
        'tipo' => $tipo,
        'valor' => $valor,
        'qtFuncionario' => $qtFunc,
        'funcionarios' => $funcionarios
    ];
}






    $mesesIndisponiveis = $_POST['mesIndisponivel'] ?? [];
    $diasSemanaIndisponiveis = $_POST['semanaIndisponivel'] ?? [];
    $quantidadeServicos = $_POST['quantidadeServicos'] ?? 1;

    $tipos = [];
    $valores = [];
    $qtFuncionarios = [];
    $funcionarios = [];

    for ($i = 1; $i <= $quantidadeServicos; $i++) {
        $tipos[$i] = $_POST["tipo$i"] ?? '';
        $valores[$i] = $_POST["valor$i"] ?? '';
        $qtFuncionarios[$i] = $_POST["qtFuncionario$i"] ?? 1;

        for ($j = 1; $j <= $qtFuncionarios[$i]; $j++) {
            $funcionarios[$i][$j] = $_POST["funcionario{$i}_{$j}"] ?? '';
        }
    }

    $horario = [
        'inicio' => $_POST['Hinicio'] ?? '',
        'fim' => $_POST['Htermino'] ?? '',
        'duracao' => $_POST['duracaoServico'] ?? '',
        'intervalo' => $_POST['intervaloServico'] ?? ''
    ];

    $conn = new mysqli('localhost', 'root', 'Duk23092020$$', 'consultorio');
    if (isset($_POST['remover_data'])) {
            $data = $_POST['remover_data'];

            $sql = "DELETE FROM dia_indisponivel WHERE data = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $data);
            $stmt->execute();
            $stmt->close();
            
            // Recarrega a página
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
    }
?>



    <h1>configuração</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <h2>serviços</h2>
        <label for="quantidadeServicos">quantidade de serviços</label>
        <input type="number" name="quantidadeServicos" id="quantidadeServicos" value="<?= htmlspecialchars($quantidadeServicos) ?>" min="1" max="5">
        

        <h3>Serviço 1</h3>
         <div>
            <label for="tipo1">Serviço:</label>
            <input type="text" name="tipo1" id="tipo1" value="<?= htmlspecialchars($tipos[1]) ?>">
        </div><br>

        <div>
            <label for="valor1">Valor:</label>
            <input type="number" step="0.01" name="valor1" id="valor1" value="<?= htmlspecialchars($valores[1]) ?>">
        </div>

        <div>
            <label for="qtFuncionario1">Quantidade de funcionarios para este serviço:</label>
            <input type="number" name="qtFuncionario1" id="qtFuncionario1" value="<?= htmlspecialchars($qtFuncionarios[1]) ?>" min="1" max="5">

            <div>
                <label for="funcionario1_1">nome do funcionarios 1:</label>
                <input type="text" name="funcionario1_1" id="funcionario1_1" value="<?= htmlspecialchars($funcionarios[1][1]) ?>">
            </div>

            <div id="funcionarios"></div>


        </div>
        <div id="camposServicos"></div>
        
       
        
        <h2>mes indisponivel</h2>
        <label>
            <input type="checkbox" class="mes-checkbox" name="mesIndisponivel[]" value="1" 
                <?php if (in_array(1, $mesesIndisponiveis)) echo 'checked'; ?>> Janeiro
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="2"
            <?php if (in_array(2, $mesesIndisponiveis)) echo 'checked'; ?>> Fevereiro
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="3"
            <?php if (in_array(3, $mesesIndisponiveis)) echo 'checked'; ?>> Março
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="4"
            <?php if (in_array(4, $mesesIndisponiveis)) echo 'checked'; ?>> Abril
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="5"
            <?php if (in_array(5, $mesesIndisponiveis)) echo 'checked'; ?>> Maio
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="6"
            <?php if (in_array(6, $mesesIndisponiveis)) echo 'checked'; ?>> Junho
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="7"
            <?php if (in_array(7, $mesesIndisponiveis)) echo 'checked'; ?>> Julho
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="8"
            <?php if (in_array(8, $mesesIndisponiveis)) echo 'checked'; ?>> Agosto
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="9"
            <?php if (in_array(9, $mesesIndisponiveis)) echo 'checked'; ?>> Setembro
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="10"
            <?php if (in_array(10, $mesesIndisponiveis)) echo 'checked'; ?>> Outubro
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="11"
            <?php if (in_array(11, $mesesIndisponiveis)) echo 'checked'; ?>> Novembro
        </label>
        <label>
            <input type="checkbox" name="mesIndisponivel[]" class="mes-checkbox" value="12"
            <?php if (in_array(12, $mesesIndisponiveis)) echo 'checked'; ?>> Dezembro
        </label>




        <h2>dias da semana indisponiveis</h2>
        <label>
        <input type="checkbox" name="semanaIndisponivel[]" class="sem-checkbox" value="0"
        <?php if (in_array(0, $diasSemanaIndisponiveis)) echo 'checked'; ?>> Domingo

        </label>
        <label>
            <input type="checkbox" name="semanaIndisponivel[]" class="sem-checkbox"  value="1"
                <?php if (in_array(1, $diasSemanaIndisponiveis)) echo 'checked'; ?>> Segunda
        </label>

        <label>
            <input type="checkbox" name="semanaIndisponivel[]" class="sem-checkbox" value="2"
            <?php if (in_array(2, $diasSemanaIndisponiveis)) echo 'checked'; ?>> Terça
        </label>
        <label>
            <input type="checkbox" name="semanaIndisponivel[]" class="sem-checkbox" value="3"
            <?php if (in_array(3, $diasSemanaIndisponiveis)) echo 'checked'; ?>> Quarta
        </label>
        <label>
            <input type="checkbox" name="semanaIndisponivel[]" class="sem-checkbox" value="4"
            <?php if (in_array(4, $diasSemanaIndisponiveis)) echo 'checked'; ?>> Quinta
        </label>
        <label>
            <input type="checkbox" name="semanaIndisponivel[]" class="sem-checkbox" value="5"
            <?php if (in_array(5, $diasSemanaIndisponiveis)) echo 'checked'; ?>> Sexta
        </label>
        <label>
            <input type="checkbox" name="semanaIndisponivel[]" class="sem-checkbox" value="6"
            <?php if (in_array(6, $diasSemanaIndisponiveis)) echo 'checked'; ?>> Sábado
        </label>

        <h2>dias do mes indisponiveis</h2>
        
        <select name="mes" id="mesSelect">
            <option value="1">janeiro</option>
            <option value="2">fevereiro</option>
            <option value="3">março</option>
            <option value="4">abril</option>
            <option value="5">maio</option>
            <option value="6">junho</option>
            <option value="7">julho</option>
            <option value="8">agosto</option>
            <option value="9">setembro</option>
            <option value="10">outubro</option>
            <option value="11">novembro</option>
            <option value="12">dezembro</option>
        </select>

        <select name="ano" id="anoSelect">
            <option value="2025">2025</option>
            <option value="2026">2026</option>
            <option value="2027">2027</option>
            <option value="2028">2028</option>
            <option value="2029">2029</option>
        </select>

        <table id="dataDisponiveis">
            <tbody>
                <!--calendario gerado no js-->
            </tbody>
        </table>

        <h3 id="naoFuncionamento"><!--titulo da lista--></h3>

<ul>
<?php
// Buscar as datas
$result = $conn->query("SELECT data FROM dia_indisponivel ORDER BY data ASC");

while ($row = $result->fetch_assoc()) {
    $data = $row['data'];
    echo "<li>
            <form method='post' style='display:inline;'>
                <input type='date' value='$data' readonly>
                <button type='submit' name='remover_data' value='$data'>Remover</button>
            </form>
          </li>";
}
?>


<script>
    const servicosPost = <?= json_encode($servicos); ?>;
</script>

<script>
    const tiposPHP = <?= json_encode($tipos, JSON_HEX_TAG) ?>;
    const valoresPHP = <?= json_encode($valores, JSON_HEX_TAG) ?>;
    const qtdFuncsPHP = <?= json_encode($qtdFuncs, JSON_HEX_TAG) ?>;
    const funcionariosPHP = <?= json_encode($funcionarios, JSON_HEX_TAG) ?>;
</script>
</ul>


        <h2>horas</h2>
        <input type="time" name="Hinicio" value="<?= $horario['inicio'] ?>">
        <input type="time" name="Htermino" value="<?= $horario['fim'] ?>">
        <input type="time" name="duracaoServico" value="<?= $horario['duracao'] ?>">
        <input type="time" name="intervaloServico" value="<?= $horario['intervalo'] ?>">


        
        <button type="submit" name="salvar" id="salvar">Salvar</button>
    </form>

    <script src="../../javaScript/configuracao.js"></script>

    <?php

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['salvar'])) {
    $quantidadeServicos = intval($_POST['quantidadeServicos']);

    for ($i = 1; $i <= $quantidadeServicos; $i++) {
        $tipo = $_POST["tipo$i"] ?? null;
        $valor = isset($_POST["valor$i"]) ? floatval($_POST["valor$i"]) : null;
        $qtFuncionario = isset($_POST["qtFuncionario$i"]) ? intval($_POST["qtFuncionario$i"]) : 0;

        // Ignora se algum campo essencial estiver faltando
        if (empty($tipo) || $valor === null) {
            continue;
        }

        // Insere serviço
        $stmt = $conn->prepare("INSERT INTO servico (tipo_servico, valor, quantidade_de_funcionarios) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $tipo, $valor, $qtFuncionario);
        $stmt->execute();
        $servico_id = $stmt->insert_id;
        $stmt->close();

        // Insere funcionários
        for ($j = 1; $j <= $qtFuncionario; $j++) {
            $nomeFuncionario = $_POST["funcionario{$i}_{$j}"] ?? '';
            if (!empty($nomeFuncionario)) {
                $stmt = $conn->prepare("INSERT INTO funcionario (servico_id, nome) VALUES (?, ?)");
                $stmt->bind_param("is", $servico_id, $nomeFuncionario);
                $stmt->execute();
                $stmt->close();
            }
        }
    }


        // Horas
        $horaInicio = $_POST['Hinicio'] ?? null;
        $horaTermino = $_POST['Htermino'] ?? null;
        $duracao = $_POST['duracaoServico'] ?? null;
        $intervalo = $_POST['intervaloServico'] ?? null;

        if ($horaInicio && $horaTermino && $duracao && $intervalo) {
            $stmt = $conn->prepare("INSERT INTO horario_config (inicio_servico, termino_servico, duracao, intervalo) VALUES (?, ?, ?, ?)");
            
            if ($stmt === false) {
                die("Erro ao preparar statement: " . $conn->error);
            }

            $stmt->bind_param("ssss", $horaInicio, $horaTermino, $duracao, $intervalo);

            if (!$stmt->execute()) {
                die("Erro ao executar statement: " . $stmt->error);
            }

            $stmt->close();
        }


        // Limpar dados anteriores
        $conn->query("DELETE FROM mes_indisponivel");
        foreach ($mesesIndisponiveis as $mes) {
            $stmt = $conn->prepare("INSERT INTO mes_indisponivel (mes) VALUES (?)");
            $stmt->bind_param("i", $mes);
            $stmt->execute();
            $stmt->close();
        }

        $conn->query("DELETE FROM semana_indisponivel");
        foreach ($diasSemanaIndisponiveis as $diaSemana) {
            $stmt = $conn->prepare("INSERT INTO semana_indisponivel (dia_semana) VALUES (?)");
            $stmt->bind_param("i", $diaSemana);
            $stmt->execute();
            $stmt->close();
        }




        // Dias do mês indisponíveis

        $diasIndisponiveis = isset($_POST["diasIndisponiveis"]) ? $_POST["diasIndisponiveis"] : [];

        if (is_array($diasIndisponiveis)) {
            foreach ($diasIndisponiveis as $data) {
                // Verifica se a data já existe no banco
                $check = $conn->prepare("SELECT COUNT(*) FROM dia_indisponivel WHERE data = ?");
                $check->bind_param("s", $data);
                $check->execute();
                $check->bind_result($count);
                $check->fetch();
                $check->close();

                if ($count == 0) {
                    // Só insere se ainda não estiver no banco
                    $stmt = $conn->prepare("INSERT INTO dia_indisponivel (data) VALUES (?)");
                    $stmt->bind_param("s", $data);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        exit;

        if (isset($_POST['data'])) {
        require 'conexao.php'; // ou onde está sua conexão

        $data = $_POST['data'];
        $stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ?");
        $stmt->bind_param("s", $data);

        if ($stmt->execute()) {
            echo "OK";
        } else {
            echo "Erro ao deletar: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Data não recebida";
    }



        $mes = [
            "janeiro", "fevereiro", "março", "abril", "maio", "junho",
            "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"
        ];


        // // Consulta 1
        $sql = "SELECT dia_semana FROM semana_indisponivel"; 
        $result = $conn->query($sql);

        if (!$result) {
            die("Erro na query: " . $conn->error . " | SQL: " . $sql);
        }
        while ($row = $result->fetch_assoc()) {
            $mesesIndisponiveis[] = $row['dia_semana']; // CORRIGIDO
        }

        // Consulta 2
        $result = $conn->query("SELECT * FROM horario_config LIMIT 1");

        if (!$result) {
            die("Erro na consulta: " . $conn->error); // Mostra o erro do MySQL
        }

        $horario = $result->fetch_assoc();


        // Consulta 3

        $result = $conn->query("SELECT data FROM dia_indisponivel LIMIT 1");

        if (!$result) {
            die("Erro na consulta: " . $conn->error); // Mostra o erro do MySQL
        }

        $horario = $result->fetch_assoc();




        // Consulta 4
        $result = $conn->query("SELECT * FROM horario_config LIMIT 1");
        if (!$result) {
            die("Erro na consulta: " . $conn->error);
        }
        $horario = $result->fetch_assoc();

        
}

    ?>

</body>
</html>