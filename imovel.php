<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$codigo = normalizarCodigo($_GET['codigo'] ?? '');

$imovel = null;
if ($codigo !== '') {
    $stmt = $pdo->prepare('SELECT * FROM imoveis WHERE codigo = ? AND status = "ativo" LIMIT 1');
    $stmt->execute([$codigo]);
    $imovel = $stmt->fetch();
}

if (!$imovel) {
    $paginaTitulo = 'Imóvel não encontrado - Grupo Pé na Areia';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="detalhe-card" style="padding: 40px; text-align: center;">
      <h2 style="color:#1e293b; margin-bottom: 10px;">Imóvel não encontrado</h2>
      <p style="color:#64748b; margin-bottom: 20px;">Verifique se o código foi digitado corretamente, ou o imóvel pode não estar mais disponível.</p>
      <a href="/index.php" class="btn btn-primary">Voltar para a lista de imóveis</a>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmtFotos = $pdo->prepare('SELECT caminho_arquivo FROM fotos_imovel WHERE imovel_id = ? ORDER BY ordem ASC LIMIT 10');
$stmtFotos->execute([$imovel['id']]);
$fotos = array_column($stmtFotos->fetchAll(), 'caminho_arquivo');

$paginaTitulo = h($imovel['titulo']) . ' (' . h($imovel['codigo']) . ') - Grupo Pé na Areia';
$paginaDescricao = mb_substr($imovel['descricao'], 0, 155);
require __DIR__ . '/includes/header.php';

$mensagemWhats = 'Olá, tenho interesse no imóvel ' . $imovel['codigo'] . ' - ' . $imovel['titulo'] . ' (' . $imovel['cidade_bairro'] . ')';
?>

    <div class="detalhe-topo">
      <a href="/index.php" class="detalhe-voltar"><i class="fas fa-arrow-left"></i> Voltar para a lista de imóveis</a>
      <span class="detalhe-codigo"><?= h($imovel['codigo']) ?></span>
    </div>

    <div class="detalhe-card">
      <div class="carrossel">
        <?php if (!empty($fotos)): ?>
        <div class="carrossel-imagens" id="carrossel-imagens">
          <?php foreach ($fotos as $i => $foto): ?>
            <img src="<?= h(UPLOAD_URL . '/' . $foto) ?>" alt="Foto <?= $i + 1 ?> - <?= h($imovel['titulo']) ?>" class="<?= $i === 0 ? 'ativa' : '' ?>">
          <?php endforeach; ?>
        </div>
        <?php if (count($fotos) > 1): ?>
          <button class="carrossel-nav prev" onclick="trocarFotoCarrossel(-1)" aria-label="Foto anterior"><i class="fas fa-chevron-left"></i></button>
          <button class="carrossel-nav next" onclick="trocarFotoCarrossel(1)" aria-label="Próxima foto"><i class="fas fa-chevron-right"></i></button>
          <span class="carrossel-contador" id="carrossel-contador">1 / <?= count($fotos) ?></span>
        <?php endif; ?>
        <?php else: ?>
        <div class="carrossel-imagens">
          <img src="/assets/img/sem-foto.svg" alt="Sem foto disponível" class="ativa">
        </div>
        <?php endif; ?>
      </div>

      <div class="detalhe-body">
        <div class="card-badge" style="position:static; display:inline-block; margin-bottom:10px;">
          <?= h(tipoLabel($imovel['tipo'])) ?> · <?= h(finalidadeLabel($imovel['finalidade'])) ?>
        </div>
        <h2 class="detalhe-titulo"><?= h($imovel['titulo']) ?></h2>
        <div class="detalhe-local"><i class="fas fa-map-marker-alt"></i> <?= h($imovel['cidade_bairro']) ?></div>
        <div class="detalhe-preco"><?= h(formatarPreco((float) $imovel['preco'], $imovel['finalidade'])) ?></div>

        <div class="detalhe-specs">
          <span><i class="fas fa-bed"></i> <?= (int) $imovel['dormitorios'] ?> dormitório(s)</span>
          <span><i class="fas fa-bath"></i> <?= (int) $imovel['banheiros'] ?> banheiro(s)</span>
          <span><i class="fas fa-car"></i> <?= (int) $imovel['vagas'] ?> vaga(s)</span>
          <?php if ($imovel['aceita_financiamento']): ?><span><i class="fas fa-hand-holding-dollar"></i> Aceita financiamento</span><?php endif; ?>
          <?php if ($imovel['aceita_permuta']): ?><span><i class="fas fa-right-left"></i> Aceita permuta</span><?php endif; ?>
        </div>

        <div class="detalhe-valores">
          <?php if ($imovel['tipo'] === 'apartamento' && $imovel['condominio'] !== null): ?>
            <div class="detalhe-valor-box">Condomínio<strong><?= h(formatarMoeda((float) $imovel['condominio'])) ?></strong></div>
          <?php endif; ?>
          <?php if ($imovel['iptu'] !== null): ?>
            <div class="detalhe-valor-box">IPTU<strong><?= h(formatarMoeda((float) $imovel['iptu'])) ?></strong></div>
          <?php endif; ?>
        </div>

        <p class="detalhe-descricao"><?= nl2br(h($imovel['descricao'])) ?></p>

        <div class="detalhe-cta">
          <a href="https://wa.me/<?= h(WHATSAPP_PRINCIPAL) ?>?text=<?= urlencode($mensagemWhats) ?>" target="_blank" rel="noopener" class="btn-whatsapp-card">
            <i class="fab fa-whatsapp"></i> Tenho Interesse — Falar no WhatsApp
          </a>
        </div>
      </div>
    </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
