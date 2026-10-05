<?php
/**
 * Cabeçalho comum das páginas públicas.
 * Espera opcionalmente $paginaTitulo e $paginaDescricao definidas antes do include.
 */
require_once __DIR__ . '/functions.php';

$paginaTitulo = $paginaTitulo ?? 'Grupo Pé na Areia | Imobiliária em Mongaguá e Litoral Sul - SP';
$paginaDescricao = $paginaDescricao ?? 'Imobiliária em Mongaguá, Praia Grande e Itanhaém. Casas, apartamentos, kitnets e terrenos à venda e para alugar no Litoral Sul de SP. Consultoria completa com o Grupo Pé na Areia.';
$paginaUrl = $paginaUrl ?? absoluteUrl($_SERVER['REQUEST_URI'] ?? '');
$ogImagem = $ogImagem ?? absoluteUrl('/assets/img/penareia_logo.png');
$ogTipo = $ogTipo ?? 'website';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= h($paginaDescricao) ?>">
  <title><?= h($paginaTitulo) ?></title>

  <link rel="canonical" href="<?= h($paginaUrl) ?>">

  <meta property="og:type" content="<?= h($ogTipo) ?>">
  <meta property="og:site_name" content="Grupo Pé na Areia">
  <meta property="og:locale" content="pt_BR">
  <meta property="og:title" content="<?= h($paginaTitulo) ?>">
  <meta property="og:description" content="<?= h($paginaDescricao) ?>">
  <meta property="og:image" content="<?= h($ogImagem) ?>">
  <meta property="og:url" content="<?= h($paginaUrl) ?>">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= h($paginaTitulo) ?>">
  <meta name="twitter:description" content="<?= h($paginaDescricao) ?>">
  <meta name="twitter:image" content="<?= h($ogImagem) ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
  <script>window.BASE_URL = "<?= BASE_URL ?>";</script>
</head>
<body>

  <header>
    <div>
      <a href="<?= BASE_URL ?>/index.php"><img src="<?= BASE_URL ?>/assets/img/penareia_logo.png" alt="Logo Grupo Pé na Areia" class="header-logo"></a>
    </div>

    <div class="header-center">
      <h1 class="header-title">Grupo Pé na Areia</h1>
      <div class="header-subtitle">Consultoria e Assessoria Imobiliária</div>
    </div>

    <div class="header-contact">
      <p>📱 <strong>WhatsApp:</strong> <a href="https://wa.me/<?= h(WHATSAPP_PRINCIPAL) ?>" target="_blank" rel="noopener">(13) 98838-1441</a></p>
      <p>✉️ <strong>E-mail:</strong> <a href="mailto:<?= h(EMAIL_CONTATO) ?>"><?= h(EMAIL_CONTATO) ?></a></p>
    </div>
  </header>

  <div class="header-division-bar">
    <nav class="header-nav">
      <a href="<?= BASE_URL ?>/index.php"<?= (basename($_SERVER['SCRIPT_NAME']) === 'index.php') ? ' class="active"' : '' ?>>Início</a>
      <a href="<?= BASE_URL ?>/quem-somos.php"<?= (basename($_SERVER['SCRIPT_NAME']) === 'quem-somos.php') ? ' class="active"' : '' ?>>Quem Somos</a>
    </nav>
    <span>Litoral Sul – SP | Mongaguá, Itanhaém, Praia Grande e regiões.</span>
  </div>

  <div class="container">
