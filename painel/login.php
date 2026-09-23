<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (painelJaLogado()) {
    redirecionar('/painel/index.php');
}

function painelJaLogado(): bool
{
    return !empty($_SESSION['painel_usuario_id']);
}

$erro = '';
$expirado = isset($_GET['expirado']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrfToken($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão expirada, atualize a página e tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            usleep(300000); // reduz diferença de tempo entre "usuário existe" e "não existe"
            $erro = 'E-mail ou senha inválidos.';
        } elseif ($usuario['bloqueado_ate'] && strtotime($usuario['bloqueado_ate']) > time()) {
            $minutosRestantes = ceil((strtotime($usuario['bloqueado_ate']) - time()) / 60);
            $erro = "Muitas tentativas erradas. Tente novamente em {$minutosRestantes} minuto(s).";
        } elseif (!password_verify($senha, $usuario['senha_hash'])) {
            $tentativas = $usuario['tentativas_falhas'] + 1;
            if ($tentativas >= LOGIN_MAX_TENTATIVAS) {
                $bloqueadoAte = date('Y-m-d H:i:s', time() + LOGIN_BLOQUEIO_MINUTOS * 60);
                $stmt = $pdo->prepare('UPDATE usuarios SET tentativas_falhas = 0, bloqueado_ate = ? WHERE id = ?');
                $stmt->execute([$bloqueadoAte, $usuario['id']]);
                $erro = "Muitas tentativas erradas. Sua conta foi bloqueada por " . LOGIN_BLOQUEIO_MINUTOS . " minutos.";
            } else {
                $stmt = $pdo->prepare('UPDATE usuarios SET tentativas_falhas = ? WHERE id = ?');
                $stmt->execute([$tentativas, $usuario['id']]);
                $erro = 'E-mail ou senha inválidos.';
            }
        } else {
            $stmt = $pdo->prepare('UPDATE usuarios SET tentativas_falhas = 0, bloqueado_ate = NULL WHERE id = ?');
            $stmt->execute([$usuario['id']]);

            session_regenerate_id(true);
            $_SESSION['painel_usuario_id'] = $usuario['id'];
            $_SESSION['painel_usuario_email'] = $usuario['email'];
            $_SESSION['painel_ultimo_acesso'] = time();

            redirecionar('/painel/index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acesso ao Painel - Grupo Pé na Areia</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/painel.css">
</head>
<body>
  <div class="login-box">
    <img src="/assets/img/penareia_logo.jpeg" alt="Grupo Pé na Areia">
    <h1>Grupo Pé na Areia</h1>
    <div class="sub">Painel administrativo</div>

    <?php if ($expirado): ?>
      <div class="alerta alerta-info">Sua sessão expirou por inatividade. Faça login novamente.</div>
    <?php endif; ?>
    <?php if ($erro): ?>
      <div class="alerta alerta-erro"><?= h($erro) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= h(gerarCsrfToken()) ?>">
      <div class="form-grupo">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required autofocus>
      </div>
      <div class="form-grupo">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>
      </div>
      <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <a href="/index.php" class="voltar">← Voltar ao site</a>
  </div>
</body>
</html>
