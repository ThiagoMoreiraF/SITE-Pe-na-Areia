/* Revista digital "Conheça a Baixada Santista" — efeito de folhear com StPageFlip.
   Sem JavaScript (ou se a biblioteca falhar), as páginas aparecem em grade, uma após a outra. */
document.addEventListener('DOMContentLoaded', () => {
  const livro = document.getElementById('revista-livro');
  if (!livro || !window.St || !window.St.PageFlip) return;

  const controles = document.querySelector('.revista-controles');
  const contador = document.getElementById('revista-contador');
  const btnAmpliar = controles.querySelector('[data-acao="ampliar"]');
  const zoom = document.getElementById('revista-zoom');
  const zoomPaginas = zoom.querySelector('.revista-zoom-paginas');
  const paginas = Array.from(livro.querySelectorAll('.revista-pagina'));

  const revista = new St.PageFlip(livro, {
    width: 600,
    height: 890,
    size: 'stretch',
    minWidth: 260,
    maxWidth: 700,
    minHeight: 385,
    maxHeight: 1040,
    showCover: true,
    usePortrait: true,
    mobileScrollSupport: true,
    maxShadowOpacity: 0.45,
    flippingTime: 800,
  });
  revista.loadFromHTML(paginas);
  livro.classList.add('revista-pronta');
  controles.hidden = false;

  const total = revista.getPageCount();

  // Páginas visíveis agora: no celular uma; no computador a capa sozinha e depois pares (1-2, 3-4...).
  function paginasVisiveis() {
    const atual = revista.getCurrentPageIndex();
    if (revista.getOrientation() === 'portrait' || atual === 0) return [atual];
    const esquerda = atual % 2 === 1 ? atual : atual - 1;
    return [esquerda, esquerda + 1].filter(p => p < total);
  }

  function paginasDeCidade() {
    return paginasVisiveis().map(i => paginas[i]).filter(p => p && p.classList.contains('revista-pagina-cidade'));
  }

  function atualizar() {
    const visiveis = paginasVisiveis();
    contador.textContent = visiveis.length > 1
      ? `Páginas ${visiveis[0] + 1}–${visiveis[1] + 1} de ${total}`
      : `Página ${visiveis[0] + 1} de ${total}`;
    btnAmpliar.disabled = paginasDeCidade().length === 0;
  }

  function abrirZoom() {
    const cidades = paginasDeCidade();
    if (cidades.length === 0) return;
    zoomPaginas.replaceChildren(...cidades.map(p => {
      const img = p.querySelector('img').cloneNode();
      img.removeAttribute('style');
      return img;
    }));
    zoom.showModal();
    zoom.scrollTop = 0;
  }

  revista.on('flip', atualizar);
  revista.on('changeOrientation', atualizar);
  revista.on('init', atualizar);
  atualizar();

  controles.addEventListener('click', e => {
    const botao = e.target.closest('[data-acao]');
    if (!botao) return;
    const acao = botao.dataset.acao;
    if (acao === 'anterior') revista.flipPrev();
    if (acao === 'proxima') revista.flipNext();
    if (acao === 'sumario') revista.flip(1);
    if (acao === 'ampliar') abrirZoom();
  });

  livro.addEventListener('click', e => {
    const item = e.target.closest('[data-ir-para]');
    if (item) revista.flip(Number(item.dataset.irPara));
  });

  document.addEventListener('keydown', e => {
    if (zoom.open || e.target.closest('input, textarea, select')) return;
    if (e.key === 'ArrowRight') revista.flipNext();
    if (e.key === 'ArrowLeft') revista.flipPrev();
  });

  zoom.addEventListener('click', e => {
    if (e.target === zoom || e.target.closest('[data-acao="fechar-zoom"]')) zoom.close();
  });
});
