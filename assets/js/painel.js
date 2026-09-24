/* Grupo Pé na Areia - JS do painel administrativo */

// Máscara de moeda BR (mesma lógica do simulador do site público) para os
// campos Preço/IPTU/Condomínio — evita o bug de digitar "1.450.000,00" num
// input numérico comum, que corta o valor no primeiro ponto extra.
function mascaraMoeda(input) {
  let valor = input.value.replace(/\D/g, '');
  valor = (Number(valor) / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  input.value = valor;
}

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
