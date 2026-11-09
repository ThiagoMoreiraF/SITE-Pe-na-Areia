<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/guia.php';

$guia = carregarGuia($pdo);
$introducao = $guia['introducao'];
unset($guia['introducao']);

$paginaTitulo = 'Guia & Orientações | Grupo Pé na Areia - Imobiliária em Mongaguá';
$paginaDescricao = 'Orientações para comprar, vender, alugar, avaliar, passar o ponto ou permutar imóveis no Litoral Sul de SP, com o Grupo Pé na Areia.';
require __DIR__ . '/includes/header.php';
?>

    <div class="guia-hero" style="--guia-fundo: url('<?= BASE_URL ?>/assets/img/guia/introducao.jpg')">
      <h2><i class="fas <?= h($introducao['icone']) ?>"></i> <?= h($introducao['titulo']) ?></h2>
      <p><?= formatarTextoGuia($introducao['texto']) ?></p>
    </div>

    <div class="guia-grid">
      <?php foreach ($guia as $slug => $topico): ?>
      <div class="guia-card" style="--guia-fundo: url('<?= BASE_URL ?>/assets/img/guia/<?= h($slug) ?>.jpg')">
        <h3><i class="fas <?= h($topico['icone']) ?>"></i> <?= h($topico['titulo']) ?></h3>
        <p><?= formatarTextoGuia($topico['texto']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="guia-cta">
      <span>Ficou com alguma dúvida? Nossa equipe te orienta.</span>
      <a href="https://wa.me/<?= h(WHATSAPP_PRINCIPAL) ?>?text=<?= urlencode('Olá! Vim pelo Guia & Orientações do site e gostaria de tirar uma dúvida.') ?>" target="_blank" rel="noopener" class="btn-whatsapp-card">
        <i class="fab fa-whatsapp"></i> Falar no WhatsApp
      </a>
    </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
