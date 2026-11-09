<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/guia.php';
painelExigirLogin();

const GUIA_TITULO_MAX = 120;
const GUIA_TEXTO_MAX = 2000;

function voltarGuia(string $tipo, string $mensagem, string $ancora = ''): void
{
    $_SESSION['guia_msg'] = [$tipo, $mensagem];
    redirecionar('/painel/guia.php' . ($ancora !== '' ? '#' . $ancora : ''));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrfToken($_POST['csrf_token'] ?? null)) {
        voltarGuia('erro', 'Sessão expirada. Atualize a página e tente novamente.');
    }

    $padrao = guiaTopicosPadrao();

    try {
        $restaurar = $_POST['restaurar'] ?? null;
        if ($restaurar !== null) {
            if (isset($padrao[$restaurar])) {
                $pdo->prepare('DELETE FROM guia_topicos WHERE slug = ?')->execute([$restaurar]);
                voltarGuia('sucesso', 'Texto original de "' . $padrao[$restaurar]['titulo'] . '" restaurado.', $restaurar);
            }
            voltarGuia('erro', 'Tema não encontrado.');
        }

        $novos = [];
        foreach ($padrao as $slug => $topico) {
            $titulo = trim((string) ($_POST['titulo'][$slug] ?? ''));
            $texto = trim(str_replace("\r\n", "\n", (string) ($_POST['texto'][$slug] ?? '')));
            if ($titulo === '' || $texto === '') {
                voltarGuia('erro', 'O título e o texto de "' . $topico['titulo'] . '" não podem ficar vazios. Nada foi salvo.', $slug);
            }
            if (mb_strlen($titulo) > GUIA_TITULO_MAX || mb_strlen($texto) > GUIA_TEXTO_MAX) {
                voltarGuia('erro', 'O texto de "' . $topico['titulo'] . '" passou do limite de tamanho. Nada foi salvo.', $slug);
            }
            $novos[$slug] = [$titulo, $texto];
        }

        // Tema igual ao padrão não fica gravado no banco: assim continua "original".
        $pdo->beginTransaction();
        $grava = $pdo->prepare('INSERT INTO guia_topicos (slug, titulo, texto) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE titulo = VALUES(titulo), texto = VALUES(texto)');
        $apaga = $pdo->prepare('DELETE FROM guia_topicos WHERE slug = ?');
        foreach ($novos as $slug => [$titulo, $texto]) {
            if ($titulo === $padrao[$slug]['titulo'] && $texto === $padrao[$slug]['texto']) {
                $apaga->execute([$slug]);
            } else {
                $grava->execute([$slug, $titulo, $texto]);
            }
        }
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Erro ao salvar o guia: ' . $e->getMessage());
        voltarGuia('erro', 'Não foi possível salvar os textos. Confirme se a atualização do banco de dados (003_guia_topicos.sql) foi importada no phpMyAdmin.');
    }

    voltarGuia('sucesso', 'Textos do guia salvos — já estão no site.');
}

$mensagem = $_SESSION['guia_msg'] ?? null;
unset($_SESSION['guia_msg']);
$guia = carregarGuia($pdo);

$paginaTitulo = 'Guia & Orientações - Painel Pé na Areia';
require __DIR__ . '/includes/topo.php';
?>

  <div class="painel-titulo">
    <h1>Textos do Guia & Orientações</h1>
    <a href="<?= BASE_URL ?>/guia.php" target="_blank" rel="noopener" class="btn btn-secondary"><i class="fas fa-up-right-from-square"></i> Ver página no site</a>
  </div>

  <?php if ($mensagem): ?>
    <div class="alerta alerta-<?= $mensagem[0] === 'sucesso' ? 'sucesso' : 'erro' ?>"><?= h($mensagem[1]) ?></div>
  <?php endif; ?>

  <div class="alerta alerta-info">
    Edite os títulos e textos abaixo e clique em <strong>Salvar textos</strong>. Pra deixar uma palavra em negrito,
    coloque entre dois asteriscos: <code>**assim**</code>. Pule uma linha com Enter normalmente.
  </div>

  <form method="POST" action="<?= BASE_URL ?>/painel/guia.php">
    <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">

    <?php /* Primeiro botão de envio do formulário: é ele que o Enter aciona, nunca um "Restaurar". */ ?>
    <div class="guia-editor-salvar topo">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar textos</button>
    </div>

    <?php foreach ($guia as $slug => $topico): ?>
    <div class="card-painel guia-editor" id="<?= h($slug) ?>">
      <div class="guia-editor-capa" style="background-image: url('<?= BASE_URL ?>/assets/img/guia/<?= h($slug) ?>.jpg')">
        <span><i class="fas <?= h($topico['icone']) ?>"></i> <?= $slug === 'introducao' ? 'Introdução (topo da página)' : h(guiaTopicosPadrao()[$slug]['titulo']) ?></span>
        <em class="guia-editor-status <?= $topico['editado'] ? 'editado' : '' ?>"><?= $topico['editado'] ? 'Texto personalizado' : 'Texto original' ?></em>
      </div>

      <div class="form-grupo">
        <label for="titulo-<?= h($slug) ?>">Título</label>
        <input type="text" id="titulo-<?= h($slug) ?>" name="titulo[<?= h($slug) ?>]" required maxlength="<?= GUIA_TITULO_MAX ?>" value="<?= h($topico['titulo']) ?>">
      </div>
      <div class="form-grupo">
        <label for="texto-<?= h($slug) ?>">Texto</label>
        <textarea id="texto-<?= h($slug) ?>" name="texto[<?= h($slug) ?>]" rows="5" required maxlength="<?= GUIA_TEXTO_MAX ?>"><?= h($topico['texto']) ?></textarea>
      </div>

      <?php if ($topico['editado']): ?>
      <button type="submit" name="restaurar" value="<?= h($slug) ?>" formnovalidate class="btn btn-sm btn-secondary guia-restaurar"
              onclick="return confirm('Voltar este tema para o texto original? O texto que você escreveu será descartado.');">
        <i class="fas fa-rotate-left"></i> Restaurar texto original
      </button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="guia-editor-salvar">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar textos</button>
    </div>
  </form>

<?php require __DIR__ . '/includes/rodape.php'; ?>
