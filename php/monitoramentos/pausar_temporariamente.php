<?php
include '../login_empresa/get_id.php';
include '../conexao.php';

// Verifica se o ID está realmente definido
if (!isset($empresa_id)) {
    http_response_code(403);
    exit("Acesso negado.");
}

// Verifica se a empresa está pausada
$stmt = $conn->prepare("SELECT status FROM cadastro_empresa WHERE id = ?");
$stmt->bind_param("i", $empresa_id);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

if ($status === 'pausado') {
    $html = <<<HTML
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Sistema Pausado</title>
        <style>
            body {
                font-family: sans-serif;
                background: #f5f5f5;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100vh;
            }
            .box {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 10px #aaa;
                text-align: center;
            }
            .box h1 {
                color: red;
            }
            .box a {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background: #3498db;
                color: white;
                border-radius: 5px;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class='box'>
            <h1>⚠ Sistema Pausado</h1>
            <p>Esta empresa está com o sistema temporariamente suspenso.</p>
            <a href='../tabela_clientes/clientes_agendados.php'>Ver Agenda</a>
            <a href='../settings/configuracao.php'>Configurações</a>
        </div>
    </body>
    </html>
    HTML;

    echo $html;
    exit;
}
?>
