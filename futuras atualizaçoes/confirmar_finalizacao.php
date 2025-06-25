<?php
session_start();

require_once __DIR__ . '../../vendor/autoload.php';
use MercadoPago\SDK;
use MercadoPago\Payment;

SDK::setAccessToken("SEU_ACCESS_TOKEN");

// Captura o payment_id da URL
$paymentId = $_GET['payment_id'] ?? null;

if (!$paymentId) {
    die("ID do pagamento não recebido.");
}

// Consulta o pagamento
$pagamento = Payment::find_by_id($paymentId);

if (!$pagamento || $pagamento->status !== 'approved') {
    die("Pagamento não encontrado ou não aprovado.");
}

// Recupera a referência externa usada para salvar os dados
$ref = $pagamento->external_reference;

// Agora busque os dados do banco pela referência
require_once '../conexao.php'; // ajustar para seu arquivo de conexão

$stmt = $conn->prepare("SELECT * FROM pagamentos_em_espera WHERE ref = ?");
$stmt->execute([$ref]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    die("Cadastro não encontrado com essa referência.");
}

// Agora insere no cadastro final, ou atualiza
$stmt = $conn->prepare("INSERT INTO cadastro_empresa (nome_empresa, ramo_empresa, email_profissional, senha_inicial, criado_em) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([
    $dados['empresa'],
    $dados['ramo'],
    $dados['email'],
    $dados['senha'] // já vem com hash
]);

// Limpa da tabela temporária
$conn->prepare("DELETE FROM pagamentos_em_espera WHERE ref = ?")->execute([$ref]);

echo "Cadastro finalizado com sucesso!";
?>
