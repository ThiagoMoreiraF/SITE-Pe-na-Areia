<?php
/**
 * Funções auxiliares usadas no site público e no painel.
 */

/** Escapa saída HTML de forma curta e segura. */
function h(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/** Tipos de imóvel aceitos (valor salvo no banco => rótulo exibido). */
function tiposImovel(): array
{
    return [
        'casa'        => 'Casa',
        'sobrado'     => 'Sobrado',
        'apartamento' => 'Apartamento',
        'kitnet'      => 'Kitnet',
        'chacara'     => 'Chácara',
        'comercial'   => 'Comercial',
        'terreno'     => 'Terreno',
    ];
}

/** Ícone Font Awesome para cada tipo (mantém o padrão visual do site do cliente). */
function tipoIcone(string $tipo): string
{
    $icones = [
        'casa'        => 'fa-house',
        'sobrado'     => 'fa-building-user',
        'apartamento' => 'fa-building',
        'kitnet'      => 'fa-door-open',
        'chacara'     => 'fa-tree',
        'comercial'   => 'fa-store',
        'terreno'     => 'fa-map',
    ];
    return $icones[$tipo] ?? 'fa-house';
}

function tipoLabel(string $tipo): string
{
    return tiposImovel()[$tipo] ?? ucfirst($tipo);
}

function finalidadeLabel(string $finalidade): string
{
    return $finalidade === 'aluguel' ? 'Aluguel' : 'Venda';
}

/** Formata preço em R$, acrescentando "/mês" para aluguel. */
function formatarPreco(float $valor, string $finalidade): string
{
    $formatado = 'R$ ' . number_format($valor, 2, ',', '.');
    return $finalidade === 'aluguel' ? $formatado . '/mês' : $formatado;
}

function formatarMoeda(?float $valor): ?string
{
    if ($valor === null) {
        return null;
    }
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/** Formata um número (sem "R$") no padrão BR, pra preencher campos com máscara de moeda. */
function formatarNumeroBr(?float $valor): string
{
    if ($valor === null) {
        return '';
    }
    return number_format($valor, 2, ',', '.');
}

/**
 * Converte um valor digitado no padrão BR (ex: "1.450.000,00") pra float.
 * Retorna null se vazio ou inválido — nunca use is_numeric() direto num campo
 * de moeda digitado por brasileiro, porque "1.450.000,00" não é numérico em PHP/JS.
 */
function parseMoedaBr(string $valor): ?float
{
    $valor = trim($valor);
    if ($valor === '') {
        return null;
    }
    $normalizado = str_replace('.', '', $valor);
    $normalizado = str_replace(',', '.', $normalizado);
    return is_numeric($normalizado) ? (float) $normalizado : null;
}

/**
 * Gera o código legível do imóvel (ex: PNA-00001) a partir do ID numérico.
 * Deve ser chamada DEPOIS do INSERT, usando o lastInsertId(), para evitar
 * condições de corrida entre cadastros simultâneos.
 */
function gerarCodigo(int $id): string
{
    return 'PNA-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
}

/** Normaliza um código digitado pelo usuário (busca rápida) para o formato PNA-00001. */
function normalizarCodigo(string $entrada): string
{
    $entrada = strtoupper(trim($entrada));
    $entrada = preg_replace('/[^A-Z0-9-]/', '', $entrada);
    if ($entrada !== '' && strpos($entrada, 'PNA-') !== 0) {
        $numeros = preg_replace('/\D/', '', $entrada);
        if ($numeros !== '') {
            $entrada = 'PNA-' . str_pad($numeros, 5, '0', STR_PAD_LEFT);
        }
    }
    return $entrada;
}

/**
 * Monta a URL de um arquivo estático (CSS/JS/imagem) com um parâmetro de versão
 * baseado na data de modificação do arquivo, pra forçar o navegador a buscar
 * a versão nova sempre que o arquivo mudar (evita cache antigo no celular).
 */
function assetUrl(string $caminhoRelativo): string
{
    $caminhoAbsoluto = dirname(__DIR__) . $caminhoRelativo;
    $versao = is_file($caminhoAbsoluto) ? filemtime($caminhoAbsoluto) : time();
    return BASE_URL . $caminhoRelativo . '?v=' . $versao;
}

function redirecionar(string $destino): void
{
    header('Location: ' . BASE_URL . $destino);
    exit;
}

/**
 * Comprime e redimensiona uma imagem recém-enviada usando GD, pra não estourar
 * o espaço em disco da hospedagem. Se o GD não estiver disponível no servidor,
 * retorna false (o chamador deve então salvar o arquivo original sem comprimir
 * — nunca travar o cadastro por causa disso).
 */
function comprimirImagem(string $caminhoOrigem, string $caminhoDestino, string $mimeOriginal): bool
{
    if (!extension_loaded('gd')) {
        return false;
    }

    switch ($mimeOriginal) {
        case 'image/jpeg':
            $origem = @imagecreatefromjpeg($caminhoOrigem);
            break;
        case 'image/png':
            $origem = @imagecreatefrompng($caminhoOrigem);
            break;
        case 'image/webp':
            $origem = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($caminhoOrigem) : false;
            break;
        default:
            $origem = false;
    }

    if ($origem === false) {
        return false;
    }

    $larguraOriginal = imagesx($origem);
    $alturaOriginal = imagesy($origem);

    if ($larguraOriginal > UPLOAD_LARGURA_MAXIMA) {
        $novaLargura = UPLOAD_LARGURA_MAXIMA;
        $novaAltura = (int) round($alturaOriginal * ($novaLargura / $larguraOriginal));
        $final = imagecreatetruecolor($novaLargura, $novaAltura);

        if ($mimeOriginal === 'image/png') {
            imagealphablending($final, false);
            imagesavealpha($final, true);
        }

        imagecopyresampled($final, $origem, 0, 0, 0, 0, $novaLargura, $novaAltura, $larguraOriginal, $alturaOriginal);
        imagedestroy($origem);
    } else {
        $final = $origem;
    }

    $sucesso = ($mimeOriginal === 'image/png')
        ? imagepng($final, $caminhoDestino, 6)
        : imagejpeg($final, $caminhoDestino, UPLOAD_QUALIDADE_JPEG);

    imagedestroy($final);

    return $sucesso;
}

/**
 * Converte um caminho relativo ao site (que já pode incluir o BASE_URL local,
 * tipo /pena-areia) numa URL absoluta correta usando o domínio de produção
 * (SITE_URL) — usado em og:image, og:url, canonical etc, que o Facebook/Google
 * precisam receber como URL completa e correta mesmo quando testamos localmente.
 */
function absoluteUrl(string $caminho): string
{
    if (BASE_URL !== '' && strpos($caminho, BASE_URL) === 0) {
        $caminho = substr($caminho, strlen(BASE_URL));
    }
    return rtrim(SITE_URL, '/') . $caminho;
}

/** Extrai o ID de um vídeo do YouTube a partir de vários formatos de URL possíveis. */
function extrairYoutubeId(string $url): ?string
{
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $url, $m)) {
        return $m[1];
    }
    return null;
}

/** Extensão do arquivo final após a compressão (PNG permanece PNG, o resto vira JPEG). */
function extensaoComprimida(string $mimeOriginal): string
{
    return $mimeOriginal === 'image/png' ? 'png' : 'jpg';
}

/** Gera (ou reaproveita) o token CSRF da sessão atual. */
function gerarCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCsrfToken(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
