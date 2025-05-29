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
$quantidadeServicos = max(1, min((int)$quantidadeServicos, 5));

$servicosPost = [];

for ($i = 1; $i <= $quantidadeServicos; $i++) {
    $tipo = $_POST["tipo$i"] ?? '';
    $valor = $_POST["valor$i"] ?? '';
    $qtFuncionario = $_POST["qtFuncionario$i"] ?? 1;
    $duracaoServico = $_POST["duracaoServico$i"] ?? '';
    $intervaloServico = $_POST["intervaloServico$i"] ?? '';
    $tipoDia = $_POST['tipoDia'] ?? [];
    $inicio = $_POST['inicio'] ?? [];
    $fim = $_POST['fim'] ?? [];

    $funcionarios = [];
    for ($j = 1; $j <= 5; $j++) {
        $key = "funcionario{$i}_{$j}";
        if (isset($_POST[$key])) {
            $funcionarios[] = $_POST[$key];
        }
    }

    $servicosPost[$i - 1] = [ 
        'tipo' => $tipo,
        'valor' => $valor,
        'qtFuncionario' => (int)$qtFuncionario,
        'funcionarios' => $funcionarios,
        'duracao' => $duracaoServico,   
        'intervalo' => $intervaloServico   
    ];


}
    $mesesIndisponiveis = $_POST['mesIndisponivel'] ?? [];
    $diasSemanaIndisponiveis = $_POST['semanaIndisponivel'] ?? [];
    $quantidadeServicos = $_POST['quantidadeServicos'] ?? 1;

    $tipos = [];
    $valores = [];
    $qtFuncionarios = [];
    $funcionarios = [];
    $duracaoServico = [];
    $intervaloServico = [];

    $semana = [];
    $datasEspecificas = [];

    



    for ($i = 1; $i <= $quantidadeServicos; $i++) {
        $tipos[$i] = $_POST["tipo$i"] ?? '';
        $valores[$i] = $_POST["valor$i"] ?? '';
        $qtFuncionarios[$i] = $_POST["qtFuncionario$i"] ?? 1;

        for ($j = 1; $j <= $qtFuncionarios[$i]; $j++) {
            $funcionarios[$i][$j] = $_POST["funcionario{$i}_{$j}"] ?? '';
        }
    }

    
    

$conn = new mysqli('localhost', 'root', 'Duk23092020$$', 'consultorio');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['removerData'])) {
        $data = $_POST['removerData'];

        $stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ?");
        $stmt->bind_param("s", $data);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']); // Atualiza a página
        exit;
    }
    

    // Limpa as tabelas relacionadas a serviços
    $conn->query("DELETE FROM funcionario");
    $conn->query("DELETE FROM servico");

    // Salva os novos serviços
    $quantidadeServicos = $_POST['quantidadeServicos'] ?? 1;
    $quantidadeServicos = max(1, min((int)$quantidadeServicos, 5));

    for ($i = 1; $i <= $quantidadeServicos; $i++) {
        $tipo = $_POST["tipo$i"] ?? '';
        $valor = $_POST["valor$i"] ?? '';
        $qtFuncionario = $_POST["qtFuncionario$i"] ?? 1;
        $duracaoServico = $_POST["duracaoServico$i"] ?? '';
        $intervaloServico = $_POST["intervaloServico$i"] ?? '';

        $stmt = $conn->prepare("INSERT INTO servico (tipo_servico, valor, quantidade_de_funcionarios, duracao_servico, intervalo_entre_servico) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) die("Erro na preparação da query de serviço: " . $conn->error);

        $stmt->bind_param("ssiss", $tipo, $valor, $qtFuncionario, $duracaoServico, $intervaloServico);
        $stmt->execute();
        $servicoId = $conn->insert_id;
        $stmt->close();

        for ($j = 1; $j <= 5; $j++) {
            $key = "funcionario{$i}_{$j}";
            if (!empty($_POST[$key])) {
                $nomeFuncionario = $_POST[$key];
                $stmtFunc = $conn->prepare("INSERT INTO funcionario (servico_id, nome) VALUES (?, ?)");
                if (!$stmtFunc) die("Erro na preparação da query de funcionário: " . $conn->error);
                $stmtFunc->bind_param("is", $servicoId, $nomeFuncionario);
                $stmtFunc->execute();
                $stmtFunc->close();
            }
        }
    }

    

    // Salva meses e dias da semana indisponíveis
    $conn->query("DELETE FROM mes_indisponivel");
    $conn->query("DELETE FROM semana_indisponivel");

    if (!empty($_POST['mesIndisponivel'])) {
        foreach ($_POST['mesIndisponivel'] as $mes) {
            $conn->query("INSERT INTO mes_indisponivel (mes) VALUES (" . (int)$mes . ")");
        }
    }

    if (!empty($_POST['semanaIndisponivel'])) {
        foreach ($_POST['semanaIndisponivel'] as $dia) {
            $conn->query("INSERT INTO semana_indisponivel (dia_semana) VALUES (" . (int)$dia . ")");
        }
    }

    // Salva dias específicos indisponíveis
    if (!empty($_POST['diasIndisponiveis'])) {
        foreach ($_POST['diasIndisponiveis'] as $data) {
            $check = $conn->prepare("SELECT COUNT(*) FROM dia_indisponivel WHERE data = ?");
            $check->bind_param("s", $data);
            $check->execute();
            $check->bind_result($count);
            $check->fetch();
            $check->close();

            if ($count == 0) {
                $stmt = $conn->prepare("INSERT INTO dia_indisponivel (data) VALUES (?)");
                $stmt->bind_param("s", $data);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    $data = $_POST['dataParaRemover'] ?? null;

    if ($data) {
        $stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ?");
        $stmt->bind_param("s", $data);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }



       
    

    // Salva os horários
    $conn->query("DELETE FROM horario_config");

    if (!empty($_POST['tipoDia']) && is_array($_POST['tipoDia'])) {
        foreach ($_POST['tipoDia'] as $i => $tipoDia) {
            $inicio = $_POST['inicio'][$i] ?? '';
            $fim = $_POST['fim'][$i] ?? '';

            if (isset($tipoDia) && $inicio !== '' && $fim !== '') {
                $stmt = $conn->prepare("INSERT INTO horario_config (semana_ou_data, inicio_servico, termino_servico) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $tipoDia, $inicio, $fim);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    $conn->close();
        error_reporting(E_ALL);
    ini_set('display_errors', 1);

    var_dump($_POST);


        // Tudo certo, agora pode redirecionar
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }


$dadosServicos = [];

$sql = "SELECT * FROM servico";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $servicoId = $row['id'];

        $stmt = $conn->prepare("SELECT nome FROM funcionario WHERE servico_id = ?");
        $stmt->bind_param("i", $servicoId);
        $stmt->execute();
        $res = $stmt->get_result();

        $funcionarios = [];
        while ($func = $res->fetch_assoc()) {
            $funcionarios[] = $func['nome'];
        }

        $row['funcionarios'] = $funcionarios;
        $dadosServicos[] = $row;

        $stmt->close();
    }
}





// CARREGAR os dados após salvar
$mesesIndisponiveis = [];
$res1 = $conn->query("SELECT mes FROM mes_indisponivel");
if ($res1) {
    while ($linha = $res1->fetch_assoc()) {
        $mesesIndisponiveis[] = (int)$linha['mes'];
    }
}

$diasSemanaIndisponiveis = [];
$res2 = $conn->query("SELECT dia_semana FROM semana_indisponivel");
if ($res2) {
    while ($linha = $res2->fetch_assoc()) {
        $diasSemanaIndisponiveis[] = (int)$linha['dia_semana'];
    }
}

    

    $sqlHoras = "SELECT * FROM horario_config";
    $result = $conn->query($sqlHoras);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $chave = $row['semana_ou_data'];
            $inicio = $row['inicio_servico'];
            $fim = $row['termino_servico'];

            // Se for número (0 a 6), é dia da semana
            if (is_numeric($chave)) {
                $semana[(int)$chave] = [
                    'inicio' => $inicio,
                    'fim' => $fim
                ];
            } else {
                // senão, é data específica
                $datasEspecificas[] = [
                    'data' => $chave,
                    'inicio' => $inicio,
                    'fim' => $fim
                ];
            }
        }
    }
?>      
        <script>
            const servicosPost = <?= json_encode(array_values($servicosPost)) ?>;
        </script>

        <script>
            const tiposPHP = <?= json_encode($tipos, JSON_HEX_TAG) ?>;
            const valoresPHP = <?= json_encode($valores, JSON_HEX_TAG) ?>;
            const qtdFuncsPHP = <?= json_encode($qtdFuncs, JSON_HEX_TAG) ?>;
            const funcionariosPHP = <?= json_encode($funcionarios, JSON_HEX_TAG) ?>;
        </script>

        <h1>Configuração</h1>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
            <h2>Serviços</h2>
            
           
            <label for="quantidadeServicos">Quantidade de serviços</label>
            <input type="number" name="quantidadeServicos" id="quantidadeServicos" value="<?= htmlspecialchars($quantidadeServicos) ?>" min="1" max="5">

            <div id="camposServicos"><!--no js--></div>
          

        
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
                <input type='date' value='$data' readonly>
                <button type='submit' name='removerData' value='$data'>Remover</button>
              </li>";
    }
    ?>


</ul>


        <h2>horas</h2>
        <table>
            <thead>
            <tr>
                <th>Dias da Semana</th>
                <th>Início Expediente</th>
                <th>Término Expediente</th>
            </tr>
            </thead>
            <tbody>
            <?php
                $dias = ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"];
                foreach ($dias as $i => $dia) {
                    $inicio = $semana[$i]['inicio'] ?? '';
                    $fim = $semana[$i]['fim'] ?? '';
                    echo "<tr>
                            <th>$dia</th>
                            <td><input type='time' name='inicio[]' class='inicio' data-index='$i' value='$inicio'></td>
                            <td><input type='time' name='fim[]' class='fim' data-index='$i' value='$fim'>
                            <input type='hidden' name='tipoDia[]' value='$i'>
                            </td>
                        </tr>";
                }
            ?>
            </tbody>
                <tbody id="datas-especificas">
                    <?php
                        foreach ($datasEspecificas as $index => $data) {
                            echo "<tr>
                                <td><input type='date' name='tipoDia[]' value='" . htmlspecialchars($data['data']) . "'></td>
                                <td><input type='time' name='inicio[]' value='" . htmlspecialchars($data['inicio']) . "' class='inicio' data-index='$index'></td>
                                <td><input type='time' name='fim[]' value='" . htmlspecialchars($data['fim']) . "' class='fim' data-index='$index'></td>
                                <td style='background:red;cursor:pointer' onclick='this.parentNode.remove()'>X</td>
                            </tr>";
                        }
                    ?>

            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="button" id="addData">Adicionar Data Específica</button>
                    </td>
                </tr>
            </tfoot>
        </table>



        
        <button type="submit" name="salvar" id="salvar">Salvar</button>
    </form>
    <script>
        const dados = <?= json_encode($dadosServicos, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
        const qtd = dados.length;
    </script>
    

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
        $duracaoServico = $_POST["duracaoServico$i"] ?? null;
        $intervaloServico = $_POST["intervaloServico$i"] ?? null;


        // Ignora se algum campo essencial estiver faltando
        if (empty($tipo) || $valor === null) {
            continue;
        }

        // Insere serviço
        $stmt = $conn->prepare("INSERT INTO servico (tipo_servico, valor, quantidade_de_funcionarios, duracao_servico, intervalo_entre_servico) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiss", $tipo, $valor, $qtFuncionario, $duracaoServico, $intervaloServico);
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
    
        $tipos = $_POST['tipoDia'] ?? [];
        $inicios = $_POST['inicio'] ?? [];
        $fins = $_POST['fim'] ?? [];

        if (count($tipos) === count($inicios) && count($inicios) === count($fins)) {

            // Apaga os registros antigos
            $conn->query("DELETE FROM horario_config");

            $stmt = $conn->prepare("INSERT INTO horario_config (semana_ou_data, inicio_servico, termino_servico) VALUES (?, ?, ?)");

            foreach ($tipos as $i => $tipo) {
                $horaInicio = $inicios[$i];
                $horaFim = $fins[$i];

                if ($horaInicio && $horaFim) {
                    $stmt->bind_param("sss", $tipo, $horaInicio, $horaFim);
                    $stmt->execute();
                }
            }

            $stmt->close();
        } 
         
        



        //Limpar dados anteriores

        $conn->query("DELETE FROM funcionario");
        $conn->query("DELETE FROM servico");

        $quantidadeServicos = intval($_POST['quantidadeServicos']);

        for ($i = 1; $i <= $quantidadeServicos; $i++) {
            $tipo = $_POST["tipo$i"] ?? '';
            $valor = $_POST["valor$i"] ?? 0;
            $duracao = $_POST["duracaoServico$i"] ?? '';
            $intervalo = $_POST["intervaloServico$i"] ?? '';
            $qtFuncionario = intval($_POST["qtFuncionario$i"] ?? 1);

            // Inserir serviço
            $stmt = $conn->prepare("INSERT INTO servico (tipo_servico, valor, quantidade_de_funcionarios, duracao_servico, intervalo_entre_servico) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssi", $tipo, $valor, $qtFuncionario, $duracao, $intervalo);
            $stmt->execute();
            $servicoId = $stmt->insert_id;

            // Inserir funcionários do serviço
            for ($j = 1; $j <= $qtFuncionario; $j++) {
                $nomeFuncionario = $_POST["funcionario{$i}_{$j}"] ?? '';
                if (!empty($nomeFuncionario)) {
                    $stmtF = $conn->prepare("INSERT INTO funcionario(servico_id, nome) VALUES (?, ?)");
                    $stmtF->bind_param("is", $servicoId, $nomeFuncionario);
                    $stmtF->execute();
                }
            }
        }
if (!$stmt) {
    die("Erro no prepare: " . $conn->error);
}
if (!$stmt->execute()) {
    die("Erro ao executar: " . $stmt->error);
}


        // Salvar meses indisponíveis
        $conn->query("DELETE FROM mes_indisponivel");
        if (isset($_POST['mesIndisponivel'])) {
            foreach ($_POST['mesIndisponivel'] as $mes) {
                $mes = (int)$mes;
                $conn->query("INSERT INTO mes_indisponivel (mes) VALUES ($mes)");
            }
        }

        // Salvar dias da semana indisponíveis
        $conn->query("DELETE FROM semana_indisponivel");
        if (isset($_POST['semanaIndisponivel'])) {
            foreach ($_POST['semanaIndisponivel'] as $dia) {
                $dia = (int)$dia;
                $conn->query("INSERT INTO semana_indisponivel (dia_semana) VALUES ($dia)");
            }
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
        // Busca os horários salvos do banco
        $horarios = [];
        $result = $conn->query("SELECT * FROM horario_config");

        while ($row = $result->fetch_assoc()) {
            $horarios[] = $row;
        }



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

<script>
    window.dadosServicosSalvos = <?= json_encode($dadosServicos, JSON_UNESCAPED_UNICODE); ?>;
    console.log("DADOS DO PHP:", window.dadosServicosSalvos);
</script>


<script src="../../javaScript/configuracao.js"></script>
</body>
</html>
