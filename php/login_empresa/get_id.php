<?php
    session_start();

    if (!isset($_SESSION['empresa_id'])) {
        header("Location: ../login_empresa/login.php");
        exit;
    }

    $empresa_id = (int) $_SESSION['empresa_id'];
?>
