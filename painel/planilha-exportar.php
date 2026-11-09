<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/planilha.php';
painelExigirLogin();

// Rótulo da coluna na planilha => coluna do banco. As colunas de "referência"
// servem só pra pessoa se localizar no Excel e são ignoradas na importação.
$colunas = [
    'Código'                     => 'codigo',
    'Título (referência)'        => 'titulo',
    'Cidade/Bairro (referência)' => 'cidade_bairro',
    'Status (referência)'        => 'status',
    'Rua'                        => 'endereco_rua',
    'Número'                     => 'endereco_numero',
    'Complemento'                => 'endereco_complemento',
    'CEP'                        => 'endereco_cep',
    'Proprietário'               => 'proprietario_nome',
    'Telefone'                   => 'proprietario_telefone',
    'Celular'                    => 'proprietario_celular',
    'E-mail'                     => 'proprietario_email',
    'Observações internas'       => 'obs_internas',
];

$imoveis = $pdo->query('SELECT ' . implode(', ', array_values($colunas)) . ' FROM imoveis ORDER BY id ASC')->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="imoveis-dados-internos-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store, no-cache, must-revalidate');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF"); // BOM: faz o Excel abrir os acentos corretamente
fputcsv($saida, array_keys($colunas), ';', '"', '');
foreach ($imoveis as $imovel) {
    fputcsv($saida, array_map(fn($coluna) => protegerCelulaCsv($imovel[$coluna] ?? ''), $colunas), ';', '"', '');
}
fclose($saida);
exit;
