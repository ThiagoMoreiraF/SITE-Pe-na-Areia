<?php
require_once __DIR__ . '/includes/auth.php';
painelExigirLogin();

$stmt = $pdo->query('SELECT id, codigo, titulo, finalidade, tipo, preco, cidade_bairro, destaque, status FROM imoveis ORDER BY created_at DESC');
$imoveis = $stmt->fetchAll();

$mensagem = $_GET['msg'] ?? '';

$paginaTitulo = 'Imóveis - Painel Pé na Areia';
require __DIR__ . '/includes/topo.php';
?>

  <div class="painel-titulo">
    <h1>Imóveis cadastrados</h1>
    <a href="<?= BASE_URL ?>/painel/imovel-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Novo imóvel</a>
  </div>

  <?php if ($mensagem === 'salvo'): ?>
    <div class="alerta alerta-sucesso">Imóvel salvo com sucesso.</div>
  <?php elseif ($mensagem === 'excluido'): ?>
    <div class="alerta alerta-sucesso">Imóvel excluído com sucesso.</div>
  <?php endif; ?>

  <div class="card-painel">
    <?php if (empty($imoveis)): ?>
      <p>Nenhum imóvel cadastrado ainda.</p>
    <?php else: ?>
    <div class="tabela-scroll">
    <table class="tabela-imoveis">
      <thead>
        <tr>
          <th>Código</th>
          <th>Título</th>
          <th>Tipo</th>
          <th>Finalidade</th>
          <th>Preço</th>
          <th>Cidade/Bairro</th>
          <th>Destaque</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($imoveis as $imovel): ?>
        <tr>
          <td><span class="codigo-tag"><?= h($imovel['codigo']) ?></span></td>
          <td><?= h($imovel['titulo']) ?></td>
          <td><?= h(tipoLabel($imovel['tipo'])) ?></td>
          <td><?= h(finalidadeLabel($imovel['finalidade'])) ?></td>
          <td><?= h(formatarPreco((float) $imovel['preco'], $imovel['finalidade'])) ?></td>
          <td><?= h($imovel['cidade_bairro']) ?></td>
          <td>
            <form method="POST" action="<?= BASE_URL ?>/painel/imovel-acao.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $imovel['id'] ?>">
              <input type="hidden" name="acao" value="toggle_destaque">
              <button type="submit" class="btn btn-sm btn-secondary <?= $imovel['destaque'] ? 'destaque-sim' : '' ?>">
                <?= $imovel['destaque'] ? '★ Sim' : '☆ Não' ?>
              </button>
            </form>
          </td>
          <td>
            <form method="POST" action="<?= BASE_URL ?>/painel/imovel-acao.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $imovel['id'] ?>">
              <input type="hidden" name="acao" value="toggle_status">
              <button type="submit" class="btn btn-sm btn-secondary <?= $imovel['status'] === 'ativo' ? 'status-ativo' : 'status-inativo' ?>">
                <?= $imovel['status'] === 'ativo' ? 'Ativo' : 'Inativo' ?>
              </button>
            </form>
          </td>
          <td class="acoes">
            <a href="<?= BASE_URL ?>/painel/imovel-form.php?id=<?= (int) $imovel['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <form method="POST" action="<?= BASE_URL ?>/painel/imovel-acao.php" onsubmit="return confirm('Tem certeza que deseja excluir este imóvel e todas as fotos dele? Essa ação não pode ser desfeita.');" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $imovel['id'] ?>">
              <input type="hidden" name="acao" value="excluir">
              <button type="submit" class="btn btn-sm btn-perigo">Excluir</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>

<?php require __DIR__ . '/includes/rodape.php'; ?>
