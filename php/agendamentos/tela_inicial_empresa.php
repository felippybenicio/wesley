<?php
include '../login_empresa/get_id.php';
include '../conexao.php';

if (!isset($_SESSION['empresa_id'])) {
    // Se não estiver logado, redireciona para login
    header("Location: ../../pages/login_empresa/tela_login.php");
    exit();
}

$empresa_id = $_SESSION['empresa_id'];


$stmt = $conn->prepare("SELECT * FROM servico WHERE empresa_id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
$configs = $result->fetch_all(MYSQLI_ASSOC);

// Exemplo para processar os serviços, corrigindo seu código:
function tempoParaMinutos($tempo) {
    if (strpos($tempo, ':') !== false) {
        list($h, $m) = explode(':', $tempo);
        return ((int)$h) * 60 + ((int)$m);
    }
    return (int)$tempo;
}

$tempoentrecessao = [];
foreach ($configs as $config) {
    $duracao = tempoParaMinutos($config['duracao_servico']);
    $intervalo = tempoParaMinutos($config['intervalo_entre_servico']);
    $idServico = $config['id']; // ou o campo correto

    $tempoentrecessao[$idServico] = $duracao + $intervalo;
}




// Pega horários
$stmt = $conn->prepare("SELECT * FROM horario_config WHERE empresa_id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
$horarios = $result->fetch_all(MYSQLI_ASSOC);


// Função para converter hora em minutos — OK
function timeToMinutes($timeStr) {
    list($h, $m, $s) = explode(':', $timeStr);
    return ($h * 60) + $m;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento</title>
    <link rel="stylesheet" href="estilo/style.css">

</head>
<body>
    <a href="../settings/configuracao.php">configuração</a>
    <h1>ola seja bem vindo</h1>
    <p>como podemos ajudar?</p>

    <main id="form">
        <form action="agendamento.php" method="POST">
            <section id="pessoal">
                <h2>Nos informe algumas informações pessoais</h2>
               
                <div>
                    <label for="nome">Nome: </label>
                    <input type="text" name="nome" id="nome">
                </div>
                <div>
                    <label for="sobrenome">Sobrenome: </label>
                    <input type="text" name="sobrenome" id="sobrenome">
                </div>
                <div>
                    <label for="nascimento">Data de nascimento: </label>
                    <input type="date" name="nascimento" id="nascimento">
                </div>
                <div>
                    <label for="email">E-mail: </label>
                    <input type="email" name="email" id="email">
                </div>
                <div>
                    <label for="cpf">CPF: </label>
                    <input type="text" name="cpf" id="cpf">
                </div>
                <div>
                    <label for="cll">Celular: </label>
                    <input type="tel" name="cll" id="cll">
                </div>
            </section>
            <section id="tipoServico">
                <h2>tipos de serviçoes que deseja</h2>
                <div>
                    <label for="servico">qual tipo de serviço voce gostaria</label>
                    <select name="servico" id="servico">
                        <?php
                            $i = 1;
                            if (isset($configs) && is_array($configs)) {
                                foreach ($configs as $config) {
                                    $id = $config['id'];

                                    $servico_name = htmlspecialchars($config['tipo_servico']);
                                    $valor_formatado = htmlspecialchars(number_format($config['valor'], 2, ',', '.'));
                                    $duracao_minutos = timeToMinutes($config['duracao_servico']);
                                    $intervalo_minutos = timeToMinutes($config['intervalo_entre_servico']);
                                    echo "<option value='$id' data-duracao='{$duracao_minutos}' data-intervalo='{$intervalo_minutos}'>
                                            {$servico_name} - R$$valor_formatado
                                        </option>";
                                    $i++;
                                }
                            } else {
                                echo "<option value=''>Nenhum serviço disponível</option>";
                            }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="duracao">quantas cessão que deseja: </label>
                    <select name="duracao" id="duracao">

                        <?php
                            for ($i = 1; $i <= 3; $i++) {
                                $cessao = "$i";
                                echo "<option value='$cessao'>$i cessão</option>";
                            }


                        ?>
                    </select>
                </div>
                <div>
                    <label for="dia">Para qual dia gostaria de agendar? </label>
                    <input type="date" id="dataSelecionada" name="dia" readonly placeholder="__/__/__">

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

                    <table id="dataDisponiveis">
                        <p>dias disponiveis neste mes de <strong class="mes">...</strong></p>
                        <tbody>
                            </tbody>
                    </table>
                </div>
                <div>
                    <label for="hora">Qual horario melhor para você? </label>
                    <input type="time" name="hora" id="hora" readonly>
                    <table id="horarios">
                        <thead>
                            <th>horas disponiveis</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <p id="tempoTotal">Tempo entre serviços: -- minutos</p>
                </div>
                
                <button type="submit">agendar</button>
            </section>
        </form>
    </main>
    
    
    <script>
        const globalHoraInicio = "<?= $globalHoraInicio ?>";
        const globalHoraTermino = "<?= $globalHoraTermino ?>";
        const horariosSalvos = <?php echo json_encode($horarios); ?>;
        
    </script>
    <script>
        const intervaloEntreHorarios = <?php echo json_encode($tempoentrecessao); ?>;
        console.log("Intervalo:", intervaloEntreHorarios); // Deve aparecer [90], [60], etc.

    </script>
     <script>
        const cessao = <?php echo json_encode($cessao); ?>
    </script>
    <script src="../../javaScript/tela_pricipal.js"></script>
</body>
</html>