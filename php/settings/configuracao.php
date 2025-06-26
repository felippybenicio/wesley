<?php
    include '../login_empresa/get_id.php';
    include '../conexao.php';



// echo "ID da empresa logada: " . $empresa_id;
// exit;
   

    $servicosPost = [];

    $mesesIndisponiveis = $_POST['mesIndisponivel'] ?? [];
    $diasSemanaIndisponiveis = $_POST['semanaIndisponivel'] ?? [];
    $agendamentosPorClientes = $_POST['agendamentosPorClientes'] ?? 1;

    $servicoId = [];
    $idSecundario = [];
    $tipos = [];
    $valores = [];
    $qtFuncionarios = [];
    $funcionarios = [];
    $duracaoServico = [];
    $intervaloServico = [];

    $semana = [];
    $datasEspecificas = [];
    
    $quantidadeServicos = $_POST['quantidadeServicos'] ?? 1;
    $quantidadeServicos = max(1, min((int)$quantidadeServicos, 5));
    for ($i = 1; $i <= $quantidadeServicos; $i++) {
        $tipos[$i] = $_POST["tipo$i"] ?? '';
        $valores[$i] = $_POST["valor$i"] ?? '';
        $qtFuncionarios[$i] = $_POST["qtFuncionario$i"] ?? 1;

        for ($j = 1; $j <= $qtFuncionarios[$i]; $j++) {
            $funcionarios[$i][$j] = $_POST["funcionario{$i}_{$j}"] ?? '';
        }
    }


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['removerData'])) {
        $data = $_POST['removerData'];

        // $stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ?");
        // $stmt->bind_param("s", $data);
        $stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ? AND empresa_id = ?");
        $stmt->bind_param("si", $data, $empresa_id);

        $stmt->execute();
        $stmt->close();

        header("Location: configuracao.php");
        exit;
    }
    
// Verifica se já existe
$stmt = $conn->prepare("SELECT COUNT(*) FROM quantidade_servico WHERE empresa_id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    // Atualiza os dois campos
    $stmt = $conn->prepare("UPDATE quantidade_servico SET quantidade_de_servico = ?, agendamentos_por_clientes = ? WHERE empresa_id = ?");
    $stmt->bind_param("iii", $quantidadeServicos, $agendamentosPorClientes, $empresa_id);
} else {
    // Insere os dois campos
    $stmt = $conn->prepare("INSERT INTO quantidade_servico (empresa_id, quantidade_de_servico, agendamentos_por_clientes) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $empresa_id, $quantidadeServicos, $agendamentosPorClientes);
}
$stmt->execute();
$stmt->close();

    // Limpa as tabelas relacionadas a serviços
    $quantidadeServicos = $_POST['quantidadeServicos'] ?? 1;
$quantidadeServicos = max(1, min((int)$quantidadeServicos, 5));

$agendamentosPorClientes = $_POST['agendamentosPorClientes'] ?? 1;
$agendamentosPorClientes = max(1, min((int)$agendamentosPorClientes, 10));


for ($i = 1; $i <= $quantidadeServicos; $i++) {
    $id = $_POST["id$i"] ?? null;  // pode ser null
    $idSecundario = $i;
    $tipo = $_POST["tipo$i"] ?? '';
    $valor = $_POST["valor$i"] ?? 0;
    $qtFuncionarios = (int)($_POST["qtFuncionario$i"] ?? 1);
    $duracao = $_POST["duracaoServico$i"] ?? '';
    $intervalo = $_POST["intervaloServico$i"] ?? '';

    $servicoValido = false;

    if (!empty($id) && is_numeric($id)) {
        // Verifica se o serviço pertence a esta empresa
        $stmt = $conn->prepare("SELECT id FROM servico WHERE id = ? AND empresa_id = ?");
        $stmt->bind_param("ii", $id, $empresa_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $servicoValido = true;
        } else {
            $id = null;
            $servicoValido = false; 
        }

        $stmt->close();
    }

    if ($servicoValido) {
        // Faz UPDATE do serviço existente
        $stmt = $conn->prepare("UPDATE servico SET id_secundario=?, tipo_servico=?, valor=?, quantidade_de_funcionarios=?, duracao_servico=?, intervalo_entre_servico=? WHERE id=? AND empresa_id=?");
        $stmt->bind_param("isdissii", $idSecundario, $tipo, $valor, $qtFuncionarios, $duracao, $intervalo, $id, $empresa_id);
        $stmt->execute();
        $stmt->close();

        // Apaga funcionários antigos daquele serviço
        $stmtDel = $conn->prepare("DELETE FROM funcionario WHERE servico_id = ? AND empresa_id = ?");
        $stmtDel->bind_param("ii", $id, $empresa_id);
        $stmtDel->execute();
        $stmtDel->close();

        $servicoId = $id; // para inserir os funcionários abaixo
    } else {
        // Insere novo serviço
        $stmt = $conn->prepare("INSERT INTO servico (empresa_id, id_secundario, tipo_servico, valor, quantidade_de_funcionarios, duracao_servico, intervalo_entre_servico) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisdiss", $empresa_id, $idSecundario, $tipo, $valor, $qtFuncionarios, $duracao, $intervalo);
        $stmt->execute();
        $servicoId = $stmt->insert_id;
        $stmt->close();
    }

    // Insere funcionários para o serviço (novo ou atualizado)
    for ($j = 1; $j <= $qtFuncionarios; $j++) {
        $key = "funcionario{$i}_{$j}";
        if (!empty($_POST[$key])) {
            $nome = $_POST[$key];
            $stmtFunc = $conn->prepare("INSERT INTO funcionario (empresa_id, servico_id, nome) VALUES (?, ?, ?)");
            $stmtFunc->bind_param("iis", $empresa_id, $servicoId, $nome);
            $stmtFunc->execute();
            $stmtFunc->close();
        }
    }
}

//PARA DELETAR SERVIÇO  

// if ($conn->connect_error) {
//     die("Erro na conexão: " . $conn->connect_error);
// }

// $stmt = $conn->prepare("DELETE FROM servico WHERE id = ?");
// if (!$stmt) {
//     die("Erro ao preparar: " . $conn->error);
// }

// $stmt->bind_param("i", $id);

// if (!$stmt->execute()) {
//     die("Erro ao executar: " . $stmt->error);
// }

// echo "Serviço deletado com sucesso!";


    // Salva meses e dias da semana indisponíveis
    // $conn->query("DELETE FROM mes_indisponivel");
    // $conn->query("DELETE FROM semana_indisponivel");

    $stmt = $conn->prepare("DELETE FROM mes_indisponivel WHERE empresa_id = ?");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM semana_indisponivel WHERE empresa_id = ?");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $stmt->close();

    if (!empty($_POST['mesIndisponivel'])) {
    $stmtMes = $conn->prepare("INSERT INTO mes_indisponivel (empresa_id, mes) VALUES (?, ?)");
    foreach ($_POST['mesIndisponivel'] as $mes) {
        $mes = (int)$mes;
        $stmtMes->bind_param("ii", $empresa_id, $mes);
        $stmtMes->execute();
    }
    $stmtMes->close();
}

if (!empty($_POST['semanaIndisponivel'])) {
    $stmtSemana = $conn->prepare("INSERT INTO semana_indisponivel (empresa_id, dia_semana) VALUES (?, ?)");
    foreach ($_POST['semanaIndisponivel'] as $dia) {
        $dia = (int)$dia;
        $stmtSemana->bind_param("ii", $empresa_id, $dia);
        $stmtSemana->execute();
    }
    $stmtSemana->close();
}

    // Salva dias específicos indisponíveis
    if (!empty($_POST['diasIndisponiveis'])) {
    foreach ($_POST['diasIndisponiveis'] as $data) {
        $check = $conn->prepare("SELECT COUNT(*) FROM dia_indisponivel WHERE data = ? AND empresa_id = ?");
        $check->bind_param("si", $data, $empresa_id);

        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count == 0) {
            $stmt = $conn->prepare("INSERT INTO dia_indisponivel (empresa_id, data) VALUES (?, ?)");
            $stmt->bind_param("is", $empresa_id, $data);
            $stmt->execute();
            $stmt->close();
        }
    }
}


    $data = $_POST['dataParaRemover'] ?? null;

    if ($data) {
        $stmt = $conn->prepare("DELETE FROM dia_indisponivel WHERE data = ? AND empresa_id = ?");
        $stmt->bind_param("s", $data);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }


    // Salva os horários
    $conn->query("DELETE FROM horario_config WHERE empresa_id = $empresa_id");

    if (!empty($_POST['tipoDia']) && is_array($_POST['tipoDia'])) {
        foreach ($_POST['tipoDia'] as $i => $tipoDia) {
            $id = $_POST['id'][$i] ?? '';
            $inicio = $_POST['inicio'][$i] ?? '';
            $fim = $_POST['fim'][$i] ?? '';
            
            
            if (isset($tipoDia) && $inicio !== '' && $fim !== '') {
                $stmt = $conn->prepare("INSERT INTO horario_config (empresa_id, semana_ou_data, inicio_servico, termino_servico) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $empresa_id, $tipoDia, $inicio, $fim);
                $stmt->execute();
                $stmt->close();
            } 
        }
    }   

        // Tudo certo, agora pode redirecionar
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }



    $dadosSalvos = []; // Defina como array vazio se nada for buscado

    $res = $conn->query("SELECT * FROM servico WHERE empresa_id = $empresa_id");
    while ($row = $res->fetch_assoc()) {
        $id = $row['id'];
        $row['funcionarios'] = [];

        $resFunc = $conn->query("SELECT nome FROM funcionario WHERE servico_id = $id AND empresa_id = $empresa_id");
        while ($func = $resFunc->fetch_assoc()) {
            $row['funcionarios'][] = $func['nome'];
        }

        $dadosSalvos[] = $row;
    }



// CARREGAR os dados após salvar
$mesesIndisponiveis = [];
$res1 = $conn->query("SELECT mes FROM mes_indisponivel WHERE empresa_id = $empresa_id");
if ($res1) {
    while ($linha = $res1->fetch_assoc()) {
        $mesesIndisponiveis[] = (int)$linha['mes'];
    }
}


$diasSemanaIndisponiveis = [];
$res2 = $conn->query("SELECT dia_semana FROM semana_indisponivel WHERE empresa_id = $empresa_id");
if ($res2) {
    while ($linha = $res2->fetch_assoc()) {
        $diasSemanaIndisponiveis[] = (int)$linha['dia_semana'];
    }
}

    $sqlHoras = "SELECT * FROM horario_config WHERE empresa_id = $empresa_id";
    $result = $conn->query($sqlHoras);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $chave = $row['semana_ou_data'];
            $inicio = $row['inicio_servico'];
            $fim = $row['termino_servico'];

            if (is_numeric($chave)) {
                $semana[(int)$chave] = [
                    'inicio' => $inicio,
                    'fim' => $fim
                ];
            } else {
                $datasEspecificas[] = [
                    'data' => $chave,
                    'inicio' => $inicio,
                    'fim' => $fim
                ];
            }
        }
    }

    $quantidadeServicos = 1;
    $agendamentosPorClientes = 1;

    $stmt = $conn->prepare("SELECT quantidade_de_servico, agendamentos_por_clientes FROM quantidade_servico WHERE empresa_id = ?");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($quantidadeServicos, $agendamentosPorClientes);
        $stmt->fetch();
    }
    $stmt->close();
?>    

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>configuração</title>
</head>
<body>


    <a href="../agendamentos/tela_inicial_empresa.php">Voltar</a>



        <h1>Configuração</h1>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
            <h2>Serviços</h2>
            
           
            <label for="quantidadeServicos">Quantidade de serviços</label>
            <input type="number" name="quantidadeServicos" id="quantidadeServicos" value="<?= htmlspecialchars($quantidadeServicos) ?>" min="1" max="5">

            <div id="camposServicos"><!--no js--></div><br>
          
            <label for="agendamentosPorClientes">Máx. sessões por cliente:</label>
            <input type="number" name="agendamentosPorClientes" id="agendamentosPorClientes" value="<?= htmlspecialchars($agendamentosPorClientes) ?>" min="1" max="10">

        
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
// Pegue o ID da empresa logada (supondo que você armazene isso na sessão)
$empresaId = $_SESSION['empresa_id']; // ajuste conforme sua lógica

// Use uma query preparada para segurança
$stmt = $conn->prepare("SELECT data FROM dia_indisponivel WHERE empresa_id = ? ORDER BY data ASC");
$stmt->bind_param("i", $empresaId);
$stmt->execute();
$result = $stmt->get_result();

// Exiba apenas as datas da empresa logada
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

    <?php

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['salvar'])) {
        // // Salvar meses indisponíveis
        // $conn->query("DELETE FROM mes_indisponivel");
        // if (isset($_POST['mesIndisponivel'])) {
        //     foreach ($_POST['mesIndisponivel'] as $mes) {
        //         $mes = (int)$mes;
        //         $conn->query("INSERT INTO mes_indisponivel (mes) VALUES ($mes)");
        //     }
        // }

        // // Salvar dias da semana indisponíveis
        // $conn->query("DELETE FROM semana_indisponivel");
        // if (isset($_POST['semanaIndisponivel'])) {
        //     foreach ($_POST['semanaIndisponivel'] as $dia) {
        //         $dia = (int)$dia;
        //         $conn->query("INSERT INTO semana_indisponivel (dia_semana) VALUES ($dia)");
        //     }
        // }


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
    }

?>

<script>
    const dadosServicos = <?php echo json_encode(isset($dadosSalvos) ? $dadosSalvos : []); ?>;
</script>


<script src="/sistema-agendamento/javaScript/configuracao.js"></script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const qtdInput = document.getElementById("quantidadeServicos");

        if (dadosServicos.length > 0) {
            qtdInput.value = dadosServicos.length;
        }

        criarCampos(dadosServicos);

        qtdInput.addEventListener("input", () => {
            criarCampos(dadosServicos);
        });
    });
</script>
<script>
    // Salvar a posição do scroll antes de sair
    window.addEventListener("beforeunload", function () {
        localStorage.setItem("scrollPos", window.scrollY);
    });
</script>
<script>
    // Quando a página carregar, volta para a posição salva
    window.addEventListener("load", function () {
        const scrollPos = localStorage.getItem("scrollPos");
        if (scrollPos !== null) {
            window.scrollTo(0, parseInt(scrollPos));
        }
    });
</script>

</body>
</html>
