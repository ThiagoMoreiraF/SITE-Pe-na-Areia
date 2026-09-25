<?php
/**
 * Criação do usuário administrador (uso único).
 * IMPORTANTE: depois de criar seu acesso, APAGUE este arquivo do servidor.
 * Enquanto ele existir, qualquer pessoa que descobrir a URL pode tentar acessá-lo
 * — mas ele só funciona se ainda não existir nenhum administrador cadastrado.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$stmtExiste = $pdo->query('SELECT COUNT(*) AS total FROM usuarios');
$jaExisteAdmin = (int) $stmtExiste->fetch()['total'] > 0;

$erro = '';
$sucesso = false;

if ($jaExisteAdmin) {
    $erro = 'Já existe um administrador cadastrado. Por segurança, apague o arquivo painel/setup.php do servidor.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrfToken($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada, atualize a página e tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } elseif (strlen($senha) < 8) {
            $erro = 'A senha deve ter pelo menos 8 caracteres.';
        } elseif ($senha !== $confirmar) {
            $erro = 'As senhas não conferem.';
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (email, senha_hash) VALUES (?, ?)');
            $stmt->execute([$email, $hash]);
            $sucesso = true;
        }
    }
}

$paginaTitulo = 'Configuração inicial - Painel Pé na Areia';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($paginaTitulo) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?= assetUrl('/assets/css/painel.css') ?>">
</head>
<body>
  <div class="login-box">
    <img src="<?= BASE_URL ?>/assets/img/penareia_logo.png" alt="Grupo Pé na Areia">
    <h1>Configuração inicial</h1>
    <div class="sub">Criação do usuário administrador (uso único)</div>

    <?php if ($erro): ?>
      <div class="alerta alerta-erro"><?= h($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
      <div class="alerta alerta-sucesso">
        Administrador criado com sucesso! Agora <strong>apague o arquivo painel/setup.php</strong> do servidor
        e acesse o painel normalmente.
      </div>
      <a href="<?= BASE_URL ?>/painel/login.php" class="btn btn-primary" style="display:block; text-align:center;">Ir para o login</a>
    <?php elseif (!$jaExisteAdmin): ?>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">
        <div class="form-grupo">
          <label for="email">E-mail de acesso</label>
          <input type="email" id="email" name="email" required autofocus>
        </div>
        <div class="form-grupo">
          <label for="senha">Senha (mínimo 8 caracteres)</label>
          <input type="password" id="senha" name="senha" minlength="8" required>
        </div>
        <div class="form-grupo">
          <label for="confirmar_senha">Confirmar senha</label>
          <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary">Criar administrador</button>
      </form>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>/index.php" class="voltar">← Voltar ao site</a>
  </div>
</body>
</html>
