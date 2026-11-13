<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Páginas da revista, em ordem geográfica pelo litoral (nordeste -> sudoeste).
// Para trocar a ordem ou incluir uma cidade, basta mexer nesta lista.
$cidades = [
    ['arquivo' => '01-guaruja.jpg',        'cidade' => 'Guarujá',        'regiao' => ''],
    ['arquivo' => '02-santos.jpg',         'cidade' => 'Santos',         'regiao' => ''],
    ['arquivo' => '03-canto-do-forte.jpg', 'cidade' => 'Canto do Forte', 'regiao' => 'Praia Grande'],
    ['arquivo' => '04-boqueirao.jpg',      'cidade' => 'Boqueirão',      'regiao' => 'Praia Grande'],
    ['arquivo' => '05-guilhermina.jpg',    'cidade' => 'Guilhermina',    'regiao' => 'Praia Grande'],
    ['arquivo' => '06-caicara.jpg',        'cidade' => 'Caiçara',        'regiao' => 'Praia Grande'],
    ['arquivo' => '07-mongagua.jpg',       'cidade' => 'Mongaguá',       'regiao' => ''],
    ['arquivo' => '08-itanhaem.jpg',       'cidade' => 'Itanhaém',       'regiao' => ''],
    ['arquivo' => '09-peruibe.jpg',        'cidade' => 'Peruíbe',        'regiao' => ''],
];
const REVISTA_PRIMEIRA_CIDADE = 2; // índice da página: 0 = capa, 1 = sumário

$mensagemWhats = 'Olá! Vim pela revista "Conheça a Baixada Santista" do site e quero encontrar um imóvel no litoral.';

$paginaTitulo = 'Conheça a Baixada Santista | Revista Digital - Grupo Pé na Areia';
$paginaDescricao = 'Revista digital com as cidades da Baixada Santista e Litoral Sul de SP: Guarujá, Santos, Praia Grande, Mongaguá, Itanhaém e Peruíbe. Qualidade de vida, pontos turísticos e infraestrutura.';
$ogImagem = absoluteUrl('/assets/img/revista/07-mongagua.jpg');
require __DIR__ . '/includes/header.php';
?>

    <div class="revista-cabecalho">
      <h2><i class="fas fa-book-open"></i> Conheça a Baixada Santista</h2>
      <p>Folheie nossa revista digital e conheça cada cidade do litoral: qualidade de vida, pontos turísticos e infraestrutura.</p>
    </div>

    <div class="revista-palco">
      <div id="revista-livro" class="revista-livro">

        <div class="revista-pagina revista-capa" data-density="hard">
          <div class="revista-capa-conteudo">
            <img src="<?= BASE_URL ?>/assets/img/penareia_logo.png" alt="Grupo Pé na Areia" class="revista-capa-logo">
            <span class="revista-capa-selo">Revista Digital</span>
            <h3>Conheça a<br>Baixada Santista</h3>
            <p>Um passeio pelas cidades do litoral de São Paulo</p>
            <span class="revista-capa-dica"><i class="fas fa-hand-pointer"></i> Arraste a página ou use as setas para folhear</span>
          </div>
        </div>

        <div class="revista-pagina revista-sumario">
          <div class="revista-sumario-conteudo">
            <h3>Nesta edição</h3>
            <ol>
              <?php foreach ($cidades as $i => $c): ?>
              <li>
                <button type="button" data-ir-para="<?= REVISTA_PRIMEIRA_CIDADE + $i ?>">
                  <span class="sumario-cidade"><?= h($c['cidade']) ?><?php if ($c['regiao']): ?> <small><?= h($c['regiao']) ?></small><?php endif; ?></span>
                  <span class="sumario-pontilhado"></span>
                  <span class="sumario-pagina"><?= REVISTA_PRIMEIRA_CIDADE + $i + 1 ?></span>
                </button>
              </li>
              <?php endforeach; ?>
            </ol>
            <p class="sumario-dica">Toque numa cidade para ir direto até ela.</p>
          </div>
        </div>

        <?php foreach ($cidades as $c): ?>
        <div class="revista-pagina revista-pagina-cidade">
          <img src="<?= h(assetUrl('/assets/img/revista/' . $c['arquivo'])) ?>"
               alt="Conheça <?= h($c['cidade'] . ($c['regiao'] ? ' - ' . $c['regiao'] : '')) ?>: qualidade de vida, pontos turísticos e infraestrutura"
               decoding="async">
        </div>
        <?php endforeach; ?>

        <div class="revista-pagina revista-capa revista-contracapa" data-density="hard">
          <div class="revista-capa-conteudo">
            <img src="<?= BASE_URL ?>/assets/img/penareia_logo.png" alt="Grupo Pé na Areia" class="revista-capa-logo">
            <h3>Gostou de alguma cidade?</h3>
            <p>Encontre o imóvel ideal no litoral com quem conhece cada bairro da região.</p>
            <a href="https://wa.me/<?= h(WHATSAPP_PRINCIPAL) ?>?text=<?= urlencode($mensagemWhats) ?>" target="_blank" rel="noopener" class="btn-whatsapp-card revista-contracapa-whats">
              <i class="fab fa-whatsapp"></i> Falar no WhatsApp
            </a>
            <small>CRECI/SP <?= h(CRECI) ?></small>
          </div>
        </div>

      </div>
    </div>

    <div class="revista-controles" hidden>
      <button type="button" class="revista-btn" data-acao="anterior" aria-label="Página anterior"><i class="fas fa-chevron-left"></i> <span>Anterior</span></button>
      <button type="button" class="revista-btn revista-btn-secundario" data-acao="sumario" aria-label="Ir para o sumário"><i class="fas fa-list"></i> <span>Sumário</span></button>
      <span class="revista-contador" id="revista-contador" aria-live="polite"></span>
      <button type="button" class="revista-btn revista-btn-secundario" data-acao="ampliar" aria-label="Ampliar página para leitura"><i class="fas fa-magnifying-glass-plus"></i> <span>Ampliar</span></button>
      <button type="button" class="revista-btn" data-acao="proxima" aria-label="Próxima página"><span>Próxima</span> <i class="fas fa-chevron-right"></i></button>
    </div>

    <dialog id="revista-zoom" class="revista-zoom" aria-label="Página ampliada">
      <button type="button" class="revista-zoom-fechar" data-acao="fechar-zoom" aria-label="Fechar"><i class="fas fa-xmark"></i></button>
      <div class="revista-zoom-paginas"></div>
    </dialog>

    <script src="<?= assetUrl('/assets/js/vendor/page-flip.browser.js') ?>" defer></script>
    <script src="<?= assetUrl('/assets/js/revista.js') ?>" defer></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
