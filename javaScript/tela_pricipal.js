
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


for (let i = 0; i < 5; i++) {
    const selectServico = document.getElementById(`servico-${i}`);

    if (!selectServico) continue; // Pula se não existir esse select

    selectServico.addEventListener('change', () => {
        marcarDiasCheios(selectServico.value, i);
    });

    // Chamada inicial se tiver um valor pré-selecionado
    if (selectServico.value) {
        marcarDiasCheios(selectServico.value, i);
    }
}

function marcarDiasCheios(servicoSelecionado, index) {
    if (!servicoSelecionado) {
        console.warn("Serviço selecionado inválido. Abortando marcação.");
        return;
    }

    fetch(`../../php/agendamentos/dias_ocupados.php?mes=${mesAtual}&ano=${anoAtual}`)
        .then(res => res.json())
        .then(data => {
            const diasCheios = data.dias_cheios || {};
            const horariosOcupados = data.horarios_ocupados || {};

            console.log(`📅 Dias cheios recebidos para servico-${index}:`, diasCheios);
            console.log("⏰ Horários ocupados recebidos:", horariosOcupados);

            // Limpar todos os dias previamente marcados de qualquer serviço
            Object.values(diasCheios).flat().forEach(dia => {
                const el = document.getElementById(dia);
                if (el) {
                    el.style.backgroundColor = '';
                    el.style.color = '';
                }
            });

            // Marcar dias cheios do serviço selecionado
            if (diasCheios[servicoSelecionado]) {
                diasCheios[servicoSelecionado].forEach(dia => {
                    const el = document.getElementById(dia);
                    if (el) {
                        el.style.backgroundColor = 'red';
                        el.style.color = 'white';
                    }
                });
            } else {
                console.warn(`Nenhum dia cheio para o serviço ${servicoSelecionado}`);
            } 

            // Marcar horários ocupados (em vermelho)
        // if (horariosOcupados[servicoSelecionado]) {
        //     const inputData = document.getElementById(`data-${index}`);
        //     if (!inputData) {
        //         console.warn(`Elemento data-${index} não encontrado.`);
        //         return;
        //     }

        //     const dataSelecionada = inputData.value;
        //     console.log("🔎 Data selecionada:", dataSelecionada);

        //     horariosOcupados[servicoSelecionado].forEach(entry => {
        //         console.log("⏰ Verificando horário:", entry);

        //         if (entry.dia === dataSelecionada) {
        //             const horaOcupada = entry.hora;

        //             document.querySelectorAll("#horarios tbody td").forEach(td => {
        //                 console.log("🔘 Comparando:", td.textContent, horaOcupada);
        //                 if (td.textContent.trim() === horaOcupada.trim()) {
        //                     td.style.backgroundColor = "red";
        //                     td.style.color = "white";
        //                     td.style.pointerEvents = "none";
        //                 }
        //             });
        //         }
        //     });
        // }

                 })
        //         .catch(err => console.error("Erro ao buscar dias ocupados:", err));
         }


function aplicarConfiguracoesMes() {
    const mesSelect = document.getElementById('mesSelect');
    if (!mesSelect) return; 

    // Primeiro, habilita tudo (reset)
    for (let i = 0; i < mesSelect.options.length; i++) {
        mesSelect.options[i].disabled = false;
    }

    // Carrega os meses desabilitados do localStorage
    const mesesDesabilitadosJSON = localStorage.getItem('mesesDesabilitados');
    if (mesesDesabilitadosJSON) {
        const mesesDesabilitados = JSON.parse(mesesDesabilitadosJSON); // Converte de volta para array

        mesesDesabilitados.forEach(valor => {
            for (let i = 0; i < mesSelect.options.length; i++) {
                if (mesSelect.options[i].value === valor) {
                    mesSelect.options[i].disabled = true;
                }
            }
        });
    }
}




function aplicarConfiguracoesSemanas() {
    const todasCelulas = document.querySelectorAll('td.data');

    // Carrega os dias da semana desabilitados do localStorage
    const diasSemanaDesabilitadosJSON = localStorage.getItem('diasSemanaDesabilitados');
    if (diasSemanaDesabilitadosJSON) {
        const diasMarcados = JSON.parse(diasSemanaDesabilitadosJSON);

        todasCelulas.forEach(td => {
            const dia = parseInt(td.textContent);
            if (isNaN(dia)) return;

            const diaSemana = new Date(anoAtual, mesAtual - 1, dia).getDay();

            if (diasMarcados.includes(diaSemana)) {
                td.style.color = "orange";
                td.style.pointerEvents = "none";
                td.style.opacity = "0.7";
            } else {
                // Se não estiver marcado, restaura o estilo
                td.style.color = "";
                td.style.pointerEvents = "auto";
                td.style.opacity = "1";
            }
        });
    } else {
        // Se não houver dados salvos, certifique-se de que todos os dias estejam habilitados
        todasCelulas.forEach(td => {
            td.style.color = "";
            td.style.pointerEvents = "auto";
            td.style.opacity = "1";
        });
    }
}


    document.addEventListener('DOMContentLoaded', () => {
        const dataAtual = new Date();
        mesAtual = dataAtual.getMonth() + 1; // Mês atual (1-12)
        anoAtual = dataAtual.getFullYear(); // Ano atual
    });


    
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

            const dataInputteste = document.getElementById("dataSelecionada"); // ← DEVE vir antes
            const dataSelecionada = `${anoAtual}-${mesFormatado}-${diaFormatado}`;
            dataInputteste.value = dataSelecionada;

            //verificarHorarioSimples(servicoId, dataSelecionada);
        });

    }
}

    function criarTabelaCalendario(mes, ano) {
    const tabela = document.getElementById('dataDisponiveis');
    tabela.innerHTML = '';

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
            } else {
                td.textContent = dia;
                td.id = `${ano}-${mes.toString().padStart(2, '0')}-${dia.toString().padStart(2, '0')}`;
                td.classList.add('data');
                td.dataset.mes = mes.toString().padStart(2, '0');
                td.dataset.ano = ano.toString();
                dia++;
            }

            tr.appendChild(td);
        }
        tbody.appendChild(tr);
        if (dia > ultimoDia) break;
    }

    tabela.appendChild(tbody);

    // ✅ Agora sim: só chama após os <td> estarem no DOM
    marcarDiasIndisponiveis(mes.toString().padStart(2, '0'), ano.toString());
    // Obtem o serviço selecionado atual do primeiro select (ou o que estiver em uso)
    const selectServico = document.getElementById('servico-0');
    if (selectServico) {
        marcarDiasCheios(selectServico.value, 0);  // ou o índice real usado
    }

}


    

    criarTabelaCalendario(mesAtual, anoAtual);
    marcarDiasCheios();
    dataDoAgendamento();
    aplicarConfiguracoesSemanas()
    aplicarConfiguracoesMes()
    ativarCliqueNosDias()

    //Adiciona essa parte no final do seu DOMContentLoaded
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
        aplicarConfiguracoesSemanas()
        aplicarConfiguracoesMes()
        ativarCliqueNosDias()    
     });
});

function marcarDiasIndisponiveis(mesVisivel, anoVisivel) {
    fetch("dias_indisponiveis.php")
        .then(res => res.json())
        .then(datas => {
            datas.forEach(data => {
                const [ano, mes, dia] = data.split("-");
                const idCompleto = `${ano}-${mes}-${dia}`;

                if (mes !== mesVisivel || ano !== anoVisivel) return;

                const td = document.getElementById(idCompleto);
                if (td) {
                    td.classList.add("desabilitado");
                    td.style.backgroundColor = "yellow";
                    td.style.pointerEvents = "none";
                    td.style.opacity = "0.6";
                }
            });
        })
        .catch(err => {
            console.error("Erro ao carregar dias indisponíveis:", err);
        });
}


document.querySelectorAll("td.data").forEach(td => {
    console.log(`${td.textContent} - ${td.dataset.dia}-${td.dataset.mes}-${td.dataset.ano}`);
});


// QUANTIDADE DE agendamentos
const container = document.getElementById('agendamentos-container');
const qtdSelect = document.getElementById('qtdagendamentos');
let agendamentoAtivo = null;

qtdSelect.addEventListener('change', function () {
    const qtd = parseInt(this.value);
    container.innerHTML = '';

    for (let i = 0; i < qtd; i++) {
        const div = document.createElement('div');
        div.classList.add('bloco-agendamento');
        div.dataset.index = i;

        div.innerHTML = `
            <hr>
            <p><strong>Agendamento ${i + 1}</strong></p>
            <label>Serviço:</label>
            <select id="servico-${i}" name="agendamentos[${i}][servico_id]" required>
                ${configs.map(config => {
                    const id = config.id;
                    const nome = config.tipo_servico;
                    const valor = parseFloat(config.valor).toFixed(2).replace('.', ',');
                    const duracao = config.duracao_servico;
                    const intervalo = config.intervalo_entre_servico;
                    return `<option value="${id}" data-duracao="${duracao}" data-intervalo="${intervalo}">${nome} - R$${valor}</option>`;
                }).join('')}
            </select>

            <label for="dia[${i}]">Para qual dia gostaria de agendar?</label>
            <input type="date" id="dia-${i}" name="dia[${i}]" readonly>

            <label for="hora[${i}]">Qual horário melhor para você?</label>
            <input type="time" id="hora-${i}" name="hora[${i}]" readonly>
        `;

        // Clique no bloco inteiro ativa
        div.addEventListener('click', () => {
            agendamentoAtivo = i;
            console.log("Agendamento ativo:", agendamentoAtivo);
        });

        // Clique em elementos internos também ativa o bloco
        setTimeout(() => {
            div.querySelectorAll('input, select, label').forEach(elemento => {
                elemento.addEventListener('click', () => {
                    agendamentoAtivo = i;
                    console.log("Agendamento ativo (interno):", agendamentoAtivo);
                });
            });
        }, 0);

        container.appendChild(div);
    }
});

qtdSelect.dispatchEvent(new Event('change'));


function ativarCliqueNosDias() {
    const dias = document.querySelectorAll('.data');

    dias.forEach(td => {
        td.addEventListener('click', () => {
            if (agendamentoAtivo === null) {
                alert("Clique em um agendamento primeiro.");
                return;
            }

            const dia = td.textContent.padStart(2, '0');
            const mesSelecionado = document.getElementById('mesSelect').value.padStart(2, '0');
            const agora = new Date();
            const ano = agora.getFullYear(); // opcionalmente, você pode criar um select de ano também
            const dataSelecionada = `${ano}-${mesSelecionado}-${dia}`;

            fetch('buscar_horario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'dataSelecionada=' + encodeURIComponent(dataSelecionada)
            })
            .then(response => response.json())
            .then(horarios => {
                if (!horarios.inicio || !horarios.termino) {
                    alert("Nenhum horário disponível para essa data.");
                    return;
                }

                const selectServico = document.getElementById(`servico-${agendamentoAtivo}`);
                const servicoId = selectServico.value;
                const tempoEntre = intervaloEntreHorarios[servicoId] || 60; // fallback pra evitar erro

                console.log("Tempo total entre sessões:", tempoEntre);


                // Preenche a data correta no input
                const inputData = document.getElementById(`dia-${agendamentoAtivo}`);
                inputData.value = dataSelecionada;

                // Gera e exibe os horários
                const lista = gerarHorariosComMinutos(horarios.inicio, horarios.termino, tempoEntre);
                
                if (lista.length === 0) {
                    alert("Nenhum horário disponível.");
                    return;
                }

                exibirHorarios(lista);
            })
            .catch(error => {
                console.error('Erro ao buscar horários:', error);
            });
        });
    });
}

function exibirHorarios(horarios) {
    const tbody = document.querySelector("#horarios tbody");
    tbody.innerHTML = "";

    horarios.forEach(horario => {
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.textContent = horario;

        td.addEventListener('click', function () {
            if (agendamentoAtivo !== null) {
                const inputHora = document.getElementById(`hora-${agendamentoAtivo}`);
                inputHora.value = horario;

                // Atualiza cor do horário selecionado
                document.querySelectorAll("#horarios tbody td").forEach(el => {
                    el.style.color = ''; // limpa os outros
                });
                this.style.color = 'blue';
            }
        });

        tr.appendChild(td);
        tbody.appendChild(tr);
    });
}


function gerarHorariosComMinutos(inicio, fim, intervaloMinutos) {
    const horarios = [];

    let [hInicio, mInicio] = inicio.split(':').map(Number);
    let [hFim, mFim] = fim.split(':').map(Number);

    let inicioMin = hInicio * 60 + mInicio;
    let fimMin = hFim * 60 + mFim;

    while (inicioMin + intervaloMinutos <= fimMin) {
        let h = Math.floor(inicioMin / 60).toString().padStart(2, '0');
        let m = (inicioMin % 60).toString().padStart(2, '0');
        horarios.push(`${h}:${m}`);
        inicioMin += intervaloMinutos;
    }

    return horarios;
}


document.addEventListener('DOMContentLoaded', () => {
    ativarCliqueNosDias();
});

document.addEventListener("DOMContentLoaded", () => {
    fetch("dias_indisponiveis.php")
        .then(res => res.json())
        .then(datas => {
           
            datas.forEach(data => {
                const [ano, mes, dia] = data.split("-");
                const idDia = parseInt(dia, 10).toString(); // id do td

                const td = document.getElementById(idDia);
                if (td) {
                    td.classList.add("desabilitado");
                    td.style.backgroundColor = "yellow"; // pinta de amarelo
                    td.style.pointerEvents = "none"; // desativa clique
                    td.style.opacity = "0.6"; // aparência de desabilitado
                }
            });
        })
        .catch(err => {
            console.error("Erro ao carregar dias indisponíveis:", err);
        });
});

