
const dias = document.querySelectorAll("td.data");
const ul = document.querySelector("ul");
const mesSelect = document.getElementById('mesSelect');
const anoSelect = document.getElementById('anoSelect');
let agora = new Date();
let mesAtual = agora.getMonth() + 1;
let anoAtual = agora.getFullYear();





//SERVIÇOS E FUNCIONARIOS
    document.addEventListener("DOMContentLoaded", function () {
    const qtdInput = document.getElementById("quantidadeServicos");
    const container = document.getElementById("camposServicos");


    function criarCampos(dados = []) {
    const qtd = Math.min(Math.max(parseInt(qtdInput.value) || 1, 1), 5);
    container.innerHTML = "";

    function formatarHora(hora) {
      if (!hora) return "";
      if (hora.length > 5) hora = hora.slice(0, 5);
      const partes = hora.split(":");
      if (partes.length < 2) return "";
      let hh = partes[0].padStart(2, "0");
      let mm = partes[1].padStart(2, "0");
      return `${hh}:${mm}`;
    }

    for (let i = 1; i <= qtd; i++) {
        const s = dados[i - 1] || {};
        const tipo = s.tipo || "";
        const valor = s.valor || "";
        const qtFunc = parseInt(s.qtFuncionario || 1);
        const funcionarios = s.funcionarios || [];
        const duracao = formatarHora(s.duracao || "");
        const intervalo = formatarHora(s.intervalo || "");

        const bloco = document.createElement("div");
        bloco.innerHTML = `
            <h3>Serviço ${i}</h3>
            <div>
                <label for="tipo${i}">Serviço:</label>
                <input type="text" name="tipo${i}" id="tipo${i}" value="${tipo}">
            </div><br>

            <div>
                <label for="valor${i}">Valor:</label>
                <input type="number" step="0.01" name="valor${i}" id="valor${i}" value="${valor}">
            </div>

            <div>
                <label for="duracaoServico${i}">Duração do Serviço:</label>
                <input type="time" name="duracaoServico${i}" id="duracaoServico${i}" value="${duracao}">
            </div>

            <div>
                <label for="intervaloServico${i}">Intervalo do Serviço:</label>
                <input type="time" name="intervaloServico${i}" id="intervaloServico${i}" value="${intervalo}">
            </div>

            <div>
                <label for="qtFuncionario${i}">Quantidade de funcionários:</label>
                <input type="number" name="qtFuncionario${i}" id="qtFuncionario${i}" value="${qtFunc}" min="1" max="5">
            </div>

            <div id="funcionarios${i}"></div>
        `;

        container.appendChild(bloco);


            const qtFuncInput = bloco.querySelector(`#qtFuncionario${i}`);
            const funcContainer = bloco.querySelector(`#funcionarios${i}`);

            function atualizarFuncionarios(qtdF) {
                funcContainer.innerHTML = "";
                for (let j = 1; j <= qtdF; j++) {
                    const nome = funcionarios[j - 1] || "";
                    const fDiv = document.createElement("div");
                    fDiv.innerHTML = `
                        <label for="funcionario${i}_${j}">Nome do funcionário ${j}:</label>
                        <input type="text" name="funcionario${i}_${j}" id="funcionario${i}_${j}" value="${nome}">
                    `;
                    funcContainer.appendChild(fDiv);
                }
            }

            atualizarFuncionarios(qtFunc);

            qtFuncInput.addEventListener("input", function () {
                const novaQtd = Math.min(Math.max(parseInt(qtFuncInput.value) || 1, 1), 5);
                atualizarFuncionarios(novaQtd);
            });
        }
    }

    // Inicializa com dados do POST (se houver)
    criarCampos(servicosPost);



    // Atualiza campos ao mudar quantidade de serviços
    qtdInput.addEventListener("input", function () {
    const dadosAtuais = coletarDadosAtuais(); // coleta o que o usuário já digitou
    const dadosArray = [];

    // transforma o objeto em array, pois criarCampos espera um array
    for (let i = 1; i <= 5; i++) {
        if (dadosAtuais[i]) {
            dadosArray.push(dadosAtuais[i]);
        }
    }

    criarCampos(dadosArray);
    });

    });

    const qtdInput = document.getElementById("quantidadeServicos");

    function coletarDadosAtuais() {
        const qtdInput = document.getElementById("quantidadeServicos");
        const dados = {};
        const qtd = Math.min(Math.max(parseInt(qtdInput.value) || 1, 1), 5);

        for (let i = 1; i <= qtd; i++) {
            const tipoInput = document.querySelector(`#tipo${i}`);
            const valorInput = document.querySelector(`#valor${i}`);
            const qtFuncInput = document.querySelector(`#qtFuncionario${i}`);
            const duracaoInput = document.querySelector(`#duracaoServico${i}`);
            const intervaloInput = document.querySelector(`#intervaloServico${i}`);

            if (!tipoInput || !valorInput || !qtFuncInput) continue;

            const tipo = tipoInput.value || "";
            const valor = valorInput.value || "";
            const qtFuncionario = parseInt(qtFuncInput.value) || 1;
            const duracao = duracaoInput ? duracaoInput.value : "";
            const intervalo = intervaloInput ? intervaloInput.value : "";

            const funcionarios = [];
            for (let j = 1; j <= qtFuncionario; j++) {
                const fInput = document.querySelector(`#funcionario${i}_${j}`);
                funcionarios.push(fInput ? fInput.value : "");
            }

            dados[i] = {
                tipo,
                valor,
                qtFuncionario,
                funcionarios,
                duracao,
                intervalo
            };
        }

        return dados;
    }


    const nomesDosMeses = [
    "janeiro", "fevereiro", "março", "abril", "maio", "junho",
    "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"
    ];


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
                    td.setAttribute("name", "diasIndisponiveis");
                    td.classList.add('data');
                    dia++;
                }
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
            if (dia > ultimoDia) break; 
        }

        tabela.appendChild(tbody);
        diasDeNaoFucionamento();
        diasDeNaoFucionamento();
        semanasDesabilitado();
    }

    criarTabelaCalendario(mesAtual, anoAtual);


    function mesDesabilitado() {
        const checkboxes = document.querySelectorAll('.mes-checkbox');
        const mesSelect = document.getElementById('mesSelect');

        // Primeiro, habilita tudo (reset)
        for (let i = 0; i < mesSelect.options.length; i++) {
            mesSelect.options[i].disabled = false;
        }


        checkboxes.forEach(cb => {
            if (cb.checked) {
                const valor = cb.value;
                for (let i = 0; i < mesSelect.options.length; i++) {
                    if (mesSelect.options[i].value === valor) {
                        mesSelect.options[i].disabled = true;
                    }
                }
            }
        });
    }

        function semanasDesabilitado() {
        const checkboxes = document.querySelectorAll('.sem-checkbox:checked');
        const diasMarcados = Array.from(checkboxes).map(cb => parseInt(cb.value));

        const todasCelulas = document.querySelectorAll('td.data');

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
    }




    function diasDeNaoFucionamento() {
        const dias = document.querySelectorAll("td.data"); // atualiza os dias

        dias.forEach(dia => {
            dia.addEventListener("click", () => {
                const diaNumero = dia.id.padStart(2, '0');
                const mesNumero = mesSelect.value.padStart(2, '0');
                const ano = anoSelect.value;

                const dataFormatada = `${diaNumero}/${mesNumero}/${ano}`; // para verificação
                const dataParaInput = `${ano}-${mesNumero}-${diaNumero}`; // para input date (yyyy-mm-dd)

                const jaExiste = [...ul.querySelectorAll("input[type='date']")]
                    .some(input => input.value === dataParaInput);

                const h3 = document.getElementById('naoFuncionamento');
                if (jaExiste) return; // Evita duplicatas

                dia.classList.add("desabilitado");
                dia.style.pointerEvents = "none";
                dia.style.opacity = "0.5";

                const li = document.createElement("li");
                h3.innerHTML = "Datas de não funcionamento";
                h3.style.display = "block";

                // Criar input visível
                const inputData = document.createElement("input");
                inputData.type = "date";
                inputData.value = dataParaInput;
                inputData.name = "diasIndisponiveis[]";
                inputData.readOnly = true;
                inputData.required = true;
                

                // Botão de remover
                const btnRemover = document.createElement("button");
                btnRemover.textContent = "Remover";
                btnRemover.style.marginLeft = "10px";
                btnRemover.style.cursor = "pointer";

                btnRemover.addEventListener("click", () => {
                    li.remove();

                    // Reativa o dia no calendário
                    const diaElement = document.getElementById(parseInt(diaNumero, 10));
                    if (diaElement) {
                        diaElement.classList.remove("desabilitado");
                        diaElement.style.pointerEvents = "auto";
                        diaElement.style.opacity = "1";

                        if (ul.children.length === 0) {
                            h3.style.display = "none";
                        }
                    }
                });

                li.appendChild(inputData);
                li.appendChild(btnRemover);
                ul.appendChild(li);
            });
        });
    }


        

    document.querySelectorAll('.sem-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            semanasDesabilitado();
        });
    });


    document.querySelectorAll('.mes-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            mesDesabilitado();
        });
    });




    mesSelect.value = mesAtual;

    function mes_anoSelecionado() {
        mesSelect.addEventListener('change', function () {
            mesAtual = parseInt(this.value);
            criarTabelaCalendario(mesAtual, anoAtual);
            diasDeNaoFucionamento();
        });

        anoSelect.addEventListener('change', function () {
            anoAtual = parseInt(this.value);
            criarTabelaCalendario(mesAtual, anoAtual);
            diasDeNaoFucionamento();
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        mesDesabilitado()
        semanasDesabilitado()
        mes_anoSelecionado();
        diasDeNaoFucionamento()
    });

    const diasSelecionados = [];

    document.querySelectorAll('.data').forEach(td => {
        td.addEventListener('click', () => {
            td.classList.toggle('selecionado');
            const dia = td.id;
            const mes = document.getElementById('mesSelect').value;
            const ano = document.getElementById('anoSelect').value;

            const dataCompleta = `${ano}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;

            if (td.classList.contains('selecionado')) {
                diasSelecionados.push(dataCompleta);
            } else {
                const index = diasSelecionados.indexOf(dataCompleta);
                if (index > -1) diasSelecionados.splice(index, 1);
            }

            document.getElementById('diasIndisponiveis').value = JSON.stringify(diasSelecionados);
        });
    });


    //HORAS
    document.addEventListener('DOMContentLoaded', function () {
    const segundaInicio = document.querySelector('input.inicio[data-index="1"]');
    const segundaFim = document.querySelector('input.fim[data-index="1"]');

    segundaInicio.addEventListener('input', function () {
        const valor = this.value;
        document.querySelectorAll('input.inicio').forEach(input => {
        if (input.dataset.index && input.dataset.index !== "0" && input.dataset.index !== "1") {
            input.value = valor;
        }
        });
    });

    segundaFim.addEventListener('input', function () {
        const valor = this.value;
        document.querySelectorAll('input.fim').forEach(input => {
        if (input.dataset.index && input.dataset.index !== "0" && input.dataset.index !== "1") {
            input.value = valor;
        }
        });
    });

    // Adicionar nova linha com data específica
    document.getElementById('addData').addEventListener('click', function () {
        const tbody = document.getElementById('datas-especificas');
        const index = tbody.children.length;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="date" name="tipoDia[]" value=""></td>
            <td><input type="time" name="inicio[]" value="" class="inicio" data-index="${index}"></td>
            <td><input type="time" name="fim[]" value="" class="fim" data-index="${index}"></td>
            <td style="background:red;cursor:pointer" onclick="this.parentNode.remove()">X</td>
        `;
        tbody.appendChild(row);
    });

    const salvar = document.getElementById('salvar')

    salvar.addEventListener('click', function () {
    setTimeout(() => {
        location.reload();
    }, 500); // tempo em milissegundos (500ms = meio segundo)
});


});













