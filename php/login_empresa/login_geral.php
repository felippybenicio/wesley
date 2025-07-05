<?php

// Ativar erros para depuração durante desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './get_id.php';
include '../conexao.php';

// Captura o empresa_id de forma segura e unificada: POST > SESSION > GET
$empresa_id = intval($_POST['empresa_id'] ?? ($_SESSION['empresa_id'] ?? ($_GET['empresa_id'] ?? 0)));

// Se for requisição POST (AJAX) e o ID for inválido, retorne erro em JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $empresa_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'msg' => 'Empresa inválida.']);
    exit;
}

// Se for acesso normal (GET) e empresa_id inválido, morre com mensagem simples
if ($empresa_id <= 0) {
    die("Empresa inválida.");
}



// Função para buscar senha hash no banco para a empresa
function getSenhaHash($conn, $empresa_id) {
    $stmt = $conn->prepare("SELECT senha_geral FROM cadastro_empresa WHERE id = ?");
    if (!$stmt) {
    die("Erro no prepare: " . $conn->error);
}
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['senha_geral'];
    }
    return null;
}

// Função para cadastrar senha (hash) no banco
function setSenhaHash($conn, $empresa_id, $hash) {
    $stmt = $conn->prepare("UPDATE cadastro_empresa SET senha_geral = ? WHERE id = ?");
    $stmt->bind_param("si", $hash, $empresa_id);
    return $stmt->execute();
}


// Processar requisição AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $senha = trim($_POST['senha'] ?? '');

    if ($empresa_id <= 0) {
        echo json_encode(['success' => false, 'msg' => 'Empresa inválida.']);
        exit;
    }

    if (empty($senha) || strlen($senha) < 4) {
        echo json_encode(['success' => false, 'msg' => 'Senha deve ter pelo menos 4 caracteres.']);
        exit;
    }

    if ($action === 'cadastrar') {
        // Verifica se já tem senha cadastrada
        $hashAtual = getSenhaHash($conn, $empresa_id);
        if ($hashAtual) {
            echo json_encode(['success' => false, 'msg' => 'Senha já cadastrada. Faça login.']);
            exit;
        }
        // Cria hash e salva
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $ok = setSenhaHash($conn, $empresa_id, $hash);
        if ($ok) {
            echo json_encode(['success' => true, 'msg' => 'Senha cadastrada com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Erro ao cadastrar senha no banco.']);
        }
        exit;
    }

    if ($action === 'login') {
        $hash = getSenhaHash($conn, $empresa_id);
        if (!$hash) {
            echo json_encode(['success' => false, 'msg' => 'Nenhuma senha cadastrada. Cadastre primeiro.']);
            exit;
        }
        if (password_verify($senha, $hash)) {
            $_SESSION['empresa_logada'] = $empresa_id; // pode usar para controlar acesso depois
            echo json_encode(['success' => true, 'msg' => 'Login efetuado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Senha incorreta.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'msg' => 'Ação inválida.']);
    exit;
}

$senhaHash = getSenhaHash($conn, $empresa_id);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Senha Configuração - Empresa #<?php echo $empresa_id; ?></title>
<style>
  body { font-family: Arial, sans-serif; background:#f0f2f5; display:flex; height:100vh; justify-content:center; align-items:center; margin:0;}
  .container {background:white; padding:25px 30px; border-radius:8px; box-shadow:0 0 8px rgba(0,0,0,0.1); width:320px;}
  h2 {margin-bottom:20px; text-align:center;}
  input[type="password"] {width:100%; padding:10px 8px; margin:8px 0 16px 0; box-sizing:border-box; font-size:16px;}
  button {width:100%; padding:12px 0; font-size:16px; background:#3b82f6; border:none; color:white; border-radius:4px; cursor:pointer;}
  button:hover {background:#2563eb;}
  .error {color:red; margin-bottom:10px; font-size:14px; text-align:center;}
  .success {color:green; margin-bottom:10px; font-size:14px; text-align:center;}
</style>
</head>
<body>
  <div class="container" id="container"></div>

<script>
  const container = document.getElementById('container');
  const senhaCadastrada = <?php echo ($senhaHash ? 'true' : 'false'); ?>;
  const empresaId = <?php echo $empresa_id; ?>;

  function showSetPasswordForm() {
    container.innerHTML = `
      <h2>Cadastrar Senha</h2>
      <div id="message" class="error"></div>
      <input type="password" id="password1" placeholder="Digite a senha" autocomplete="new-password" />
      <input type="password" id="password2" placeholder="Confirme a senha" autocomplete="new-password" />
      <button id="btnSet">Cadastrar</button>
    `;

    document.getElementById('btnSet').onclick = () => {
      const p1 = document.getElementById('password1').value.trim();
      const p2 = document.getElementById('password2').value.trim();
      const message = document.getElementById('message');

      if (!p1 || !p2) {
        message.textContent = "Preencha ambos os campos.";
        return;
      }
      if (p1 !== p2) {
        message.textContent = "As senhas não coincidem.";
        return;
      }
      if (p1.length < 4) {
        message.textContent = "A senha deve ter pelo menos 4 caracteres.";
        return;
      }

      fetch(window.location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          action: 'cadastrar',
          senha: p1,
          empresa_id: empresaId
        })
      })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
            message.className = 'success';
            message.textContent = data.msg;
            setTimeout(() => {
                window.location.href = '../settings/configuracao.php';
            }, 1500);
            } else {
            message.className = 'error';
            message.textContent = data.msg;
            }
        })
        .catch(err => {
        console.error("Erro ao comunicar com o servidor:", err);
        message.className = 'error';
        message.textContent = "Erro ao comunicar com o servidor.";
        });
    }
  }

  function showLoginForm() {
    container.innerHTML = `
      <h2>Entrar com a Senha</h2>
      <div id="message" class="error"></div>
      <input type="password" id="passwordLogin" placeholder="Digite a senha" autocomplete="current-password" />
      <button id="btnLogin">Entrar</button>
    `;

    document.getElementById('btnLogin').onclick = () => {
        const senhaDigitada = document.getElementById('passwordLogin').value.trim();
        const message = document.getElementById('message');

        if (!senhaDigitada) {
            message.textContent = "Digite a senha.";
            return;
        }

        fetch(window.location.href, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
            action: 'login',
            senha: senhaDigitada,
            empresa_id: empresaId
            })
        })
      .then(res => res.json())
        .then(data => {
        if (data.success) {
            message.className = 'success';
            message.textContent = data.msg;
            setTimeout(() => {
            window.location.href = '../settings/configuracao.php';
            }, 500);
        } else {
            message.className = 'error';
            message.textContent = data.msg;
        }
        })
      .catch(() => {
        message.className = 'error';
        message.textContent = "Erro ao comunicar com servidor.";
      });
    }
  }

  if (senhaCadastrada) {
    showLoginForm();
  } else {
    showSetPasswordForm();
  }
</script>
</body>
</html>
