<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$stmt = $pdo->prepare(
    'SELECT i.id, i.codigo, i.titulo, i.finalidade, i.tipo, i.preco, i.observacoes,
            i.cidade_bairro, i.dormitorios, i.banheiros, i.vagas, i.destaque,
            (SELECT f.caminho_arquivo FROM fotos_imovel f WHERE f.imovel_id = i.id ORDER BY f.ordem ASC LIMIT 1) AS foto_capa
     FROM imoveis i
     WHERE i.status = "ativo"
     ORDER BY i.destaque DESC, i.created_at DESC'
);
$stmt->execute();
$imoveis = $stmt->fetchAll();

$resultado = array_map(function ($imovel) {
    return [
        'codigo'          => $imovel['codigo'],
        'titulo'          => $imovel['titulo'],
        'finalidade'      => $imovel['finalidade'],
        'finalidade_label'=> finalidadeLabel($imovel['finalidade']),
        'tipo'            => $imovel['tipo'],
        'tipo_label'      => tipoLabel($imovel['tipo']),
        'preco'           => (float) $imovel['preco'],
        'observacoes'     => $imovel['observacoes'],
        'cidade_bairro'   => $imovel['cidade_bairro'],
        'dormitorios'     => (int) $imovel['dormitorios'],
        'banheiros'       => (int) $imovel['banheiros'],
        'vagas'           => (int) $imovel['vagas'],
        'destaque'        => (bool) $imovel['destaque'],
        'foto_capa'       => $imovel['foto_capa'] ? UPLOAD_URL . '/' . $imovel['foto_capa'] : null,
    ];
}, $imoveis);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
