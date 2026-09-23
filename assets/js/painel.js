/* Grupo Pé na Areia - JS do painel administrativo */

document.addEventListener('DOMContentLoaded', () => {
  const inputFotos = document.getElementById('fotos');
  if (inputFotos) {
    inputFotos.addEventListener('change', () => {
      const total = inputFotos.files.length;
      let aviso = document.getElementById('fotos-selecionadas-aviso');
      if (!aviso) {
        aviso = document.createElement('span');
        aviso.id = 'fotos-selecionadas-aviso';
        aviso.className = 'ajuda-texto';
        inputFotos.insertAdjacentElement('afterend', aviso);
      }
      aviso.textContent = total > 0 ? `${total} arquivo(s) selecionado(s).` : '';
    });
  }
});
