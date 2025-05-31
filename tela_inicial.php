<?php
    include 'php/conexao.php';

    $config = $conn->query("SELECT * FROM servico")->fetch_assoc();


    $servico = $config['tipo_servico'];
    $valor = $config['valor'];
    $qtFuncionarios = $config['quantidade_de_funcionarios'];
    $duracao = $config['duracao_servico'];
    $intervalo = $config['intervalo_entre_servico'];
    extract($config);

    echo "$servico"
    

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento</title>
    <link rel="stylesheet" href="estilo/style.css">

</head>
<body>
    <h1>ola seja bem vindo</h1>
    <p>como podemos ajudar?</p>

    <main id="form">
        <form action="php/agendamento.php" method="post">
            <section id="pessoal">
                <h2>Nos informe algumas informações pessoais</h2>
                <div>
                    <label for="nome">Nome: </label>
                    <input type="text" name="nome" id="nome">
                </div>
                <div>
                    <label for="sobrenome">Sobrenome: </label>
                    <input type="text" name="sobrenome" id="sobrenome">
                </div>
                <div>
                    <label for="nascimento">Data de nascimento: </label>
                    <input type="date" name="nascimento" id="nascimento">
                </div>
                <div>
                    <label for="email">E-mail: </label>
                    <input type="email" name="email" id="email">
                </div>
                <div>
                    <label for="cpf">CPF: </label>
                    <input type="text" name="cpf" id="cpf">
                </div>
                <div>
                    <label for="cll">Celular: </label>
                    <input type="tel" name="cll" id="cll">
                </div>
            </section>
            <section id="tipoServico">
                <h2>tipos de serviçoes que deseja</h2>
                <div>
                    <label for="servico">qual tipo de serviço voce gostaria</label>
                    <select name="servico" id="servico">
                        <option value="opção 1"><?= $servico ?></option>
                        <option value="opção 2"><?= $servico ?></option>
                        <option value="opção 3"><?= $servico ?></option>
                        <option value="opção 4"><?= $servico ?></option>
                    </select>
                </div>
                <div>
                    <label for="duracao">quantas cessão que deseja: </label>
                    <select name="duracao" id="duracao">
                        <option value="1">1 Hora</option>
                        <option value="2">2 Hora</option>
                        <option value="3">3 Hora</option>
                    </select>
                </div>
                <div>
                    <label for="dia">Para qual dia gostaria de agendar? </label>
                    <input type="date" id="dataSelecionada" name="dia" readonly placeholder="__/__/__">

                    <select name="mes" id="mesSelect">
                        <option value="1">janeiro</option>
                        <option value="2">fevereiro</option>
                        <option value="3">março</option>
                        <option value="4">abril</option>
                        <option value="5">maio</option>
                        <option value="6">junho</option>
                        <option value="7">julho</option>
                        <option value="8">agosto</option>
                        <option value="9">setembro</option>
                        <option value="10">outubro</option>
                        <option value="11">novembro</option>
                        <option value="12">dezembro</option>
                    </select>

                    <table id="dataDisponiveis">
                        <p>dias disponiveis neste mes de <strong class="mes">...</strong></p>
                        <tbody>
                            <tr>
                                <td id="1" class="data">1</td>
                                <td id="2" class="data">2</td>
                                <td id="3" class="data">3</td>
                                <td id="4" class="data">4</td>
                                <td id="5" class="data">5</td>
                                <td id="6" class="data">6</td>
                                <td id="7" class="data">7</td>
                            </tr>
                            <tr>
                                <td id="8" class="data">8</td>
                                <td id="9" class="data">9</td>
                                <td id="10" class="data">10</td>
                                <td id="11" class="data">11</td>
                                <td id="12" class="data">12</td>
                                <td id="13" class="data">13</td>
                                <td id="14" class="data">14</td>
                            </tr>
                            <tr>
                                <td id="15" class="data">15</td>
                                <td id="16" class="data">16</td>
                                <td id="17" class="data">17</td>
                                <td id="18" class="data">18</td>
                                <td id="19" class="data">19</td>
                                <td id="20" class="data">20</td>
                                <td id="21" class="data">21</td>
                            </tr>
                            <tr>
                                <td id="22" class="data">22</td>
                                <td id="23" class="data">23</td>
                                <td id="24" class="data">24</td>
                                <td id="25" class="data">25</td>
                                <td id="26" class="data">26</td>
                                <td id="27" class="data">27</td>
                                <td id="28" class="data">28</td>
                            </tr>
                            <tr>
                                <td id="29" class="data">29</td>
                                <td id="30" class="data">30</td>
                                <td id="31" class="data">31</td>
                            </tr>

                        </tbody>
                    </table>
                    
                </div>
                <div>
                    <label for="hora">Qual horario melhor para você? </label>
                    <input type="time" name="hora" id="hora" readonly placeholder="__:__">
                    <table id="horarios">
                        <thead>
                            <th>horas disponiveis</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="hora1">13:00</td>
                            </tr>
                            <tr>
                                <td id="hora2">14:00</td>
                            </tr>
                            <tr>
                                <td id="hora3">15:00</td>
                            </tr>
                            <tr>
                                <td id="hora4">16:00</td>
                            </tr>
                            <tr>
                                <td id="hora5">17:00</td>
                            </tr>
                            <tr>
                                <td id="hora6">18:00</td>
                            </tr>
                            <tr>
                                <td id="hora7">19:00</td>
                            </tr>
                            <tr>
                                <td id="hora8">20:00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <button type="submit">agendar</button>
            </section>
        </form>
    </main>

    <script src="javaScript/script.js"></script>
    
</body>
</html>