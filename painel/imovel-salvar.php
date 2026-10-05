<?php
require_once __DIR__ . '/includes/auth.php';
painelExigirLogin();

function voltarComErro(string $mensagem, int $id = 0): void
{
    $_SESSION['form_erro'] = $mensagem;
    redirecionar('/painel/imovel-form.php' . ($id > 0 ? '?id=' . $id : ''));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('/painel/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

if (!validarCsrfToken($_POST['csrf_token'] ?? null)) {
    voltarComErro('Sessão expirada, tente novamente.', $id);
}

$titulo = trim($_POST['titulo'] ?? '');
$finalidade = $_POST['finalidade'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$preco = parseMoedaBr($_POST['preco'] ?? '');
$iptu = parseMoedaBr($_POST['iptu'] ?? '');
$condominio = parseMoedaBr($_POST['condominio'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$cidadeBairro = trim($_POST['cidade_bairro'] ?? '');
$dormitorios = (int) ($_POST['dormitorios'] ?? 0);
$banheiros = (int) ($_POST['banheiros'] ?? 0);
$vagas = (int) ($_POST['vagas'] ?? 0);
$aceitaFinanciamento = isset($_POST['aceita_financiamento']) ? 1 : 0;
$aceitaPermuta = isset($_POST['aceita_permuta']) ? 1 : 0;
$destaque = isset($_POST['destaque']) ? 1 : 0;
$status = ($_POST['status'] ?? 'ativo') === 'inativo' ? 'inativo' : 'ativo';
$youtubeUrl = trim($_POST['youtube_url'] ?? '');
$driveUrl = trim($_POST['drive_url'] ?? '');

if ($titulo === '' || $descricao === '' || $cidadeBairro === '') {
    voltarComErro('Preencha os campos obrigatórios: título, descrição e cidade/bairro.', $id);
}
if (!in_array($finalidade, ['aluguel', 'venda'], true)) {
    voltarComErro('Selecione uma finalidade válida.', $id);
}
if (!array_key_exists($tipo, tiposImovel())) {
    voltarComErro('Selecione um tipo de imóvel válido.', $id);
}
if ($preco === null || $preco < 0) {
    voltarComErro('Informe um preço válido.', $id);
}

$condominio = in_array($tipo, ['apartamento', 'kitnet'], true) ? $condominio : null;

if ($youtubeUrl !== '') {
    if (!filter_var($youtubeUrl, FILTER_VALIDATE_URL) || !preg_match('/(youtube\.com|youtu\.be)/i', $youtubeUrl)) {
        voltarComErro('O link do YouTube parece inválido. Cole a URL completa do vídeo.', $id);
    }
} else {
    $youtubeUrl = null;
}

if ($driveUrl !== '') {
    if (!filter_var($driveUrl, FILTER_VALIDATE_URL)) {
        voltarComErro('O link do Google Drive parece inválido. Cole a URL completa da pasta.', $id);
    }
} else {
    $driveUrl = null;
}

// ---------- Upload de fotos: valida ANTES de tocar no banco ----------
$arquivosEnviados = [];
if (!empty($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
    $totalArquivos = count(array_filter($_FILES['fotos']['name'], fn($nome) => $nome !== ''));

    $fotosExistentes = 0;
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM fotos_imovel WHERE imovel_id = ?');
        $stmt->execute([$id]);
        $fotosExistentes = (int) $stmt->fetch()['total'];
    }

    if ($fotosExistentes + $totalArquivos > UPLOAD_MAX_FOTOS) {
        voltarComErro('Este imóvel pode ter no máximo ' . UPLOAD_MAX_FOTOS . ' fotos (já existem ' . $fotosExistentes . ').', $id);
    }

    $tamanhoMaximoBytes = UPLOAD_MAX_TAMANHO_MB * 1024 * 1024;

    for ($i = 0; $i < count($_FILES['fotos']['name']); $i++) {
        if ($_FILES['fotos']['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
            voltarComErro('Erro ao enviar uma das fotos. Tente novamente.', $id);
        }

        $nomeOriginal = $_FILES['fotos']['name'][$i];
        $tamanho = $_FILES['fotos']['size'][$i];
        $caminhoTemp = $_FILES['fotos']['tmp_name'][$i];

        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        if (!in_array($extensao, UPLOAD_TIPOS_PERMITIDOS, true)) {
            voltarComErro('Formato de foto não permitido. Use apenas JPG, PNG ou WEBP.', $id);
        }
        if ($tamanho > $tamanhoMaximoBytes) {
            voltarComErro('Cada foto deve ter no máximo ' . UPLOAD_MAX_TAMANHO_MB . 'MB.', $id);
        }

        $infoImagem = @getimagesize($caminhoTemp);
        $tiposValidos = ['image/jpeg', 'image/png', 'image/webp'];
        if ($infoImagem === false || !in_array($infoImagem['mime'], $tiposValidos, true)) {
            voltarComErro('Um dos arquivos enviados não é uma imagem válida.', $id);
        }

        $arquivosEnviados[] = ['tmp' => $caminhoTemp, 'extensao' => $extensao, 'mime' => $infoImagem['mime']];
    }
}

// ---------- Grava no banco ----------
if ($id > 0) {
    $stmt = $pdo->prepare(
        'UPDATE imoveis SET titulo=?, finalidade=?, tipo=?, preco=?, iptu=?, condominio=?, observacoes=?, descricao=?,
         cidade_bairro=?, dormitorios=?, banheiros=?, vagas=?, aceita_financiamento=?, aceita_permuta=?, destaque=?, status=?,
         youtube_url=?, drive_url=?
         WHERE id=?'
    );
    $stmt->execute([
        $titulo, $finalidade, $tipo, $preco, $iptu, $condominio, $observacoes ?: null, $descricao,
        $cidadeBairro, $dormitorios, $banheiros, $vagas, $aceitaFinanciamento, $aceitaPermuta, $destaque, $status,
        $youtubeUrl, $driveUrl,
        $id,
    ]);
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO imoveis (codigo, titulo, finalidade, tipo, preco, iptu, condominio, observacoes, descricao,
         cidade_bairro, dormitorios, banheiros, vagas, aceita_financiamento, aceita_permuta, destaque, status,
         youtube_url, drive_url)
         VALUES ("", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $titulo, $finalidade, $tipo, $preco, $iptu, $condominio, $observacoes ?: null, $descricao,
        $cidadeBairro, $dormitorios, $banheiros, $vagas, $aceitaFinanciamento, $aceitaPermuta, $destaque, $status,
        $youtubeUrl, $driveUrl,
    ]);
    $id = (int) $pdo->lastInsertId();
    $codigo = gerarCodigo($id);
    $pdo->prepare('UPDATE imoveis SET codigo = ? WHERE id = ?')->execute([$codigo, $id]);
}

// ---------- Move as fotos validadas para a pasta final ----------
if (!empty($arquivosEnviados)) {
    $pastaImovel = UPLOAD_DIR . '/' . $id;
    if (!is_dir($pastaImovel)) {
        mkdir($pastaImovel, 0755, true);
    }

    $stmtOrdem = $pdo->prepare('SELECT COALESCE(MAX(ordem), -1) AS max_ordem FROM fotos_imovel WHERE imovel_id = ?');
    $stmtOrdem->execute([$id]);
    $proximaOrdem = (int) $stmtOrdem->fetch()['max_ordem'] + 1;

    $stmtInsereFoto = $pdo->prepare('INSERT INTO fotos_imovel (imovel_id, caminho_arquivo, ordem) VALUES (?, ?, ?)');

    foreach ($arquivosEnviados as $arquivo) {
        $extensaoFinal = extensaoComprimida($arquivo['mime']);
        $nomeFinal = bin2hex(random_bytes(8)) . '.' . $extensaoFinal;
        $destino = $pastaImovel . '/' . $nomeFinal;

        $comprimiu = comprimirImagem($arquivo['tmp'], $destino, $arquivo['mime']);

        // Se o GD não estiver disponível (ou falhar por algum motivo), salva o
        // arquivo original sem comprimir — nunca deixa o cadastro travar por isso.
        if (!$comprimiu) {
            $nomeFinal = bin2hex(random_bytes(8)) . '.' . $arquivo['extensao'];
            $destino = $pastaImovel . '/' . $nomeFinal;
            $comprimiu = move_uploaded_file($arquivo['tmp'], $destino);
        }

        if ($comprimiu) {
            $stmtInsereFoto->execute([$id, $id . '/' . $nomeFinal, $proximaOrdem]);
            $proximaOrdem++;
        }
    }
}

redirecionar('/painel/index.php?msg=salvo');
