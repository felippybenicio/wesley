<?php

include '../conexao.php';
include '../login_empresa/get_id.php';

if (!$empresa_id) {
    echo "Empresa não autenticada.";
    exit;
}

$acao = $_POST['acao'] ?? '';
$cliente_id = $_POST['cliente_id'] ?? null;

switch ($acao) {
    case 'apagar_agendamentos_todos':
        $stmt = $conn->prepare("DELETE FROM agendamento WHERE empresa_id = ?");
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();

        // Depois apagar os clientes da empresa
        $stmt = $conn->prepare("DELETE FROM clientes WHERE empresa_id = ?");
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM pagamento WHERE empresa_id = ?");
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        echo "✅ Todos os agendamentos foram apagados.";
        break;

    case 'limpar_dados_agendamentos':
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // 1. Buscar pagamentos que estão pagos (candidatos à exclusão)
        $stmt = $conn->prepare("
            SELECT id 
            FROM pagamento 
            WHERE empresa_id = ? AND status_pagamento <> 'pendente'
        ");
        if (!$stmt) {
            die("Erro ao buscar pagamentos: " . $conn->error);
        }
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $pagamentosCandidatos = [];
        while ($row = $result->fetch_assoc()) {
            $pagamentosCandidatos[] = $row['id'];
        }

        $pagamentosParaApagar = [];
        $agendamentosParaApagar = [];

        // 2. Verificar se TODOS os agendamentos vinculados estão atendidos
        foreach ($pagamentosCandidatos as $pagamentoId) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS total,
                    SUM(CASE WHEN ja_atendido IS NOT NULL THEN 1 ELSE 0 END) AS atendidos
                FROM agendamento
                WHERE pagamento_id = ? AND empresa_id = ?
            ");
            if (!$stmt) {
                die("Erro ao contar agendamentos: " . $conn->error);
            }
            $stmt->bind_param("ii", $pagamentoId, $empresa_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row && $row['total'] > 0 && $row['total'] == $row['atendidos']) {
                // TODOS os agendamentos foram atendidos — podemos apagar
                $pagamentosParaApagar[] = $pagamentoId;

                // Buscar IDs desses agendamentos
                $stmt2 = $conn->prepare("
                    SELECT id FROM agendamento 
                    WHERE pagamento_id = ? AND empresa_id = ?
                ");
                $stmt2->bind_param("ii", $pagamentoId, $empresa_id);
                $stmt2->execute();
                $res2 = $stmt2->get_result();
                while ($ag = $res2->fetch_assoc()) {
                    $agendamentosParaApagar[] = $ag['id'];
                }
            }
        }

        // 3. Apagar agendamentos
        if (!empty($agendamentosParaApagar)) {
            $idsAg = implode(',', $agendamentosParaApagar);
            $sqlDelAg = "DELETE FROM agendamento WHERE id IN ($idsAg)";
            if (!$conn->query($sqlDelAg)) {
                die("Erro ao deletar agendamentos: " . $conn->error);
            }
        }

        // 4. Apagar pagamentos
        if (!empty($pagamentosParaApagar)) {
            $idsPag = implode(',', $pagamentosParaApagar);
            $sqlDelPag = "DELETE FROM pagamento WHERE id IN ($idsPag)";
            if (!$conn->query($sqlDelPag)) {
                die("Erro ao deletar pagamentos: " . $conn->error);
            }
        }

        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        echo "✅ Somente agendamentos totalmente atendidos e seus pagamentos foram apagados.";
        break;




    case 'apagar_agendamentos_cliente':
        if (!$cliente_id) {
            echo "ID do cliente não informado.";
            exit;
        }

        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // 1. Buscar pagamentos do cliente que estão pagos
        $stmt = $conn->prepare("
            SELECT id FROM pagamento 
            WHERE empresa_id = ? AND status_pagamento <> 'pendente'
        ");
        if (!$stmt) {
            die("Erro ao buscar pagamentos do cliente: " . $conn->error);
        }
        $stmt->bind_param("i", $empresa_id);
        $stmt->execute();
        $resPag = $stmt->get_result();

        $pagamentosCandidatos = [];
        while ($row = $resPag->fetch_assoc()) {
            $pagamentosCandidatos[] = (int) $row['id'];
        }

        $pagamentosParaApagar = [];
        $agendamentosParaApagar = [];

        // 2. Verifica se os pagamentos têm TODOS os agendamentos atendidos
        foreach ($pagamentosCandidatos as $pagamentoId) {
            // Verificar se pertence ao cliente
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS total,
                    SUM(CASE WHEN ja_atendido IS NOT NULL THEN 1 ELSE 0 END) AS atendidos
                FROM agendamento
                WHERE pagamento_id = ? AND empresa_id = ? AND cliente_id = ?
            ");
            $stmt->bind_param("iii", $pagamentoId, $empresa_id, $cliente_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();

            if ($row && $row['total'] > 0 && $row['total'] == $row['atendidos']) {
                $pagamentosParaApagar[] = $pagamentoId;

                // Coleta os agendamentos para apagar
                $stmt2 = $conn->prepare("
                    SELECT id FROM agendamento 
                    WHERE pagamento_id = ? AND empresa_id = ? AND cliente_id = ?
                ");
                $stmt2->bind_param("iii", $pagamentoId, $empresa_id, $cliente_id);
                $stmt2->execute();
                $res2 = $stmt2->get_result();
                while ($ag = $res2->fetch_assoc()) {
                    $agendamentosParaApagar[] = $ag['id'];
                }
            }
        }

        // 3. Incluir agendamentos SEM pagamento, mas que foram atendidos
        $stmt = $conn->prepare("
            SELECT id FROM agendamento 
            WHERE pagamento_id IS NULL 
            AND ja_atendido IS NOT NULL 
            AND cliente_id = ? AND empresa_id = ?
        ");
        $stmt->bind_param("ii", $cliente_id, $empresa_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $agendamentosParaApagar[] = $row['id'];
        }

        // 4. Apagar agendamentos permitidos
        if (!empty($agendamentosParaApagar)) {
            $idsAg = implode(',', $agendamentosParaApagar);
            $sqlDelAg = "DELETE FROM agendamento WHERE id IN ($idsAg)";
            if (!$conn->query($sqlDelAg)) {
                die("Erro ao deletar agendamentos: " . $conn->error);
            }
        }

        // 5. Apagar pagamentos permitidos
        if (!empty($pagamentosParaApagar)) {
            $idsPag = implode(',', array_unique($pagamentosParaApagar));
            $sqlDelPag = "DELETE FROM pagamento WHERE id IN ($idsPag)";
            if (!$conn->query($sqlDelPag)) {
                die("Erro ao deletar pagamentos: " . $conn->error);
            }
            echo "✅ Pagamentos deletados: $idsPag<br>";
        } else {
            echo "ℹ️ Nenhum pagamento pôde ser deletado.<br>";
        }

        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        echo "📅 Agendamentos atendidos do cliente apagados.";
        break;

    case 'apagar_cliente_completo':
            
        if (!$cliente_id) {
            echo "ID do cliente não informado.";
            exit;
        }

        // Desativar verificação de chave estrangeira (FK)
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // 1. Apagar todos os agendamentos do cliente
        $stmt = $conn->prepare("DELETE FROM agendamento WHERE cliente_id = ? AND empresa_id = ?");
        if (!$stmt) {
            die("Erro ao deletar agendamentos: " . $conn->error);
        }
        $stmt->bind_param("ii", $cliente_id, $empresa_id);
        $stmt->execute();

        // 2. Apagar todos os pagamentos ligados ao cliente
        // Dica: se não tem `cliente_id` na tabela `pagamento`, busque pelos `pagamento_id` dos agendamentos apagados antes.
        // Aqui, vamos garantir que todos pagamentos da empresa que estão ligados aos agendamentos dele sejam apagados:
        $stmt = $conn->prepare("
            DELETE FROM pagamento 
            WHERE empresa_id = ? AND id IN (
                SELECT DISTINCT pagamento_id FROM (
                    SELECT pagamento_id FROM agendamento WHERE cliente_id = ? AND empresa_id = ? AND pagamento_id IS NOT NULL
                ) AS tmp
            )
        ");
        if (!$stmt) {
            die("Erro ao deletar pagamentos: " . $conn->error);
        }
        $stmt->bind_param("iii", $empresa_id, $cliente_id, $empresa_id);
        $stmt->execute();

        // 3. Apagar o cliente
        $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ? AND empresa_id = ?");
        if (!$stmt) {
            die("Erro ao deletar cliente: " . $conn->error);
        }
        $stmt->bind_param("ii", $cliente_id, $empresa_id);
        $stmt->execute();

        // Reativar verificação de FK
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        echo "✅ Cliente, agendamentos e pagamentos apagados com sucesso (sem restrições).";
        break;

    }
?>