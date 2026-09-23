<?php
require_once __DIR__ . '/includes/auth.php';
painelExigirLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$imovel = null;
$fotos = [];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM imoveis WHERE id = ?');
    $stmt->execute([$id]);
    $imovel = $stmt->fetch();
    if (!$imovel) {
        redirecionar('/painel/index.php');
    }
    $stmtFotos = $pdo->prepare('SELECT id, caminho_arquivo FROM fotos_imovel WHERE imovel_id = ? ORDER BY ordem ASC');
    $stmtFotos->execute([$id]);
    $fotos = $stmtFotos->fetchAll();
}

$edicao = $imovel !== null;
$erro = $_SESSION['form_erro'] ?? null;
unset($_SESSION['form_erro']);

$paginaTitulo = ($edicao ? 'Editar imóvel' : 'Novo imóvel') . ' - Painel Pé na Areia';
require __DIR__ . '/includes/topo.php';
?>

  <div class="painel-titulo">
    <h1><?= $edicao ? 'Editar imóvel' : 'Novo imóvel' ?></h1>
    <a href="/painel/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
  </div>

  <?php if ($erro): ?>
    <div class="alerta alerta-erro"><?= h($erro) ?></div>
  <?php endif; ?>

  <?php if ($edicao): ?>
    <div class="codigo-gerado"><i class="fas fa-tag"></i> Código: <?= h($imovel['codigo']) ?></div>
  <?php else: ?>
    <div class="alerta alerta-info">O código do imóvel (ex: PNA-00001) é gerado automaticamente ao salvar.</div>
  <?php endif; ?>

  <div class="card-painel">
    <form method="POST" action="/painel/imovel-salvar.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">
      <?php if ($edicao): ?>
        <input type="hidden" name="id" value="<?= (int) $imovel['id'] ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-grupo">
          <label for="titulo">Título do anúncio *</label>
          <input type="text" id="titulo" name="titulo" required maxlength="150" value="<?= h($imovel['titulo'] ?? '') ?>" placeholder="Ex: Apto a 50m do mar">
        </div>
        <div class="form-grupo">
          <label for="finalidade">Finalidade *</label>
          <select id="finalidade" name="finalidade" required>
            <option value="venda" <?= ($imovel['finalidade'] ?? '') === 'venda' ? 'selected' : '' ?>>Venda</option>
            <option value="aluguel" <?= ($imovel['finalidade'] ?? '') === 'aluguel' ? 'selected' : '' ?>>Aluguel</option>
          </select>
        </div>
        <div class="form-grupo">
          <label for="tipo">Tipo *</label>
          <select id="tipo" name="tipo" required onchange="alternarCampoCondominio()">
            <?php foreach (tiposImovel() as $valor => $rotulo): ?>
              <option value="<?= h($valor) ?>" <?= ($imovel['tipo'] ?? '') === $valor ? 'selected' : '' ?>><?= h($rotulo) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grupo">
          <label for="preco">Preço (R$) *</label>
          <input type="number" id="preco" name="preco" required min="0" step="0.01" value="<?= h($imovel['preco'] ?? '') ?>">
        </div>
        <div class="form-grupo">
          <label for="iptu">IPTU (R$)</label>
          <input type="number" id="iptu" name="iptu" min="0" step="0.01" value="<?= h($imovel['iptu'] ?? '') ?>">
        </div>
        <div class="form-grupo" id="campo-condominio">
          <label for="condominio">Condomínio (R$)</label>
          <input type="number" id="condominio" name="condominio" min="0" step="0.01" value="<?= h($imovel['condominio'] ?? '') ?>">
          <span class="ajuda-texto">Só aparece para imóveis do tipo Apartamento.</span>
        </div>
        <div class="form-grupo">
          <label for="cidade_bairro">Cidade / Bairro *</label>
          <input type="text" id="cidade_bairro" name="cidade_bairro" required maxlength="150" value="<?= h($imovel['cidade_bairro'] ?? '') ?>" placeholder="Ex: Aviação - Praia Grande / SP">
        </div>
        <div class="form-grupo">
          <label for="dormitorios">Dormitórios</label>
          <input type="number" id="dormitorios" name="dormitorios" min="0" max="20" value="<?= h($imovel['dormitorios'] ?? '0') ?>">
        </div>
        <div class="form-grupo">
          <label for="banheiros">Banheiros</label>
          <input type="number" id="banheiros" name="banheiros" min="0" max="20" value="<?= h($imovel['banheiros'] ?? '0') ?>">
        </div>
        <div class="form-grupo">
          <label for="vagas">Vagas de garagem</label>
          <input type="number" id="vagas" name="vagas" min="0" max="20" value="<?= h($imovel['vagas'] ?? '0') ?>">
        </div>
        <div class="form-grupo">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="ativo" <?= ($imovel['status'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo (visível no site)</option>
            <option value="inativo" <?= ($imovel['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo (oculto)</option>
          </select>
        </div>
      </div>

      <div class="form-grupo" style="margin-bottom: 16px;">
        <label for="observacoes">Observações curtas (aparece no card da listagem)</label>
        <input type="text" id="observacoes" name="observacoes" maxlength="200" value="<?= h($imovel['observacoes'] ?? '') ?>" placeholder="Ex: Aceita financiamento, próximo à orla">
      </div>

      <div class="form-grupo" style="margin-bottom: 16px;">
        <label for="descricao">Descrição completa *</label>
        <textarea id="descricao" name="descricao" rows="6" required><?= h($imovel['descricao'] ?? '') ?></textarea>
      </div>

      <div class="form-grid" style="margin-bottom: 16px;">
        <div class="form-grupo checkbox">
          <input type="checkbox" id="aceita_financiamento" name="aceita_financiamento" <?= !empty($imovel['aceita_financiamento']) ? 'checked' : '' ?>>
          <label for="aceita_financiamento">Aceita financiamento</label>
        </div>
        <div class="form-grupo checkbox">
          <input type="checkbox" id="aceita_permuta" name="aceita_permuta" <?= !empty($imovel['aceita_permuta']) ? 'checked' : '' ?>>
          <label for="aceita_permuta">Aceita permuta</label>
        </div>
        <div class="form-grupo checkbox">
          <input type="checkbox" id="destaque" name="destaque" <?= !empty($imovel['destaque']) ? 'checked' : '' ?>>
          <label for="destaque">Marcar como destaque</label>
        </div>
      </div>

      <?php if ($edicao && !empty($fotos)): ?>
      <div class="form-grupo" style="margin-bottom: 16px;">
        <label>Fotos atuais</label>
        <div class="fotos-preview">
          <?php foreach ($fotos as $foto): ?>
          <div class="foto-item">
            <img src="<?= h(UPLOAD_URL . '/' . $foto['caminho_arquivo']) ?>" alt="Foto do imóvel">
            <form method="POST" action="/painel/foto-excluir.php" onsubmit="return confirm('Remover esta foto?');">
              <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">
              <input type="hidden" name="foto_id" value="<?= (int) $foto['id'] ?>">
              <input type="hidden" name="imovel_id" value="<?= (int) $imovel['id'] ?>">
              <button type="submit" class="remover-foto" title="Remover foto"><i class="fas fa-times"></i></button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
        <span class="ajuda-texto"><?= count($fotos) ?> de <?= UPLOAD_MAX_FOTOS ?> fotos usadas.</span>
      </div>
      <?php endif; ?>

      <div class="form-grupo" style="margin-bottom: 20px;">
        <label for="fotos">Adicionar fotos (JPG, PNG ou WEBP, até <?= UPLOAD_MAX_TAMANHO_MB ?>MB cada)</label>
        <input type="file" id="fotos" name="fotos[]" accept=".jpg,.jpeg,.png,.webp" multiple>
        <span class="ajuda-texto">Máximo de <?= UPLOAD_MAX_FOTOS ?> fotos por imóvel no total.</span>
      </div>

      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar imóvel</button>
    </form>
  </div>

  <script>
    function alternarCampoCondominio() {
      const tipo = document.getElementById('tipo').value;
      document.getElementById('campo-condominio').style.display = (tipo === 'apartamento') ? 'flex' : 'none';
    }
    document.addEventListener('DOMContentLoaded', alternarCampoCondominio);
  </script>

<?php require __DIR__ . '/includes/rodape.php'; ?>
