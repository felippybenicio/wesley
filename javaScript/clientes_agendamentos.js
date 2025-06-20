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
    const todasTabelas = document.querySelectorAll('body > table'); // cada tabela é um cliente
    todasTabelas.forEach(tabela => {
        let mostrarCliente = false;
        const linhas = tabela.querySelectorAll('tr');
        
        linhas.forEach((linha, i) => {
            const colunas = linha.querySelectorAll('td');

            // Detectar linhas de agendamento (devem ter status na coluna 5)
            if (colunas.length >= 6) {
                const statusPagamento = tabela.querySelector("td:nth-child(9)")?.textContent?.toLowerCase() || "";
                const textoStatus = colunas[5]?.childNodes[0]?.textContent || "";
                const statusAtendimento = normalizarTexto(textoStatus);

                let mostrar = false;

                switch (filtro) {
                    case 'todos':
                        mostrar = true;
                        break;
                    case 'pagos':
                        mostrar = statusPagamento === 'pago';
                        break;
                    case 'nao-pagos':
                        mostrar = statusPagamento !== 'pago';
                        break;
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

                if (mostrar) {
                    mostrarCliente = true;
                }
            }
        });

        tabela.style.display = mostrarCliente ? '' : 'none';
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



