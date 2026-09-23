/* Grupo Pé na Areia - JS do site público (listagem, filtros, busca por código, simulador, guia) */

let todosImoveis = [];
let tipoAtual = '';
let finalidadeAtual = '';

function escapeHtml(texto) {
  const div = document.createElement('div');
  div.textContent = texto ?? '';
  return div.innerHTML;
}

function formatarPrecoJS(preco, finalidade) {
  const valor = Number(preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  return finalidade === 'aluguel' ? valor + '/mês' : valor;
}

function selecionarTipo(tipo, btn) {
  tipoAtual = tipo;
  document.querySelectorAll('.btn-type').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  aplicarFiltros();
}

function selecionarFinalidade(finalidade, btn) {
  finalidadeAtual = finalidade;
  document.querySelectorAll('.btn-finalidade').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  aplicarFiltros();
}

function renderizarCard(imovel) {
  const foto = imovel.foto_capa || '/assets/img/sem-foto.svg';
  const badgeTipo = escapeHtml(imovel.tipo_label);
  const badgeFinalidade = escapeHtml(imovel.finalidade_label);
  const mensagem = `Olá, tenho interesse no imóvel ${imovel.codigo} - ${imovel.titulo} (${imovel.cidade_bairro})`;

  return `
    <div class="card-imovel">
      <a href="/imovel.php?codigo=${encodeURIComponent(imovel.codigo)}" class="card-img-wrapper">
        <span class="card-badge">${badgeTipo} · ${badgeFinalidade}</span>
        <span class="card-codigo">${escapeHtml(imovel.codigo)}</span>
        <img src="${foto}" alt="${escapeHtml(imovel.titulo)}" loading="lazy">
      </a>
      <div class="card-body">
        <div class="card-price">${formatarPrecoJS(imovel.preco, imovel.finalidade)}</div>
        <div class="card-title-text"><a href="/imovel.php?codigo=${encodeURIComponent(imovel.codigo)}">${escapeHtml(imovel.titulo)}</a></div>
        <div class="card-location"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(imovel.cidade_bairro)}</div>
        <div class="card-details">
          <span><i class="fas fa-bed"></i> ${imovel.dormitorios} dorm.</span>
          <span><i class="fas fa-bath"></i> ${imovel.banheiros} banh.</span>
          <span><i class="fas fa-car"></i> ${imovel.vagas} vaga(s)</span>
        </div>
        ${imovel.observacoes ? `<div class="card-tags">${escapeHtml(imovel.observacoes)}</div>` : '<div class="card-tags"></div>'}
        <div class="card-actions">
          <a href="/imovel.php?codigo=${encodeURIComponent(imovel.codigo)}" class="btn-detalhes-card">Ver detalhes</a>
          <a href="https://wa.me/5513988381441?text=${encodeURIComponent(mensagem)}" target="_blank" rel="noopener" class="btn-whatsapp-card">
            <i class="fab fa-whatsapp"></i> Tenho Interesse
          </a>
        </div>
      </div>
    </div>
  `;
}

function aplicarFiltros() {
  const cidadeFiltro = (document.getElementById('filter-city')?.value || '').toLowerCase();
  const dormitoriosFiltro = document.getElementById('filter-bedrooms')?.value || '';
  const grid = document.getElementById('imoveis-grid');
  if (!grid) return;

  grid.innerHTML = '<p style="text-align: center; width: 100%; grid-column: 1 / -1; font-size: 16px; color: #0284c7;"><i class="fas fa-spinner fa-spin"></i> Atualizando...</p>';

  const filtrados = todosImoveis.filter(imovel => {
    const matchTipo = tipoAtual === '' || imovel.tipo === tipoAtual;
    const matchFinalidade = finalidadeAtual === '' || imovel.finalidade === finalidadeAtual;
    const matchCidade = cidadeFiltro === '' || imovel.cidade_bairro.toLowerCase().includes(cidadeFiltro) || imovel.titulo.toLowerCase().includes(cidadeFiltro);
    const matchDorm = dormitoriosFiltro === '' || (dormitoriosFiltro === '3' ? imovel.dormitorios >= 3 : imovel.dormitorios === Number(dormitoriosFiltro));
    return matchTipo && matchFinalidade && matchCidade && matchDorm;
  });

  if (filtrados.length === 0) {
    grid.innerHTML = '<p style="text-align: center; width: 100%; grid-column: 1 / -1; color: #64748b;">Nenhum imóvel encontrado com esses filtros.</p>';
    return;
  }

  grid.innerHTML = filtrados.map(renderizarCard).join('');
}

function limparFiltros() {
  tipoAtual = '';
  finalidadeAtual = '';
  document.querySelectorAll('.btn-type').forEach((b, idx) => b.classList.toggle('active', idx === 0));
  document.querySelectorAll('.btn-finalidade').forEach((b, idx) => b.classList.toggle('active', idx === 0));
  const cidade = document.getElementById('filter-city');
  const dorm = document.getElementById('filter-bedrooms');
  if (cidade) cidade.value = '';
  if (dorm) dorm.value = '';
  aplicarFiltros();
}

function buscarPorCodigo(event) {
  event.preventDefault();
  const campo = document.getElementById('busca-codigo-input');
  const valor = (campo?.value || '').trim();
  if (!valor) return;
  window.location.href = '/imovel.php?codigo=' + encodeURIComponent(valor);
}

async function carregarImoveis() {
  const grid = document.getElementById('imoveis-grid');
  try {
    const resp = await fetch('/api/imoveis.php');
    if (!resp.ok) throw new Error('Falha ao carregar imóveis');
    todosImoveis = await resp.json();
    aplicarFiltros();
  } catch (erro) {
    console.error(erro);
    if (grid) {
      grid.innerHTML = '<p style="text-align: center; width: 100%; grid-column: 1 / -1; color: #b91c1c;">Não foi possível carregar os imóveis agora. Tente novamente em instantes.</p>';
    }
  }
}

// Controle do Guia (Abas) no rodapé
let abaAtual = 0;
const totalAbas = 6;

function abrirAba(indice) {
  abaAtual = indice;
  const abas = document.querySelectorAll('.tab-content');
  const botoes = document.querySelectorAll('.tab-btn');

  abas.forEach((aba, i) => aba.classList.toggle('active', i === indice));
  botoes.forEach((btn, i) => btn.classList.toggle('active', i === indice));

  const indicador = document.getElementById('guide-indicator');
  if (indicador) indicador.innerText = `Página ${indice + 1} de ${totalAbas}`;
}

function mudarAba(direcao) {
  abaAtual += direcao;
  if (abaAtual < 0) abaAtual = totalAbas - 1;
  if (abaAtual >= totalAbas) abaAtual = 0;
  abrirAba(abaAtual);
}

// Envio do formulário do proprietário direto para o WhatsApp
function enviarProprietarioWhats(e) {
  e.preventDefault();
  const nome = document.getElementById('prop-nome').value;
  const whats = document.getElementById('prop-whats').value;
  const tipo = document.getElementById('prop-tipo').value;
  const cidade = document.getElementById('prop-cidade').value;
  const obs = document.getElementById('prop-obs').value;

  if (!nome || !whats || !tipo || !cidade) {
    alert('Por favor, preencha os campos obrigatórios do formulário.');
    return;
  }

  const texto = `Olá! Quero cadastrar um imóvel para venda/gestão:\n\n*Nome:* ${nome}\n*WhatsApp:* ${whats}\n*Tipo:* ${tipo}\n*Cidade/Bairro:* ${cidade}\n*Detalhes:* ${obs}`;
  const url = `https://wa.me/5513988381441?text=${encodeURIComponent(texto)}`;
  window.open(url, '_blank');
}

// Simulador de Financiamento
function mascaraMoeda(input) {
  let valor = input.value.replace(/\D/g, '');
  valor = (Number(valor) / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  input.value = valor.replace('R$', '').trim();
}

function calcularFinanciamento() {
  const vImovelStr = document.getElementById('sim-val-imovel').value.replace(/\./g, '').replace(',', '.');
  const vEntradaStr = document.getElementById('sim-val-entrada').value.replace(/\./g, '').replace(',', '.');
  const vImovel = parseFloat(vImovelStr) || 0;
  const vEntrada = parseFloat(vEntradaStr) || 0;
  const prazoAnos = parseInt(document.getElementById('sim-prazo').value) || 30;
  const taxaAnual = parseFloat(document.getElementById('sim-taxa').value) || 0;
  const sistema = document.getElementById('sim-sistema').value;

  if (vImovel <= 0) {
    alert('Informe o valor do imóvel.');
    return;
  }

  const vFinanciado = vImovel - vEntrada;
  if (vFinanciado <= 0) {
    alert('O valor da entrada não pode ser maior ou igual ao valor do imóvel.');
    return;
  }

  const meses = prazoAnos * 12;
  const taxaMensal = (taxaAnual / 100) / 12;
  let p1 = 0;
  let pUlt = 0;

  if (sistema === 'SAC') {
    const amortizacao = vFinanciado / meses;
    const jurosP1 = vFinanciado * taxaMensal;
    p1 = amortizacao + jurosP1;
    const saldoDevedorUltima = amortizacao;
    pUlt = amortizacao + (saldoDevedorUltima * taxaMensal);
    document.getElementById('box-p-ultima').style.display = 'block';
  } else {
    // PRICE - se a taxa for 0%, a parcela é simplesmente o valor financiado dividido pelos meses
    if (taxaMensal === 0) {
      p1 = vFinanciado / meses;
    } else {
      p1 = vFinanciado * (taxaMensal * Math.pow(1 + taxaMensal, meses)) / (Math.pow(1 + taxaMensal, meses) - 1);
    }
    pUlt = p1;
    document.getElementById('box-p-ultima').style.display = 'none';
  }

  const itbiCartorio = vImovel * 0.04;

  document.getElementById('res-financiado').innerText = vFinanciado.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  document.getElementById('res-p1').innerText = p1.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  document.getElementById('res-p-ult').innerText = pUlt.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  document.getElementById('res-itbi').innerText = itbiCartorio.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

  document.getElementById('sim-line-4').style.display = 'grid';
  document.getElementById('sim-line-5').style.display = 'block';
}

// Carrossel de fotos da página de detalhe do imóvel
let carrosselIndice = 0;

function trocarFotoCarrossel(direcao) {
  const container = document.getElementById('carrossel-imagens');
  if (!container) return;
  const imagens = container.querySelectorAll('img');
  if (imagens.length === 0) return;

  imagens[carrosselIndice].classList.remove('ativa');
  carrosselIndice = (carrosselIndice + direcao + imagens.length) % imagens.length;
  imagens[carrosselIndice].classList.add('ativa');

  const contador = document.getElementById('carrossel-contador');
  if (contador) contador.innerText = `${carrosselIndice + 1} / ${imagens.length}`;
}

window.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('imoveis-grid')) {
    carregarImoveis();
  }
});
