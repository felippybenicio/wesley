function normalizarTexto(texto) {
    return texto
        .toLowerCase()
        .replace(/✅|❌|⏳/g, '')
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
}

document.getElementById('filtro-agendamentos').addEventListener('change', function () {
    const filtro = this.value;
    const todasTabelas = document.querySelectorAll('body > table'); // uma tabela por cliente

    todasTabelas.forEach(tabela => {
        const linhaPagamento = tabela.querySelector('td[data-status]');
        const statusPagamento = linhaPagamento?.getAttribute('data-status')?.toLowerCase() || "";


        const linhas = tabela.querySelectorAll('tr');
        let mostrarTabela = false;

        if (filtro === 'todos') {
            // ✅ Mostrar tudo
            linhas.forEach(linha => linha.style.display = '');
            mostrarTabela = true;
        }

        // Filtros por pagamento
        else if (filtro === 'pagos') {
            mostrarTabela = statusPagamento === 'pago';
        } else if (filtro === 'nao-pagos') {
            mostrarTabela = statusPagamento === 'pendente';
        }

        // Filtros por atendimento (compareceu, não compareceu, agendado)
        else if (['compareceu', 'nao-compareceu', 'agendado'].includes(filtro)) {
            let temAgendamentoVisivel = false;

            linhas.forEach(linha => {
                const colunas = linha.querySelectorAll('td');

                if (colunas.length >= 6) {
                    const textoStatus = colunas[5]?.childNodes[0]?.textContent || "";
                    const statusAtendimento = normalizarTexto(textoStatus);

                    let mostrar = false;

                    switch (filtro) {
                        case 'compareceu':
                            mostrar = statusAtendimento === 'atendido';
                            break;
                        case 'nao-compareceu':
                            mostrar = statusAtendimento === 'nao atendido';
                            break;
                        case 'agendado':
                            mostrar = statusAtendimento === 'agendado';
                            break;
                    }

                    linha.style.display = mostrar ? '' : 'none';
                    if (mostrar) temAgendamentoVisivel = true;
                } else {
                    // Linhas que não são agendamento (cliente, pagamento, cabeçalho) sempre visíveis
                    linha.style.display = '';
                }
            });

            mostrarTabela = temAgendamentoVisivel;
        }

        tabela.style.display = mostrarTabela ? '' : 'none';
    });
});

function executarPesquisa() {
    const termo = document.getElementById('pesquisa-geral').value.trim().toLowerCase();
    const tabelasClientes = document.querySelectorAll('body > table'); // cada tabela é um cliente

    tabelasClientes.forEach(tabela => {
        let achouNoCliente = false;
        let achouNoAgendamento = false;

        const linhas = tabela.querySelectorAll('tr');

        linhas.forEach(linha => {
            if (linha.classList.contains('cabecalho-cliente') || linha.classList.contains('cabecalho-agendamento')) {
                return; // pula cabeçalhos
            }
            const texto = linha.textContent.toLowerCase();

            // Para a linha do cliente (com vários <td>), se conter termo
            if (!achouNoCliente && texto.includes(termo)) {
                achouNoCliente = true;
            }

            // Para linhas que tem menos td (agendamentos), se conter termo
            if (!achouNoAgendamento && texto.includes(termo)) {
                achouNoAgendamento = true;
            }
        });

        const mostrar = termo === '' || achouNoCliente || achouNoAgendamento;
        tabela.style.display = mostrar ? '' : 'none';
    });
}

// Evento no botão lupa
document.getElementById('botao-pesquisa').addEventListener('click', () => {
    executarPesquisa();
});



document.querySelectorAll('.botao-excluir').forEach(btn => {
  btn.addEventListener('click', () => {
    const acao = btn.dataset.acao;
    const confirmMsg = {
      apagar_agendamentos_todos: "Tem certeza que deseja apagar TODOS os agendamentos do sistema?",
      limpar_dados_agendamentos: "Tem certeza que deseja limpar os dados dos agendamentos (nome e CPF serão mantidos)?"
    }[acao] || "Confirmar ação?";

    if (confirm(confirmMsg)) {
        fetch('gerenciar_exclusoes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `acao=${acao}`
        })
        .then(res => res.text())
        .then(res => {
            alert(res);
            location.reload();
        })
        .catch(err => {
            console.error("Erro ao processar:", err); // Deve aparecer no console (F12)
            alert("Erro ao processar: " + err);
        });
    }
  });
});


  // Apagar cliente completo
  document.querySelectorAll('.botao-excluir-cliente').forEach(btn => {
    btn.addEventListener('click', () => {
      const clienteId = btn.dataset.clienteId;
      if (confirm(`Deseja realmente apagar o cliente ID ${clienteId} com todos os dados?`)) {
        fetch('gerenciar_exclusoes.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `acao=apagar_cliente_completo&cliente_id=${clienteId}`
        })
        .then(res => res.text())
        .then(res => {
          alert(res);
          location.reload();
        });
      }
    });
  });

  // Apagar só agendamentos do cliente
  document.querySelectorAll('.botao-excluir-agendamentos').forEach(btn => {
    btn.addEventListener('click', () => {
      const clienteId = btn.dataset.clienteId;
      if (confirm(`Deseja apagar apenas os agendamentos do cliente ID ${clienteId}?`)) {
        fetch('gerenciar_exclusoes.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `acao=apagar_agendamentos_cliente&cliente_id=${clienteId}`
        })
        .then(res => res.text())
        .then(res => {
          alert(res);
          location.reload();
        });
      }
    });
  });



























document.querySelectorAll(".btn-salvar-comentario").forEach(btn => {
    btn.addEventListener("click", function () {
        const id = this.getAttribute("data-id");
        const textarea = document.querySelector(`textarea[data-id='${id}']`);
        const motivo = textarea.value.trim();

        fetch("salvar_motivo.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "id=" + encodeURIComponent(id) + "&motivo=" + encodeURIComponent(motivo)
        })
        .then(res => res.text())
        .then(resp => {
            console.log("RESPOSTA DO PHP:", resp); // 👈 ADICIONE ISSO

            alert("Motivo salvo com sucesso");

            const tdMotivo = textarea.closest("td");
            tdMotivo.innerHTML = `<p>${motivo}</p>`;

            const botaoPresenca = document.querySelector(`button.btn-presenca[data-id='${id}']`);
            if (botaoPresenca) botaoPresenca.remove();

            // Atualiza o status na célula ao lado
            const tdStatus = tdMotivo.previousElementSibling;
            if (tdStatus) tdStatus.innerHTML = "❌ NÃO ATENDIDO";
        });

    });
});





document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('btn-presenca')) {
        const agendamentoId = e.target.getAttribute('data-id');

        fetch("atualizar_presenca.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "id=" + encodeURIComponent(agendamentoId) + "&presenca=sim"
        })
        .then(res => res.text())
        .then(resp => {
            e.target.parentElement.innerHTML = "✅ ATENDIDO";

            const motivoTd = document.querySelector(`.motivo-falta[data-id='${agendamentoId}']`);
            if (motivoTd) {
                motivoTd.innerHTML = "-";
                motivoTd.style.display = '';
            }
        });
    } 
});


function marcarComoPago(id, btn) {
    fetch('pagamento_manual.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(res => res.text())
    .then(res => {
        if (res === 'ok') {
            btn.parentElement.innerHTML = '<span>Pago</span>';
        } else {
            alert('Erro ao atualizar o pagamento: ' + res);
        }
    });
}




