<?php
/** Cabeçalho comum das páginas internas do painel. Requer painelExigirLogin() já chamado. */
$paginaTitulo = $paginaTitulo ?? 'Painel - Grupo Pé na Areia';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= h($paginaTitulo) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/painel.css">
</head>
<body>
  <div class="painel-header">
    <div class="logo">
      <img src="<?= BASE_URL ?>/assets/img/penareia_logo.png" alt="Logo">
      Painel · Grupo Pé na Areia
    </div>
    <nav>
      <a href="<?= BASE_URL ?>/painel/index.php">Imóveis</a>
      <a href="<?= BASE_URL ?>/index.php" target="_blank" rel="noopener">Ver site</a>
      <span>Olá, <?= h($_SESSION['painel_usuario_email'] ?? '') ?></span>
      <a href="<?= BASE_URL ?>/painel/logout.php" class="sair">Sair</a>
    </nav>
  </div>
  <div class="painel-container">
