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

function redirecionar(string $destino): void
{
    header('Location: ' . BASE_URL . $destino);
    exit;
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
