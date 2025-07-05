let acaoAtual = '';
let empresaIdAtual = null;

function toggleConteudo(id) {
  const el = document.getElementById(id);
  el.style.display = el.style.display === 'block' ? 'none' : 'block';
}

function solicitarSenha(acao, id) {
  acaoAtual = acao;
  empresaIdAtual = id;
  document.getElementById('senhaPrompt').style.display = 'block';
}

function cancelarSenha() {
  document.getElementById('senhaPrompt').style.display = 'none';
  document.getElementById('senhaInput').value = '';
}

function enviarSenha() {
  const senha = document.getElementById('senhaInput').value;

  if (!senha) {
    alert('Digite a senha.');
    return;
  }

  let url = '';

  if (acaoAtual === 'deletar') {
    url = 'deletar_empresa.php';
  } else if (acaoAtual === 'pausar') {
    url = 'pausar_empresa.php';
  } else if (acaoAtual === 'despausar') {
    url = 'despausar_empresa.php';
  }

  if (!url || empresaIdAtual === null) {
    alert('Ação inválida.');
    return;
  }

  const body = `id=${empresaIdAtual}&senha=${encodeURIComponent(senha)}`;

fetch(url, {
  method: 'POST',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: body
})
.then(res => res.json()) // ✅ direto pra JSON

.then(json => {
  if (json && json.sucesso) {
    const empresaEl = document.querySelector(`[data-empresa-id="${empresaIdAtual}"]`);

    if (acaoAtual === 'deletar') {
      if (empresaEl) empresaEl.remove();
      location.reload();
    }
    else if (empresaEl) {
      const tituloEl = empresaEl.querySelector('.titulo');

      if (acaoAtual === 'pausar') {
        // adiciona texto PAUSADA no título
        if (tituloEl && !tituloEl.innerHTML.includes('(PAUSADA)')) {
          tituloEl.innerHTML += ' <span style="color:red;">(PAUSADA)</span>';
        }

        // troca botão de pausar para despausar
        const btn = empresaEl.querySelector('button.deletar[onclick*="pausar"]');
        if (btn) {
          btn.textContent = 'Despausar';
          btn.setAttribute('onclick', `solicitarSenha('despausar', ${empresaIdAtual})`);
        }
      }
      else if (acaoAtual === 'despausar') {
        // remove texto PAUSADA do título
        if (tituloEl) {
          tituloEl.innerHTML = tituloEl.innerHTML.replace(/<span.*?<\/span>/, '').trim();
        }

        // troca botão de despausar para pausar
        const btn = empresaEl.querySelector('button.deletar[onclick*="despausar"]');
        if (btn) {
          btn.textContent = 'Pausar';
          btn.setAttribute('onclick', `solicitarSenha('pausar', ${empresaIdAtual})`);
        }
      }
    }
    cancelarSenha(); // fecha prompt
  }
  else if (json && json.erro) {
    alert(json.erro);
  }
  else {
    alert('Erro desconhecido.');
  }
})
.catch(err => {
  alert('Erro de rede ou servidor.');
  console.error(err);
});


  cancelarSenha();
}
