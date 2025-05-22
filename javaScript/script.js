document.addEventListener("DOMContentLoaded", function () {


    const mesSelect = document.getElementById('mesSelect');
    const mes = document.querySelector('.mes');
    let agora = new Date();
    let nomesDosMeses = [
        "JANEIRO", "FEVEREIRO", "MARÇO", "ABRIL", "MAIO", "JUNHO",
        "JULHO", "AGOSTO", "SETEMBRO", "OUTUBRO", "NOVEMBRO", "DEZEMBRO"
    ];
    mes.textContent = nomesDosMeses[agora.getMonth()];

    let mesAtual = agora.getMonth() + 1;
    let anoAtual = agora.getFullYear();
    let totalHoras = 8;

    const dataInput = document.getElementById('dataSelecionada');
    const horaInput = document.getElementById('hora');

    const horarios = {};
    for (let i = 1; i <= totalHoras; i++) {
        const horaElemento = document.getElementById(`hora${i}`);
        if (horaElemento) {
            horarios[`hora${i}`] = horaElemento;
            horaElemento.addEventListener('click', function () {
                if (horaElemento.style.color === 'red') return;
                Object.values(horarios).forEach(h => {
                    if (h.style.color !== 'red') h.style.color = 'black';
                });
                horaElemento.style.color = 'blue';
                horaInput.value = horaElemento.textContent;
            });
        }
    }

    function marcarDiasCheios() {
        fetch(`php/dias_ocupados.php?mes=${mesAtual}&ano=${anoAtual}`)
            .then(res => res.json())
            .then(data => {
                const mensagem = document.getElementById('diacheio');
                const diasCheios = data.dias_cheios || [];
                diasCheios.forEach(dia => {
                    const diaTd = document.getElementById(`${dia}`);
                    if (diaTd) {
                        diaTd.style.color = 'red';
                        diaTd.style.pointerEvents = 'none';
                        mensagem.textContent = 'Dia cheio';
                    }
                });
            })
            .catch(e => console.error('Erro no marcarDiasCheios:', e));
    }

    function desativarDomingos() {
    console.log('desativarDomingos chamado');
        for (let i = 1; i <= 31; i++) {
            const diaTd = document.getElementById(`${i}`);
            if (!diaTd) {
                console.log(`Elemento com id ${i} não encontrado`);
                continue;
            }
            const data = new Date(anoAtual, mesAtual - 1, i);
            if (data.getMonth() !== mesAtual - 1) {
                console.log(`${i} não pertence ao mês atual`);
                continue;
            }
            if (data.getDay() === 0) {
                console.log(`Desativando domingo: dia ${i}`);
                diaTd.style.color = 'orange';
                diaTd.style.pointerEvents = 'none';
                diaTd.title = 'Domingo - indisponível';
            }
        }
    }




    function dataDoAgendamento() {
        const dias = {};
        for (let i = 1; i <= 31; i++) {
            const dia = document.getElementById(`${i}`);
            if (!dia) continue;

            dias[`dia${i}`] = dia;
            let diaFormatado = i.toString().padStart(2, '0');
            let mesFormatado = mesAtual.toString().padStart(2, '0');

            dia.addEventListener('click', function () {
                Object.values(dias).forEach(h => {
                    if (h.style.color !== 'red' && h.style.color !== 'orange') {
                        h.style.color = 'black';
                    }
                });

                if (dia.style.color !== 'red') dia.style.color = 'blue';
                const dataSelecionada = `${anoAtual}-${mesFormatado}-${diaFormatado}`;
                dataInput.value = dataSelecionada;
                buscarHorasOcupadas(dataSelecionada);
            });
        }
    }

    function buscarHorasOcupadas(dataSelecionada) {
        fetch(`php/dias_ocupados.php?data=${dataSelecionada}`)
            .then(response => response.json())
            .then(horasOcupadas => {
                for (let i = 1; i <= totalHoras; i++) {
                    const horaElemento = document.getElementById(`hora${i}`);
                    if (!horaElemento) continue;

                    horaElemento.style.color = 'black';
                    horaElemento.style.pointerEvents = 'auto';

                    if (horasOcupadas.includes(horaElemento.textContent + ":00")) {
                        horaElemento.style.color = 'red';
                        horaElemento.style.pointerEvents = 'none';
                    }
                }
            })
            .catch(err => {
                console.error('Erro ao interpretar JSON:', err);
            });
    }

    // Atualiza os meses disponíveis no select: desativa meses anteriores
for (let i = 0; i < mesSelect.options.length; i++) {
    if (parseInt(mesSelect.options[i].value) < mesAtual) {
        mesSelect.options[i].disabled = true;
    }
}
mesSelect.value = mesAtual; // Define o mês atual como selecionado

// Atualiza calendário ao trocar o mês
mesSelect.addEventListener('change', function () {
    mesAtual = parseInt(this.value);
    mes.textContent = nomesDosMeses[mesAtual - 1]; // Atualiza o nome do mês

    criarTabelaCalendario(mesAtual, anoAtual);
    marcarDiasCheios();
    dataDoAgendamento();
    desativarDomingos();
});


    function criarTabelaCalendario(mes, ano) {
        const tabela = document.getElementById('dataDisponiveis');
        tabela.innerHTML = ''; // limpa o conteúdo da tabela

        // cria o thead com os nomes dos dias da semana
        const thead = document.createElement('thead');
        const trHead = document.createElement('tr');
        const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        diasSemana.forEach(dia => {
            const th = document.createElement('th');
            th.textContent = dia;
            trHead.appendChild(th);
        });
        thead.appendChild(trHead);
        tabela.appendChild(thead);

        const tbody = document.createElement('tbody');
        const primeiroDiaSemana = new Date(ano, mes - 1, 1).getDay();

        const ultimoDia = new Date(ano, mes, 0).getDate();

        let dia = 1;
        for (let semana = 0; semana < 6; semana++) {
            const tr = document.createElement('tr');
            for (let coluna = 0; coluna < 7; coluna++) {
                const td = document.createElement('td');

                if ((semana === 0 && coluna < primeiroDiaSemana) || dia > ultimoDia) {
                    td.textContent = '';
                    td.style.pointerEvents = 'auto'
                } else {
                    td.textContent = dia;
                    td.id = `${dia}`;
                    td.classList.add('data');
                    dia++;
                }
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
            if (dia > ultimoDia) break; 
        }

        tabela.appendChild(tbody);
    }

    criarTabelaCalendario(mesAtual, anoAtual);
    marcarDiasCheios();
    dataDoAgendamento();
    desativarDomingos();

    // Adiciona essa parte no final do seu DOMContentLoaded
    for (let i = 0; i < mesSelect.options.length; i++) {
        if (parseInt(mesSelect.options[i].value) < mesAtual) {
            mesSelect.options[i].disabled = true;
        }
    }
    mesSelect.value = mesAtual;

    mesSelect.addEventListener('change', function () {
        mesAtual = parseInt(this.value);
        mes.textContent = nomesDosMeses[mesAtual - 1];
        criarTabelaCalendario(mesAtual, anoAtual);
        marcarDiasCheios();
        dataDoAgendamento();
        desativarDomingos();
    });


    
});






