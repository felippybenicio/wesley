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
                if (h.style.color !== 'red') h.style.color = 'black';
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
        .then(horasOcupadas => {  // aqui já é o array retornado
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




marcarDiasCheios();
dataDoAgendamento();
