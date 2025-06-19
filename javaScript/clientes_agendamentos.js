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
    const linhas = Array.from(document.querySelectorAll('table tbody tr'));

    const clientesMap = {};
    let clienteAtualId = null;

    linhas.forEach(linha => {
        if (linha.classList.contains('cabecalho-cliente') || linha.classList.contains('cabecalho-agendamento')) {
            return;
        }

        const colunas = linha.querySelectorAll('td');

        if (colunas.length === 10) {
            clienteAtualId = colunas[0].textContent.trim();
            if (!clientesMap[clienteAtualId]) {
                clientesMap[clienteAtualId] = {
                    clienteLinha: linha,
                    agendamentoLinhas: []
                };
            }
        } else {
            if (clienteAtualId && clientesMap[clienteAtualId]) {
                clientesMap[clienteAtualId].agendamentoLinhas.push(linha);
            }
        }
    });

    Object.entries(clientesMap).forEach(([clienteId, dadosCliente]) => {
        const { clienteLinha, agendamentoLinhas } = dadosCliente;
        const colunasCliente = clienteLinha.querySelectorAll('td');
        const statusPagamento = colunasCliente[8].textContent.trim().toLowerCase();

        let algumAgendamentoVisivel = false;

        agendamentoLinhas.forEach(linhaAg => {
            const colunasAg = linhaAg.querySelectorAll('td');
            let statusAtendimento = normalizarTexto(colunasAg[5]?.textContent || '');

            let mostrar = false;

            switch(filtro) {
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
                case 'em-espera':
                    mostrar = statusAtendimento === 'em espera';
                    break;
            }

            linhaAg.style.display = mostrar ? '' : 'none';

            if (mostrar) {
                algumAgendamentoVisivel = true;
            }
        });

        const displayCliente = algumAgendamentoVisivel ? '' : 'none';
        clienteLinha.style.display = displayCliente;

        const cabecalhoCliente = document.getElementById('cabecalho-cliente-' + clienteId);
        const cabecalhoAgendamento = document.getElementById('cabecalho-agendamento-' + clienteId);

        if (cabecalhoCliente) cabecalhoCliente.style.display = displayCliente;
        if (cabecalhoAgendamento) cabecalhoAgendamento.style.display = displayCliente;
    });
});






function executarPesquisa() {
    const termo = document.getElementById('pesquisa-geral').value.trim().toLowerCase();
    const linhas = Array.from(document.querySelectorAll('table tbody tr'));
    
    // Agrupar linhas por cliente
    const clientesMap = {};
    let clienteAtualId = null;

    linhas.forEach(linha => {
        if (linha.classList.contains('cabecalho-cliente') || linha.classList.contains('cabecalho-agendamento')) {
            return;
        }

        const colunas = linha.querySelectorAll('td');

        if (colunas.length === 10) {
            clienteAtualId = colunas[0].textContent.trim();
            if (!clientesMap[clienteAtualId]) {
                clientesMap[clienteAtualId] = {
                    clienteLinha: linha,
                    agendamentoLinhas: [],
                    achouNoCliente: false,
                    achouNoAgendamento: false
                };
            }
            
            const textoCliente = linha.textContent.toLowerCase();
            if (textoCliente.includes(termo)) {
                clientesMap[clienteAtualId].achouNoCliente = true;
            }

        } else {
            if (clienteAtualId && clientesMap[clienteAtualId]) {
                clientesMap[clienteAtualId].agendamentoLinhas.push(linha);
                const textoAgendamento = linha.textContent.toLowerCase();
                if (textoAgendamento.includes(termo)) {
                    clientesMap[clienteAtualId].achouNoAgendamento = true;
                }
            }
        }
    });

    Object.entries(clientesMap).forEach(([clienteId, dadosCliente]) => {
        const { clienteLinha, agendamentoLinhas, achouNoCliente, achouNoAgendamento } = dadosCliente;
        const mostrar = termo === '' || achouNoCliente || achouNoAgendamento;

        clienteLinha.style.display = mostrar ? '' : 'none';

        agendamentoLinhas.forEach(linhaAg => {
            linhaAg.style.display = mostrar ? '' : 'none';
        });

        const cabecalhoCliente = document.getElementById('cabecalho-cliente-' + clienteId);
        const cabecalhoAgendamento = document.getElementById('cabecalho-agendamento-' + clienteId);

        if (cabecalhoCliente) cabecalhoCliente.style.display = mostrar ? '' : 'none';
        if (cabecalhoAgendamento) cabecalhoAgendamento.style.display = mostrar ? '' : 'none';
    });
}

// Aqui só adiciona o evento de clique no botão da lupa
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



