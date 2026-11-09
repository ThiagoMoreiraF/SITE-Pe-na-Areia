<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/planilha.php';
painelExigirLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['planilha_relatorio'] = importarPlanilha($pdo, $_FILES['planilha'] ?? null, $_POST['csrf_token'] ?? null);
    redirecionar('/painel/planilha.php');
}

$relatorio = $_SESSION['planilha_relatorio'] ?? null;
unset($_SESSION['planilha_relatorio']);

$limiteAvisos = 50;

$paginaTitulo = 'Planilha de dados internos - Painel Pé na Areia';
require __DIR__ . '/includes/topo.php';
?>

  <div class="painel-titulo">
    <h1>Planilha de dados internos</h1>
    <a href="<?= BASE_URL ?>/painel/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
  </div>

  <?php if ($relatorio !== null): ?>
    <?php if ($relatorio['erro'] !== null): ?>
      <div class="alerta alerta-erro"><?= h($relatorio['erro']) ?></div>
    <?php else: ?>
      <div class="alerta alerta-sucesso">
        Importação concluída: <strong><?= (int) $relatorio['linhas'] ?></strong> linha(s) lida(s),
        <strong><?= (int) $relatorio['atualizados'] ?></strong> imóvel(is) atualizado(s)<?php if ($relatorio['sem_alteracao'] > 0): ?>,
        <?= (int) $relatorio['sem_alteracao'] ?> já estava(m) igual(is)<?php endif; ?>.
      </div>
    <?php endif; ?>

    <?php if (!empty($relatorio['avisos'])): ?>
      <div class="card-painel relatorio-avisos">
        <h3><i class="fas fa-triangle-exclamation"></i> Pontos de atenção (<?= count($relatorio['avisos']) ?>)</h3>
        <ul>
          <?php foreach (array_slice($relatorio['avisos'], 0, $limiteAvisos) as $aviso): ?>
            <li><?= h($aviso) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php if (count($relatorio['avisos']) > $limiteAvisos): ?>
          <p class="ajuda-texto">… e mais <?= count($relatorio['avisos']) - $limiteAvisos ?> aviso(s).</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="card-painel">
    <p class="planilha-intro">
      <i class="fas fa-lock"></i> Aqui você atualiza de uma vez o endereço completo, os dados do proprietário e as
      observações internas dos imóveis. <strong>Nada disso aparece no site</strong> — fica visível só aqui no painel.
    </p>

    <ol class="planilha-passos">
      <li>
        <strong>Baixe a planilha modelo.</strong> Ela já vem com todos os imóveis cadastrados e os dados internos atuais.
        <div><a href="<?= BASE_URL ?>/painel/planilha-exportar.php" class="btn btn-primary"><i class="fas fa-file-arrow-down"></i> Baixar planilha (Excel / CSV)</a></div>
      </li>
      <li>
        <strong>Abra no Excel e preencha</strong> as colunas de endereço, proprietário e observações.
        Não altere a coluna <em>Código</em> — é por ela que o sistema sabe qual imóvel atualizar.
        As colunas marcadas como <em>(referência)</em> são só pra você se localizar e não são importadas.
      </li>
      <li>
        <strong>Salve como CSV</strong> (no Excel: <em>Arquivo → Salvar como → tipo CSV</em>). Qualquer opção de CSV funciona.
      </li>
      <li>
        <strong>Envie o arquivo abaixo.</strong>
        <form method="POST" action="<?= BASE_URL ?>/painel/planilha.php" enctype="multipart/form-data" class="planilha-upload">
          <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">
          <input type="file" name="planilha" accept=".csv,.txt" required>
          <button type="submit" class="btn btn-primary"><i class="fas fa-file-arrow-up"></i> Importar planilha</button>
        </form>
      </li>
    </ol>

    <div class="planilha-regras">
      <h4>Como a importação funciona</h4>
      <ul>
        <li>Só <strong>atualiza</strong> imóveis que já existem no painel — nunca cria nem apaga imóvel.</li>
        <li>Célula vazia <strong>não apaga</strong> o que já estava salvo. Pra apagar um dado, edite o imóvel pelo painel.</li>
        <li>Se um CEP ou e-mail estiver em formato errado, só aquele campo é ignorado e você vê o aviso aqui em cima.</li>
        <li>Já tem sua planilha antiga? Basta acrescentar uma coluna <em>Código</em> com o código de cada imóvel (ex: PNA-00012).
          O sistema reconhece colunas com títulos como Rua, Número, Complemento, CEP, Proprietário, Telefone, Celular, E-mail e Obs.</li>
      </ul>
    </div>
  </div>

<?php require __DIR__ . '/includes/rodape.php'; ?>
