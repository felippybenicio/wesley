const dias = document.querySelectorAll("td.data");
const ul = document.querySelector("ul");
const mesSelect = document.getElementById('mesSelect'); 
const anoSelect = document.getElementById('anoSelect');
let agora = new Date();
let mesAtual = agora.getMonth() + 1;
let anoAtual = agora.getFullYear();


console.log(dadosServicos);



//SERVIÇOS E FUNCIONARIOS

    let dadosGlobais = [];

    const qtdInput = document.getElementById("quantidadeServicos");
    const container = document.getElementById("camposServicos");

    function deletarServico(idServico) {
        const index = dadosGlobais.findIndex(s => String(s.id) === String(idServico));
        if (index !== -1) {
            dadosGlobais.splice(index, 1); // remove do array
        }

        const qtdInput = document.getElementById("quantidadeServicos");
        qtdInput.value = dadosGlobais.length;

        criarCampos(dadosGlobais); // reconstrói a interface
    }


function criarCampos(dados = []) {
    dadosGlobais = dados; 
    const qtd = dados.length; // usa somente os dados válidos

    const dadosSalvos = [];

for (let i = 0; i < dados.length; i++) {
    const d = dados[i] || {};

    dadosSalvos.push({
        id: d.id || "",
        tipo_servico: d.tipo_servico || "",
        valor: d.valor || "",
        duracao_servico: d.duracao_servico || "",
        intervalo_entre_servico: d.intervalo_entre_servico || "",
        quantidade_de_funcionarios: parseInt(d.quantidade_de_funcionarios || "1"),
        funcionarios: Array.isArray(d.funcionarios) ? d.funcionarios : []
    });
}


    // Usa somente os dados reais
    const dadosUsar = [];
    for (let i = 0; i < qtd; i++) {
        dadosUsar[i] = dadosSalvos[i] || dados[i] || {};
    }

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
        const s = dadosUsar[i - 1] || {};
        const idServico = s.id || "";
        const idSecundario = s.id_secundario || "";
        const tipo = s.tipo_servico || "";
        const valor = s.valor || "";
        const qtFunc = parseInt(s.quantidade_de_funcionarios || 1);
        const funcionarios = s.funcionarios || [];
        const duracao = formatarHora(s.duracao_servico || "");
        const intervalo = formatarHora(s.intervalo_entre_servico || "");

        console.log('Criando bloco', i, 'idServico:', idServico);

        const bloco = document.createElement("section");
        bloco.innerHTML = `
            <h3>Serviço ${i} <strong id="deleteServico${i}" style="background: red">X</strong></h3> 
            <input type="hidden" class="id-servico" name="id${i}" value="${idServico}">
            <input type="hidden" name="id_secundario${i}" value="${idSecundario}">

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

        const funcContainer = bloco.querySelector(`#funcionarios${i}`);

        function atualizarFuncionarios(qtdF) {
            funcContainer.innerHTML = "";
            for (let j = 1; j <= qtdF; j++) {
                const nome = funcionarios[j - 1] || "";
                const fDiv = document.createElement("div");
                fDiv.innerHTML = `
                    <input type="hidden" name="servicoId${i}_${j}" value="${idServico}">
                    <label for="funcionario${i}_${j}">Nome do funcionário ${j}:</label>
                    <input type="text" name="funcionario${i}_${j}" id="funcionario${i}_${j}" value="${nome}">
                `;
                funcContainer.appendChild(fDiv);
            }
        }



        atualizarFuncionarios(qtFunc);

        bloco.querySelector(`#qtFuncionario${i}`).addEventListener("input", function () {
            const novaQtd = Math.min(Math.max(parseInt(this.value) || 1, 1), 5);

            // 1. Coleta os dados atuais de todos serviços/funcionários antes de modificar a UI
            const dadosAtuais = coletarDadosAtuais();

            // 2. Atualiza os dadosGlobais para manter o estado
            dadosGlobais = dadosAtuais;

            // 3. Atualiza somente este serviço específico na lista para novaQtd de funcionários
            dadosGlobais[i - 1].quantidade_de_funcionarios = novaQtd;

            // 4. Garante que a lista funcionarios tem o tamanho correto (sem perder nomes existentes)
            const funcs = dadosGlobais[i - 1].funcionarios || [];
            if (funcs.length < novaQtd) {
                // adiciona vazios para completar
                for (let k = funcs.length; k < novaQtd; k++) {
                    funcs.push("");
                }
            } else if (funcs.length > novaQtd) {
                // remove extras
                funcs.length = novaQtd;
            }
            dadosGlobais[i - 1].funcionarios = funcs;

            // 5. Recria os campos para refletir a mudança
            criarCampos(dadosGlobais);
        });


        container.appendChild(bloco);

        const botaoDelete = bloco.querySelector(`#deleteServico${i}`);
        botaoDelete.addEventListener('click', function () {
            const idServico = bloco.querySelector('.id-servico')?.value || '';

            if (confirm("Deseja realmente deletar o serviço e os funcionários?")) {
                fetch('../agendamentos/deletar_servico.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id_servico: idServico,        
                    })
                })
                .then(res => res.text())
                .then(res => {
                    console.log('Resposta do PHP (crua):', res);
                    if (res.includes('success')) {
                        deletarServico(idServico);
                    } else {
                        alert('Erro ao deletar: ' + res);
                    }
                });
            }
        });
    }
}

    function coletarDadosAtuais() {
    const qtdInput = document.getElementById("quantidadeServicos");
    const dados = [];
    const qtd = Math.min(Math.max(parseInt(qtdInput.value) || 1, 1), 5);

    for (let i = 1; i <= qtd; i++) {
        const tipoInput = document.querySelector(`#tipo${i}`);
        const valorInput = document.querySelector(`#valor${i}`);
        const qtFuncInput = document.querySelector(`#qtFuncionario${i}`);
        const duracaoInput = document.querySelector(`#duracaoServico${i}`);
        const intervaloInput = document.querySelector(`#intervaloServico${i}`);
        const idInput = document.querySelector(`[name="id${i}"]`);
        const idSecInput = document.querySelector(`[name="id_secundario${i}"]`);

        if (!tipoInput || !valorInput || !qtFuncInput) continue;

        const tipo = tipoInput.value || "";
        const valor = valorInput.value || "";
        const qtFuncionario = parseInt(qtFuncInput.value) || 1;
        const duracao = duracaoInput ? duracaoInput.value : "";
        const intervalo = intervaloInput ? intervaloInput.value : "";
        const id = idInput ? idInput.value : "";
        const id_secundario = idSecInput ? idSecInput.value : "";

        // Coleta os funcionários
        const funcionarios = [];
        for (let j = 1; j <= qtFuncionario; j++) {
            const inputFunc = document.querySelector(`#funcionario${i}_${j}`);
            const nome = inputFunc ? inputFunc.value : "";
            funcionarios.push(nome);
        }

        dados[i - 1] = {
            id: id,
            id_secundario: id_secundario,
            tipo_servico: tipo,
            valor: valor,
            quantidade_de_funcionarios: qtFuncionario,
            duracao_servico: duracao,
            intervalo_entre_servico: intervalo,
            funcionarios: funcionarios
        };
    }

    return dados;
}



    
    function garantirQuantidadeDeServicos(dados, qtFunc) {
    const novaLista = [];
    for (let i = 0; i < qtFunc; i++) {
        novaLista[i] = dados[i] || {
            tipo_servico: "",
            valor: "",
            quantidade_de_funcionarios: 1,
            duracao_servico: "",
            intervalo_entre_servico: "",
            funcionarios: []
        };
    }


    return novaLista;
}


    document.addEventListener("DOMContentLoaded", function () {
        const qtdInput = document.getElementById("quantidadeServicos");

        if (window.dadosServicosSalvos && window.dadosServicosSalvos.length > 0) {
            qtdInput.value = window.dadosServicosSalvos.length;
        } else {
            qtdInput.value = 1;
        }

        let dadosIniciais = garantirQuantidadeDeServicos(window.dadosServicosSalvos || [], parseInt(qtdInput.value));
        criarCampos(dadosIniciais);

        qtdInput.addEventListener("input", function () {
            const novaQtd = Math.min(Math.max(parseInt(qtdInput.value) || 1, 1), 5);

            // 1. Coleta dados do DOM antes de atualizar
            const dadosAtualizados = coletarDadosAtuais();

            // 2. Converte objeto em array (caso esteja como objeto)
            const listaAtualizada = dadosAtualizados;


            // 3. Garante que vamos ter blocos suficientes (preenche com vazios)
            const dadosAjustados = garantirQuantidadeDeServicos(listaAtualizada, novaQtd);

            // 4. Atualiza dadosGlobais com os dados atuais + campos vazios
            dadosGlobais = dadosAjustados;

            // 5. Recria os campos na tela
            criarCampos(dadosAjustados);
        });

    });

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

        const mesesDesabilitados = []; 

        checkboxes.forEach(cb => {
            if (cb.checked) {
                const valor = cb.value;
                mesesDesabilitados.push(valor); 
                for (let i = 0; i < mesSelect.options.length; i++) {
                    if (mesSelect.options[i].value === valor) {
                        mesSelect.options[i].disabled = true;
                    }
                }
            }
        });
        localStorage.setItem('mesesDesabilitados', JSON.stringify(mesesDesabilitados));
    }

    document.addEventListener('DOMContentLoaded', () => {
        mesDesabilitado(); // Aplica ao carregar a página de configuração

        const checkboxesMes = document.querySelectorAll('.mes-checkbox');
        checkboxesMes.forEach(checkbox => {
            checkbox.addEventListener('change', mesDesabilitado); // Chama a função quando o checkbox muda
        });
    });



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

        localStorage.setItem('diasSemanaDesabilitados', JSON.stringify(diasMarcados));

    }

    document.addEventListener('DOMContentLoaded', () => {
        semanasDesabilitado(); // Aplica ao carregar a página de configuração

        const checkboxesSemana = document.querySelectorAll('.sem-checkbox');
        checkboxesSemana.forEach(checkbox => {
            checkbox.addEventListener('change', semanasDesabilitado);
        });
    });




    function diasDeNaoFucionamento() {
        const dias = document.querySelectorAll("td.data"); // atualiza os dias

        dias.forEach(dia => {
            dia.addEventListener("click", () => {
                const diaNumero = dia.id.padStart(2, '0');
                const mesNumero = mesSelect.value.padStart(2, '0');
                const ano = anoSelect.value;

                const dataParaInput = `${ano}-${mesNumero}-${diaNumero}`; // para input date (yyyy-mm-dd)

                const jaExiste = [...ul.querySelectorAll("input[type='date']")]
                    .some(input => input.value === dataParaInput);

                const h3 = document.getElementById('naoFuncionamento');
                if (jaExiste) return; // Evita duplicatas

                dia.classList.add("desabilitado");
                dia.style.pointerEvents = "none";
                dia.style.opacity = "0.5";

                                // Cria o <li> ANTES
                const li = document.createElement("li");

                // Título e estilos
                h3.innerHTML = "Datas de não funcionamento";
                h3.style.display = "block";

                // Input da data
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

                // Agora sim: o botão vê o <li>
                btnRemover.addEventListener("click", () => {
                const dataRemover = li.querySelector("input[type='date']").value;

                fetch("remover.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `data=${encodeURIComponent(dataRemover)}`
                })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === "OK") {
                        li.remove();

                        const diaElement = document.getElementById(parseInt(diaNumero, 10));
                        if (diaElement) {
                            diaElement.classList.remove("desabilitado");
                            diaElement.style.pointerEvents = "auto";
                            diaElement.style.color = "yellow";

                            if (ul.children.length === 0) {
                                h3.style.display = "none";
                            }
                        }
                    } else {
                        alert("Erro ao remover data do banco: " + res);
                    }
                })
                .catch(err => {
                    console.error("Erro na requisição:", err);
                    alert("Erro ao comunicar com o servidor.");
                });
            });


                            // Adiciona à tela
                            li.appendChild(inputData);
                            li.appendChild(btnRemover);
                            ul.appendChild(li);
                        });
                    }
                )}


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
    })

    function formaDePagamento() {
        const semVinculo = document.getElementById('semVinculo')
        const mercadoPago = document.getElementById('mercadoPago')
        const aside = document.getElementById('tipoPagamento')
        const editar = document.getElementById('editar')
        const p = document.getElementById('pOculto')

        const chave = document.createElement('section')
        chave.id = 'campoPixOuToken' // id opcional para controle

        

        function inserirChave(html) {
            chave.innerHTML = html
            if (!aside.contains(chave)) {
                aside.appendChild(chave)
            }
        }

        semVinculo.addEventListener('click', function () {
            semVinculo.checked = true
            mercadoPago.checked = false
            inserirChave('')
            inserirChave.style.display = 'none'
        })

        mercadoPago.addEventListener('click', function () {
            semVinculo.checked = false
            mercadoPago.checked = true
            inserirChave(`
                <label for="pix_acesskey">Acrescente seu token do Mercado Pago</label>
                <input type="text" name="pix_acesskey" id="pix_acesskey" value="${pixAcesskeySalvo}"style="width: 500px"><strong id="olho">olho</strong>
                <div id="mensagem"></div>
            `)
            const olho = document.getElementById('olho')
            const ocultar_olho = document.getElementById('pix_acesskey') 
            olho.addEventListener('click', function () {
                if (ocultar_olho.type === 'text') {
                    ocultar_olho.type = 'password'
                } else {
                    ocultar_olho.type = 'text'
                }
            })
        })

        editar.addEventListener('click', function () {
            semVinculo.checked = false
            mercadoPago.checked = true
            p.textContent = inserirChave(`
                
                <input type="text" name="pix_acesskey" id="pix_acesskey" value="${pixAcesskeySalvo}" style="width: 500px"><strong id="olho">olho</strong>
               
            `)
            const olho = document.getElementById('olho')
            const ocultar_olho = document.getElementById('pix_acesskey') 
            
            olho.addEventListener('click', function () {
                if (ocultar_olho.type === 'text') {
                    ocultar_olho.type = 'password'
                } else {
                    ocultar_olho.type = 'text'
                }
            })
        })
    }

    document.addEventListener('DOMContentLoaded', formaDePagamento())

