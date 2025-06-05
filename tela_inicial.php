<?php
    include 'php/conexao.php';

    $result = $conn->query("SELECT * FROM quantidade_servico WHERE id = 1");
        if ($result) {
            $row = $result->fetch_assoc();

        } else {
            echo "Erro na consulta: " . $conn->error;
        }
    $qtservico = $row['quantidade_de_servico'];

    $configs = $conn->query("SELECT * FROM servico")->fetch_all(MYSQLI_ASSOC);

    $i = 1;
    foreach ($configs as $config) {
        // Pega os valores da linha atual
        $id = $config['id'];
        $idSecundario = $config['id_secundario'];
        $servico = $config['tipo_servico'];
        $valor = $config['valor'];
        $qtFuncionarios = $config['quantidade_de_funcionarios'];
        $duracao = $config['duracao_servico'];
        $intervalo = $config['intervalo_entre_servico'];

        $tempoentrecessao[$i] = $duracao[$i] + $intervalo[$i];
    }

    $horarios = $conn->query("SELECT * FROM horario_config")->fetch_all(MYSQLI_ASSOC);
    $i = 1;
    

    
    
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
    <h1>ola seja bem vindo</h1>
    <p>como podemos ajudar?</p>

    <main id="form">
        <form action="php/agendamento.php" method="post">
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
                                echo "<option value='cessao $i'>$i cessão</option>";
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
                    <input type="time" name="hora" id="hora" readonly placeholder="__:__">
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
    <?php
        foreach ($horarios as $horario) {
            $diaSemana_ou_data = $horario['semana_ou_data'];
            $HoraInicio = $horario['inicio_servico'];
            $HoraTermino = $horario['termino_servico'];
            
            
                if ($diaSemana_ou_data === "0") {
                    $globalHoraInicio = $horario['inicio_servico'];
                    $globalHoraTermino = $horario['termino_servico'];
                    break; // já achou o que queria, pode parar
                }

            

        }


    ?>
    
    <script>
        const globalHoraInicio = "<?= $globalHoraInicio ?>";
        const globalHoraTermino = "<?= $globalHoraTermino ?>";
        const horariosSalvos = <?php echo json_encode($horarios); ?>;

    </script>
    <script src="javaScript/tela_pricipal.js"></script>
</body>
</html>