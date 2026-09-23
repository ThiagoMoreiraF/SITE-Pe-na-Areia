<?php
/**
 * Cabeçalho comum das páginas públicas.
 * Espera opcionalmente $paginaTitulo e $paginaDescricao definidas antes do include.
 */
require_once __DIR__ . '/functions.php';

$paginaTitulo = $paginaTitulo ?? 'Grupo Pé na Areia - Portal Imobiliário';
$paginaDescricao = $paginaDescricao ?? 'Grupo Pé na Areia - Consultoria e Assessoria Imobiliária no Litoral Sul de São Paulo (Mongaguá, Itanhaém, Praia Grande).';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= h($paginaDescricao) ?>">
  <title><?= h($paginaTitulo) ?></title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

  <header>
    <div>
      <a href="/index.php"><img src="/assets/img/penareia_logo.jpeg" alt="Logo Grupo Pé na Areia" class="header-logo"></a>
    </div>

    <div class="header-center">
      <h1 class="header-title">Grupo Pé na Areia</h1>
      <div class="header-subtitle">Consultoria e Assessoria Imobiliária</div>
    </div>

    <div class="header-contact">
      <p>📱 <strong>WhatsApp:</strong> <a href="https://wa.me/<?= h(WHATSAPP_PRINCIPAL) ?>" target="_blank" rel="noopener">(13) 98838-1441</a></p>
      <p>🌐 <strong>Site:</strong> <a href="<?= h(SITE_URL) ?>" target="_blank" rel="noopener">grupopenaareia.com.br</a></p>
      <p>✉️ <strong>E-mail:</strong> <a href="mailto:<?= h(EMAIL_CONTATO) ?>"><?= h(EMAIL_CONTATO) ?></a></p>
    </div>
  </header>

  <div class="header-division-bar">
    Litoral Sul – SP | Mongaguá, Itanhaém, Praia Grande e regiões.
  </div>

  <div class="container">
