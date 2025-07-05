<?php


include '../conexao.php';
include 'get_admin.php';

// seu código...
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

include '../conexao.php';

$nomeAdmin = $_SESSION['admin_nome'] ?? 'Administrador';
$empresas = $conn->query("SELECT * FROM cadastro_empresa");


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Empresas Cadastradas</title>
  <style>
    body { font-family: sans-serif; background: #f5f5f5; }
    .empresa { background: #fff; padding: 15px; margin: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
    .cabecalho { display: flex; justify-content: space-between; align-items: center; }
    .titulo { font-weight: bold; font-size: 18px; }
    .deletar, .pausar { background: red; color: #fff; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; margin-left: 10px; }
    .toggle { background: #3498db; color: #fff; border: none; margin: 5px 5px 0 0; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
    .conteudo { display: none; background: #f0f0f0; padding: 10px; border-radius: 5px; margin-top: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
    #senhaPrompt { display: none; position: fixed; top: 30%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.3); z-index: 1000; }
    #senhaPrompt input { width: 100%; padding: 8px; margin-top: 10px; }
    #senhaPrompt button { margin-top: 10px; }
  </style>
  <script src="../../javaScript/monitoramento.js"></script>
</head>
<body>

<h1>Bem-vindo, <?php echo htmlspecialchars($nomeAdmin); ?></h1>

<div id="senhaPrompt">
  <label>Digite a senha do monitoramento:</label>
  <input type="password" id="senhaInput">
  <button onclick="enviarSenha()">Confirmar</button>
  <button onclick="cancelarSenha()">Cancelar</button>
</div>

<h2>Empresas</h2>

<?php
while ($empresa = $empresas->fetch_assoc()) {
  $id = $empresa['id'];
  $status = $empresa['status'] ?? 'ativo';
  $qtd_servico = $conn->query("SELECT * FROM quantidade_servico WHERE empresa_id = $id")->fetch_assoc();
  $servicos = $conn->query("SELECT * FROM servico WHERE empresa_id = $id");
  $horarios = $conn->query("SELECT * FROM horario_config WHERE empresa_id = $id");
  $diasInd = $conn->query("SELECT data FROM dia_indisponivel WHERE empresa_id = $id");
  $semanaInd = $conn->query("SELECT dia_semana FROM semana_indisponivel WHERE empresa_id = $id");
  $mesInd = $conn->query("SELECT mes FROM mes_indisponivel WHERE empresa_id = $id");

  echo "<div class='empresa' data-empresa-id='{$id}'>";
  echo "<div class='cabecalho'><div class='titulo'>#{$empresa['id']} {$empresa['nome_empresa']}";
  if ($status === 'pausado') echo " <span style='color: red;'>(PAUSADA)</span>";
  echo "</div>";
  echo "<div><button class='deletar' onclick=\"solicitarSenha('deletar', {$id})\">Excluir</button>";
  if ($status === 'pausado') {
    echo "<button class='deletar' onclick=\"solicitarSenha('despausar', {$id})\">Despausar</button>";
  } else {
    echo "<button class='deletar' onclick=\"solicitarSenha('pausar', {$id})\">Pausar</button>";
  }
  echo "</div></div>";

  echo "<p><strong>Email:</strong> {$empresa['ramo_empresa']} | <strong>Email:</strong> {$empresa['email_profissional']} | <strong>Pagamento:</strong> {$empresa['tipo_pagamento']} | <strong>AcessKey:</strong> {$empresa['pix_acesskey']}</p>";
  echo "<p><strong>Endereço:</strong> {$empresa['endereco']}, {$empresa['cidade']} | <strong>Cadastrada em:</strong> {$empresa['dia_cadastrado']}</p>";
  echo "<p><strong>Quantidades de Serviços:</strong> {$qtd_servico['quantidade_de_servico']} | <strong>Agendamentos/Cliente:</strong> {$qtd_servico['agendamentos_por_clientes']}</p>";

  echo "<div><button class='toggle' onclick=\"toggleConteudo('ferias$id')\">Meses de Férias</button>";
  echo "<button class='toggle' onclick=\"toggleConteudo('servicos$id')\">Serviços</button>";
  echo "<button class='toggle' onclick=\"toggleConteudo('horarios$id')\">Horários</button>";
  echo "<button class='toggle' onclick=\"toggleConteudo('dias$id')\">Dias Específicos Bloqueados</button>";
  echo "<button class='toggle' onclick=\"toggleConteudo('semanas$id')\">Dias da Semana Bloqueados</button></div>";

  echo "<div class='conteudo' id='ferias$id'><ul>";
  while ($m = $mesInd->fetch_assoc()) echo "<li>Mês: {$m['mes']}</li>";
  echo "</ul></div>";

  echo "<div class='conteudo' id='servicos$id'><table><tr><th>ID</th><th>Tipo</th><th>Valor</th><th>Funcionários</th><th>Duração</th><th>Intervalo</th></tr>";
  while ($s = $servicos->fetch_assoc()) {
    $funcionarios = [];
    $resFunc = $conn->query("SELECT nome FROM funcionario WHERE empresa_id = $id AND servico_id = {$s['id']}");
    while ($f = $resFunc->fetch_assoc()) $funcionarios[] = $f['nome'];
    echo "<tr><td>{$s['id_secundario']}</td><td>{$s['tipo_servico']}</td><td>{$s['valor']}</td><td>" . implode(', ', $funcionarios) . "</td><td>" . substr($s['duracao_servico'], 0, 5) . "</td><td>" . substr($s['intervalo_entre_servico'], 0, 5) . "</td></tr>";
  }
  echo "</table></div>";

  echo "<div class='conteudo' id='horarios$id'><table><tr><th>Dia/Data</th><th>Início</th><th>Término</th></tr>";
  while ($h = $horarios->fetch_assoc()) {
    echo "<tr><td>{$h['semana_ou_data']}</td><td>" . substr($h['inicio_servico'], 0, 5) . "</td><td>" . substr($h['termino_servico'], 0, 5) . "</td></tr>";
  }
  echo "</table></div>";

  echo "<div class='conteudo' id='dias$id'><ul>";
  while ($d = $diasInd->fetch_assoc()) echo "<li>Data: " . date('d/m/Y', strtotime($d['data'])) . "</li>";
  echo "</ul></div>";

  echo "<div class='conteudo' id='semanas$id'><ul>";
  while ($s = $semanaInd->fetch_assoc()) echo "<li>Dia da Semana: {$s['dia_semana']}</li>";
  echo "</ul></div>";

  echo "</div>";
}
?>

</body>
</html>
