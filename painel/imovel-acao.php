<?php
require_once __DIR__ . '/includes/auth.php';
painelExigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCsrfToken($_POST['csrf_token'] ?? null)) {
    redirecionar('/painel/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$acao = $_POST['acao'] ?? '';

if ($id <= 0) {
    redirecionar('/painel/index.php');
}

if ($acao === 'toggle_destaque') {
    $pdo->prepare('UPDATE imoveis SET destaque = NOT destaque WHERE id = ?')->execute([$id]);
    redirecionar('/painel/index.php');
}

if ($acao === 'toggle_status') {
    $pdo->prepare("UPDATE imoveis SET status = IF(status = 'ativo', 'inativo', 'ativo') WHERE id = ?")->execute([$id]);
    redirecionar('/painel/index.php');
}

if ($acao === 'excluir') {
    $stmtFotos = $pdo->prepare('SELECT caminho_arquivo FROM fotos_imovel WHERE imovel_id = ?');
    $stmtFotos->execute([$id]);
    $fotos = array_column($stmtFotos->fetchAll(), 'caminho_arquivo');

    $pdo->prepare('DELETE FROM imoveis WHERE id = ?')->execute([$id]); // cascata remove fotos_imovel

    foreach ($fotos as $foto) {
        $caminhoCompleto = UPLOAD_DIR . '/' . $foto;
        if (is_file($caminhoCompleto)) {
            unlink($caminhoCompleto);
        }
    }
    $pastaImovel = UPLOAD_DIR . '/' . $id;
    if (is_dir($pastaImovel)) {
        @rmdir($pastaImovel);
    }

    redirecionar('/painel/index.php?msg=excluido');
}

redirecionar('/painel/index.php');
