<?php
session_start();
$ref = $_GET['ref'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>finalização</title>
</head>
<body>
    <h1>Finalize seu cadastro</h1>
    <p>Clique para confirmar seu pagamento e finalizar o cadastro.</p>
    <form action="../../php/login_empresa/confirmar_finalizacao.php" method="POST">
        <input type="hidden" name="ref" value="<?php echo htmlspecialchars($ref); ?>">
        <button type="submit">Finalizar</button>
    </form>
</body>
</html>
