<?php
require_once __DIR__ . '/includes/auth.php';
painelExigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCsrfToken($_POST['csrf_token'] ?? null)) {
    redirecionar('/painel/index.php');
}

$fotoId = (int) ($_POST['foto_id'] ?? 0);
$imovelId = (int) ($_POST['imovel_id'] ?? 0);

if ($fotoId > 0) {
    $stmt = $pdo->prepare('SELECT caminho_arquivo FROM fotos_imovel WHERE id = ? AND imovel_id = ?');
    $stmt->execute([$fotoId, $imovelId]);
    $foto = $stmt->fetch();

    if ($foto) {
        $pdo->prepare('DELETE FROM fotos_imovel WHERE id = ?')->execute([$fotoId]);
        $caminhoCompleto = UPLOAD_DIR . '/' . $foto['caminho_arquivo'];
        if (is_file($caminhoCompleto)) {
            unlink($caminhoCompleto);
        }
    }
}

redirecionar('/painel/imovel-form.php?id=' . $imovelId);
