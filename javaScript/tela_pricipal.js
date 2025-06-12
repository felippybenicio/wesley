
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






//     const todosHorarios = [
//   '08:00', '09:00', '10:00', '11:00',
//   '13:00', '14:00', '15:00', '16:00'
// ];

// function carregarHorarios(dataSelecionada) {
//   fetch(`php/get_horarios_ocupados.php?data=${dataSelecionada}`)
//     .then(res => res.json())
//     .then(ocupados => {
//       const horariosDisponiveis = todosHorarios.filter(h => !ocupados.includes(h));
//       const select = document.getElementById('horarios');

//       select.innerHTML = ''; // limpa

//       horariosDisponiveis.forEach(horario => {
//         const option = document.createElement('option');
//         option.value = horario;
//         option.textContent = horario;
//         select.appendChild(option);
//       });

//       if (horariosDisponiveis.length === 0) {
//         const option = document.createElement('option');
//         option.textContent = 'Sem horários disponíveis';
//         option.disabled = true;
//         select.appendChild(option);
//       }
//     });
// }

// // Exemplo: quando o usuário escolhe a data
// document.getElementById('data').addEventListener('change', e => {
//   carregarHorarios(e.target.value);
// });








    
    function dataDoAgendamento() {
    const dias = {};
    const servicoId = document.getElementById("servico").value;

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


// function verificarHorarioSimples(servicoId, dataSelecionada) { 
//     document.querySelectorAll('.hora-disponivel').forEach(td => {
//         const hora = td.textContent.trim();

//         const bodyData = `servico=${encodeURIComponent(servicoId)}&data=${encodeURIComponent(dataSelecionada)}&hora=${encodeURIComponent(hora)}&duracao=30`;
        
//         console.log("servicoId:", servicoId);
//         console.log("dataSelecionada:", dataSelecionada);
//         console.log("hora:", hora);
//         console.log("bodyData:", bodyData);


//         fetch('php/agendamento.php', {

//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/x-www-form-urlencoded'
//             },
//             body: bodyData
//         })
//         .then(response => response.json())
//         .then(data => {
//             console.log("Resposta recebida:", data);
//             if (data.disponivel === false) {
//                 td.style.backgroundColor = 'red';
//                 td.style.color = 'white';
//             } else if (data.disponivel === true) {
//                 td.style.backgroundColor = 'green';
//                 td.style.color = 'white';
//             }
//         })
//         .catch(error => {
//             console.error('Erro ao verificar horário:', error);
//         });
//     });
// }






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



function ativarCliqueNosDias() {
    const dias = document.querySelectorAll('.data');

    dias.forEach(td => {
        td.addEventListener('click', () => {
            console.log('Clicou em:', td.textContent);
            const dia = td.textContent.padStart(2, '0');
            let agora = new Date();
            let mesAtual = agora.getMonth() + 1;
            let anoAtual = agora.getFullYear();
            const mes = String(mesAtual).padStart(2, '0');
            const ano = anoAtual;
            const dataSelecionada = `${ano}-${mes}-${dia}`; 

            console.log('Data clicada:', dataSelecionada);

            fetch('buscar_horario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'dataSelecionada=' + encodeURIComponent(dataSelecionada)
            })
            .then(response => response.json())
            .then(horarios => {
                console.log('Horários recebidos do PHP:', horarios);

                if (!horarios.inicio || !horarios.termino) {
                    alert("Nenhum horário disponível para essa data.");
                    return;
                }

                // Pegando o tempo total entre sessões do PHP (duração + intervalo em minutos)
                const tempoEntre = (() => {
                    const servico = document.getElementById("servico");
                    const indice = parseInt(servico.value); // pega o índice do serviço
                    return intervaloEntreHorarios[indice]; // usa o tempo correto
                })();


                // Gera e exibe os horários
                const lista = gerarHorariosComMinutos(horarios.inicio, horarios.termino, tempoEntre);
                exibirHorarios(lista);

                // Atualiza texto com tempo total
                document.getElementById("tempoTotal").textContent =
                    "Tempo entre serviços: " + formatarMinutos(tempoEntre);
            })
            .catch(error => {
                console.error('Erro ao buscar horários:', error);
            });
        });
    });
}

// Função para gerar horários com base no tempo total (minutos)
function gerarHorariosComMinutos(inicio, fim, tempoTotalMin) {
    const horarios = [];

    const [hInicio, mInicio] = inicio.split(":").map(Number);
    const [hFim, mFim] = fim.split(":").map(Number);

    let atual = new Date(0, 0, 0, hInicio, mInicio, 0);
    const limite = new Date(0, 0, 0, hFim, mFim, 0);

    while (atual <= limite) {
        const horaStr = atual.toTimeString().substring(0, 5);
        horarios.push(horaStr);
        atual.setMinutes(atual.getMinutes() + tempoTotalMin);
    }

    return horarios;
}

// Função auxiliar para mostrar os horários na tabela
function exibirHorarios(horarios) {
    const tbody = document.querySelector("#horarios tbody");
    tbody.innerHTML = "";

    horarios.forEach(horario => {
        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.textContent = horario;

        // Evento de clique no horário
        td.addEventListener('click', function () {
            // Remove cor azul de todos os horários
            document.querySelectorAll("#horarios tbody td").forEach(el => {
                el.style.color = ''; // ou 'black' se preferir
            });

            // Marca o horário clicado como azul
            document.getElementById("hora").value = horario;
            this.style.color = 'blue';
        });

        tr.appendChild(td);
        tbody.appendChild(tr);
    });
}



// Função para formatar minutos em "xh ymin"
function formatarMinutos(min) {
    const h = Math.floor(min / 60);
    const m = min % 60;
    return `${h > 0 ? h + "h " : ""}${m > 0 ? m + "min" : "0min"}`;
}



// // HORAS DISPONIVEIS
//     const selectServico = document.getElementById('servico');
//     const selectSessao = document.getElementById('duracao');
//     const dataSelecionadaInput = document.getElementById('dataSelecionada');
//     const mesSelect = document.getElementById('mesSelect');
//     const diasDisponiveisTableBody = document.querySelector('#dataDisponiveis tbody');
//     const mesStrongElement = document.querySelector('.mes');
//     const tabelaHorariosBody = document.querySelector('#horarios tbody');
//     const horaInput = document.getElementById('hora');
//     const tempoTotalInfo = document.getElementById('tempoTotal');

// function getMonthName(monthNum) {
//     const monthNames = [
//         "janeiro", "fevereiro", "março", "abril", "maio", "junho",
//         "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"
//     ];
//     return monthNames[monthNum - 1] || "Mês Inválido";
// }

// function gerarDiasDoMes(year, month) {
//     diasDisponiveisTableBody.innerHTML = "";
//     const today = new Date();
//     const firstDay = new Date(year, month - 1, 1);
//     const lastDay = new Date(year, month, 0).getDate();

//     let dayOfWeek = firstDay.getDay();

//     let row = document.createElement('tr');
//     for (let i = 0; i < dayOfWeek; i++) {
//         row.innerHTML += '<td></td>';
//     }

//     for (let day = 1 ; day <= lastDay; day++) {
//         const fullDate = new Date(year, month - 1, day);
//         const isPastDay = fullDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());

//         if (dayOfWeek === 0 && day > 1) {
//             diasDisponiveisTableBody.appendChild(row);
//             row = document.createElement('tr');
//         }

//         const cell = document.createElement('td');
//         cell.id = day;
//         cell.classList.add('data');
//         cell.textContent = day;

//         if (isPastDay) {
//             cell.classList.add('unavailable');
//         } else {
//             cell.addEventListener('click', () => {
//                 document.querySelectorAll('.data.selected').forEach(td => td.classList.remove('selected'));
//                 cell.classList.add('selected');

//                 const formattedDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
//                 dataSelecionadaInput.value = formattedDate;
//             });
//         }
//         row.appendChild(cell);
//         dayOfWeek = (dayOfWeek + 1) % 7;
//     }

//     while (dayOfWeek !== 0) {
//         row.innerHTML += '<td></td>';
//         dayOfWeek = (dayOfWeek + 1) % 7;
//     }
//     diasDisponiveisTableBody.appendChild(row);

//     mesStrongElement.textContent = getMonthName(month);
// }

// function gerarHorarios() {
//     const selectedServiceOption = selectServico.options[selectServico.selectedIndex];

//     if (!selectedServiceOption || !selectedServiceOption.dataset.duracao || !selectedServiceOption.dataset.intervalo) {
//         tabelaHorariosBody.innerHTML = '<tr><td colspan="1">Selecione um serviço válido para ver os horários.</td></tr>';
//         tempoTotalInfo.textContent = "Tempo entre serviços: -- minutos";
//         return;
//     }

//     const duracaoServicoBase = parseInt(selectedServiceOption.dataset.duracao) || 0;
//     const intervaloEntreServicos = parseInt(selectedServiceOption.dataset.intervalo) || 0;
    
//     const numSessoes = parseInt(selectSessao.value) || 1; 

//     const duracaoTotalServico = duracaoServicoBase * numSessoes;

//     const tempoTotalPorSlot = duracaoTotalServico + intervaloEntreServicos;

//     tempoTotalInfo.textContent = `Tempo entre serviços: ${tempoTotalPorSlot} minutos`;

//     tabelaHorariosBody.innerHTML = "";

//     let [expedienteStartH, expedienteStartM] = globalHoraInicio.split(":").map(Number); 
//     const [expedienteEndH, expedienteEndM] = globalHoraTermino.split(":").map(Number);

//     let currentSlotStart = new Date(0, 0, 0, expedienteStartH, expedienteStartM, 0, 0);
//     const expedienteEnd = new Date(0, 0, 0, expedienteEndH, expedienteEndM, 0, 0);

//     let slotCounter = 1;
//     let hasGeneratedHours = false;

//     while (currentSlotStart.getTime() < expedienteEnd.getTime()) {
//         const currentServiceEnd = new Date(currentSlotStart.getTime());
//         currentServiceEnd.setMinutes(currentServiceEnd.getMinutes() + duracaoTotalServico);

//         if (currentServiceEnd.getTime() > expedienteEnd.getTime()) {
//             break;
//         }

//         const horaFormatada = currentSlotStart.toTimeString().slice(0, 5);
//         const newRow = document.createElement('tr');
//         const newCell = document.createElement('td');
//         newCell.id = `hora${slotCounter}`;
//         newCell.textContent = horaFormatada;
//         newCell.classList.add('hora-disponivel');
//         newCell.addEventListener('click', () => {
//             document.querySelectorAll('.hora-disponivel.selected').forEach(td => td.classList.remove('selected'));
//             newCell.classList.add('selected');
//             horaInput.value = horaFormatada;
//         });

//         newRow.appendChild(newCell);
//         tabelaHorariosBody.appendChild(newRow);
//         hasGeneratedHours = true;

//         currentSlotStart.setMinutes(currentSlotStart.getMinutes() + tempoTotalPorSlot);
//         slotCounter++;
//     }

//     if (!hasGeneratedHours) {
//         tabelaHorariosBody.innerHTML = '<tr><td colspan="1">Nenhum horário disponível para este serviço no expediente.</td></tr>';
//     }
// }

// function updateCalendar() {
//     const today = new Date();
//     const currentYear = today.getFullYear();
//     const selectedMonth = parseInt(mesSelect.value);

//     gerarDiasDoMes(currentYear, selectedMonth);
// }

// function initializeDate() {
//     const today = new Date();
//     const year = today.getFullYear();
//     const month = String(today.getMonth() + 1).padStart(2, '0');
//     const day = String(today.getDate()).padStart(2, '0');

//     dataSelecionadaInput.value = `${year}-${month}-${day}`;
//     mesSelect.value = today.getMonth() + 1;
//     mesStrongElement.textContent = getMonthName(today.getMonth() + 1);

//     gerarDiasDoMes(year, today.getMonth() + 1);
// }

// window.addEventListener('load', () => {
//     initializeDate();
//     setTimeout(gerarHorarios, 100);
// });

// selectServico.addEventListener('change', gerarHorarios);
// selectSessao.addEventListener('change', gerarHorarios);
// mesSelect.addEventListener('change', updateCalendar); 

   
