<?php
include '../login_empresa/get_id.php';
include '../conexao.php';
include '../monitoramentos/pausar_temporariamente.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['empresa_id'])) {
    header("Location: ../../pages/login_empresa/tela_login.php");
    exit();
}

$empresa_id = $_SESSION['empresa_id'];

// Geração de token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Verifica se a empresa existe
$stmt = $conn->prepare("SELECT id FROM cadastro_empresa WHERE id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION = [];
    session_destroy();
    header('Location: /sistema-agendamento/pages/login_empresa/tela_login.html?erro=empresa_excluida');
    exit;
}
$stmt->close();

// Consulta serviços
$stmt = $conn->prepare("SELECT * FROM servico WHERE empresa_id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
$configs = $result->fetch_all(MYSQLI_ASSOC);

// Conversores de tempo
function tempoParaMinutos($tempo) {
    if (strpos($tempo, ':') !== false) {
        list($h, $m) = explode(':', $tempo);
        return ((int)$h) * 60 + ((int)$m);
    }
    return (int)$tempo;
}

function timeToMinutes($timeStr) {
    list($h, $m, $s) = explode(':', $timeStr);
    return ($h * 60) + $m;
}

$tempoentrecessao = [];
foreach ($configs as $config) {
    $duracao = tempoParaMinutos($config['duracao_servico']);
    $intervalo = tempoParaMinutos($config['intervalo_entre_servico']);
    $idServico = $config['id'];
    $tempoentrecessao[$idServico] = $duracao + $intervalo;
}

// Consulta horários
$stmt = $conn->prepare("SELECT * FROM horario_config WHERE empresa_id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$result = $stmt->get_result();
$horarios = $result->fetch_all(MYSQLI_ASSOC);

// Consulta limite de agendamentos
$stmt = $conn->prepare("SELECT agendamentos_por_clientes FROM quantidade_servico WHERE empresa_id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$stmt->bind_result($agendamentosPorClientes);
$stmt->fetch();
$stmt->close();

// Valores padrão para variáveis JS (evita erro se não definidos)
$cessao = $cessao ?? [];
$globalHoraInicio = $globalHoraInicio ?? '';
$globalHoraTermino = $globalHoraTermino ?? '';

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento</title>
</head>
<body>
    <a href="..\login_empresa\login_geral.php">configuração</a>
    <a href="../tabela_clientes/clientes_agendados.php">agendamentos de clientes</a>
    <h1>ola seja bem vindo</h1>
    <p>como podemos ajudar?</p>

    <main id="form">
        <form action="agendamento.php" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <section id="pessoal">
                <h2>Nos informe algumas informações pessoais</h2>

                <div>
                    <label for="nome">Nome:</label>
                    <input type="text" name="nome" id="nome" maxlength="100" required>
                </div>
                <div>
                    <label for="sobrenome">Sobrenome:</label>
                    <input type="text" name="sobrenome" id="sobrenome" maxlength="100" required>
                </div>
                <div>
                    <label for="nascimento">Data de nascimento:</label>
                    <input type="date" name="nascimento" id="nascimento" required>
                </div>
                <div>
                    <label for="email">E-mail:</label>
                    <input type="email" name="email" id="email" maxlength="150" required>
                </div>
                <div>
                    <label for="cpf">CPF:</label>
                    <input type="text" name="cpf" id="cpf" maxlength="14" required pattern="\d{11}" inputmode="numeric">
                </div>
                <div>
                    <label for="cll">Celular:</label>
                    <input type="tel" name="cll" id="cll" maxlength="15" required>
                </div>
            </section>

            <section id="tipoServico">
                <h2>Tipos de serviços que deseja</h2>

                <div>
                    <label for="qtdagendamentos">Quantas sessões deseja:</label>
                    <input type="number" name="qtdagendamentos" id="qtdagendamentos"
                           value="1" min="1" max="<?= (int)$agendamentosPorClientes ?>" required>
                </div>

                <div name="servico" id="servico"></div>
                <div id="agendamentos-container"></div>

                <div>
                    <select name="mes" id="mesSelect" required>
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
                        <p>dias disponíveis neste mês de <strong class="mes">...</strong></p>
                        <tbody></tbody>
                    </table>
                </div>

                <div>
                    <table id="horarios">
                        <thead>
                            <th>horas disponíveis</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <button type="submit">Agendar</button>
            </section>
        </form>
    </main>

    <!-- Protegendo saída JSON no JavaScript -->
    <script>
        const globalHoraInicio = "<?= htmlspecialchars($globalHoraInicio) ?>";
        const globalHoraTermino = "<?= htmlspecialchars($globalHoraTermino) ?>";
        const horariosSalvos = JSON.parse('<?= json_encode($horarios, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>');
        const intervaloEntreHorarios = JSON.parse('<?= json_encode($tempoentrecessao, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>');
        const cessao = JSON.parse('<?= json_encode($cessao, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>');
        const configs = JSON.parse('<?= json_encode($configs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>');
    </script>

    <script src="../../javaScript/tela_pricipal.js"></script>
</body>
</html>
