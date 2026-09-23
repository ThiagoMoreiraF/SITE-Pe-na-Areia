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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/painel.css">
</head>
<body>
  <div class="painel-header">
    <div class="logo">
      <img src="/assets/img/penareia_logo.jpeg" alt="Logo">
      Painel · Grupo Pé na Areia
    </div>
    <nav>
      <a href="/painel/index.php">Imóveis</a>
      <a href="/index.php" target="_blank" rel="noopener">Ver site</a>
      <span>Olá, <?= h($_SESSION['painel_usuario_email'] ?? '') ?></span>
      <a href="/painel/logout.php" class="sair">Sair</a>
    </nav>
  </div>
  <div class="painel-container">
