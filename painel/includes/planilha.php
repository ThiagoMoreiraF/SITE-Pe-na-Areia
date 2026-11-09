<?php
/**
 * Importação/exportação da planilha de dados internos (CSV compatível com Excel).
 * A importação só ATUALIZA dados internos de imóveis já existentes (pelo código):
 * nunca cria nem apaga imóvel, e célula vazia nunca apaga o que já estava salvo.
 */
require_once __DIR__ . '/dados-internos.php';

const PLANILHA_TAMANHO_MAXIMO = 2 * 1024 * 1024;

function normalizarCabecalho(string $texto): string
{
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $texto = strtr($texto, [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e',
        'í' => 'i', 'ì' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ü' => 'u',
        'ç' => 'c', 'º' => 'o', 'ª' => 'a',
    ]);
    $texto = preg_replace('/[^a-z0-9]+/', '_', $texto);
    return trim($texto, '_');
}

/** Converte o cabeçalho de uma coluna no campo do banco correspondente (null = coluna ignorada). */
function campoDoCabecalho(string $cabecalho): ?string
{
    $apelidos = [
        'codigo' => 'codigo', 'cod' => 'codigo', 'codigo_do_imovel' => 'codigo',
        'rua' => 'endereco_rua', 'endereco' => 'endereco_rua', 'logradouro' => 'endereco_rua', 'rua_logradouro' => 'endereco_rua',
        'numero' => 'endereco_numero', 'num' => 'endereco_numero', 'n' => 'endereco_numero', 'no' => 'endereco_numero',
        'complemento' => 'endereco_complemento',
        'cep' => 'endereco_cep',
        'proprietario' => 'proprietario_nome', 'nome_do_proprietario' => 'proprietario_nome',
        'nome_proprietario' => 'proprietario_nome', 'nome_completo' => 'proprietario_nome',
        'telefone' => 'proprietario_telefone', 'tel' => 'proprietario_telefone',
        'fone' => 'proprietario_telefone', 'telefone_fixo' => 'proprietario_telefone',
        'celular' => 'proprietario_celular', 'cel' => 'proprietario_celular', 'whatsapp' => 'proprietario_celular',
        'email' => 'proprietario_email', 'e_mail' => 'proprietario_email',
        'observacoes_internas' => 'obs_internas', 'obs_internas' => 'obs_internas',
        'obs' => 'obs_internas', 'observacao_interna' => 'obs_internas',
    ];
    foreach (array_keys(camposInternos()) as $campo) {
        $apelidos[$campo] = $campo;
    }
    return $apelidos[normalizarCabecalho($cabecalho)] ?? null;
}

/**
 * Impede que o Excel interprete um valor como fórmula ao abrir a planilha exportada
 * (ex: "=HYPERLINK(...)" digitado num campo). Telefones como "+55 13..." não são afetados.
 */
function protegerCelulaCsv($valor): string
{
    $valor = (string) $valor;
    if ($valor !== '' && (preg_match('/^[=@\t\r]/', $valor) || preg_match('/^[+\-](?![\d\s(])/', $valor))) {
        return "'" . $valor;
    }
    return $valor;
}

function desprotegerCelulaCsv(string $valor): string
{
    return preg_match("/^'[=@+\\-\t\r]/", $valor) ? substr($valor, 1) : $valor;
}

/** Lê um CSV vindo do Excel (UTF-8 ou Windows-1252, separado por ; , ou tab). */
function lerCsv(string $caminho): array
{
    $conteudo = file_get_contents($caminho);
    if ($conteudo === false || trim($conteudo) === '') {
        return [[], 'O arquivo está vazio.'];
    }
    if (str_starts_with($conteudo, "\xEF\xBB\xBF")) {
        $conteudo = substr($conteudo, 3);
    }
    if (!mb_check_encoding($conteudo, 'UTF-8')) {
        $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252');
    }

    $primeiraLinha = strtok($conteudo, "\n");
    $contagem = [';' => substr_count($primeiraLinha, ';'), ',' => substr_count($primeiraLinha, ','), "\t" => substr_count($primeiraLinha, "\t")];
    arsort($contagem);
    $delimitador = array_key_first($contagem);

    $arquivo = fopen('php://temp', 'r+');
    fwrite($arquivo, $conteudo);
    rewind($arquivo);
    $linhas = [];
    while (($linha = fgetcsv($arquivo, 0, $delimitador, '"', '')) !== false) {
        $linhas[] = $linha;
    }
    fclose($arquivo);

    return [$linhas, null];
}

/** Processa o upload da planilha e retorna um relatório do que foi feito. */
function importarPlanilha(PDO $pdo, ?array $arquivo, ?string $csrf): array
{
    $relatorio = ['erro' => null, 'linhas' => 0, 'atualizados' => 0, 'sem_alteracao' => 0, 'avisos' => []];

    if (!validarCsrfToken($csrf)) {
        $relatorio['erro'] = 'Sessão expirada. Atualize a página e tente novamente.';
        return $relatorio;
    }
    if (!$arquivo || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $relatorio['erro'] = 'Selecione o arquivo da planilha antes de enviar.';
        return $relatorio;
    }
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $relatorio['erro'] = 'Erro ao enviar o arquivo. Tente novamente.';
        return $relatorio;
    }
    if ($arquivo['size'] > PLANILHA_TAMANHO_MAXIMO) {
        $relatorio['erro'] = 'Arquivo muito grande (máximo 2MB).';
        return $relatorio;
    }
    if (file_get_contents($arquivo['tmp_name'], false, null, 0, 4) === "PK\x03\x04") {
        $relatorio['erro'] = 'Esse arquivo está no formato .xlsx. No Excel, use "Salvar como" e escolha o tipo CSV, depois envie o arquivo .csv.';
        return $relatorio;
    }
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, ['csv', 'txt'], true)) {
        $relatorio['erro'] = 'Envie a planilha no formato CSV (no Excel: "Salvar como" → tipo CSV).';
        return $relatorio;
    }

    [$linhas, $erroLeitura] = lerCsv($arquivo['tmp_name']);
    if ($erroLeitura !== null) {
        $relatorio['erro'] = $erroLeitura;
        return $relatorio;
    }

    $cabecalho = array_shift($linhas) ?? [];
    $mapaColunas = [];
    foreach ($cabecalho as $indice => $titulo) {
        $campo = campoDoCabecalho((string) $titulo);
        if ($campo !== null && !in_array($campo, $mapaColunas, true)) {
            $mapaColunas[$indice] = $campo;
        }
    }

    $indiceCodigo = array_search('codigo', $mapaColunas, true);
    if ($indiceCodigo === false) {
        $relatorio['erro'] = 'Não encontrei a coluna "Código" na primeira linha da planilha. Ela é obrigatória para saber qual imóvel atualizar.';
        return $relatorio;
    }
    if (count($mapaColunas) === 1) {
        $relatorio['erro'] = 'Não reconheci nenhuma coluna de dados (Rua, Número, CEP, Proprietário, Telefone, Celular, E-mail, Observações internas). Confira os títulos da primeira linha.';
        return $relatorio;
    }

    $idsPorCodigo = $pdo->query('SELECT codigo, id FROM imoveis')->fetchAll(PDO::FETCH_KEY_PAIR);
    $atualizacoes = [];
    $linhaDoCodigo = [];

    foreach ($linhas as $posicao => $linha) {
        $numeroLinha = $posicao + 2; // +1 pelo cabeçalho, +1 porque o Excel começa na linha 1
        if (count(array_filter($linha, fn($celula) => trim((string) $celula) !== '')) === 0) {
            continue;
        }
        $relatorio['linhas']++;

        $codigo = normalizarCodigo((string) ($linha[$indiceCodigo] ?? ''));
        if ($codigo === '') {
            $relatorio['avisos'][] = "Linha $numeroLinha: sem código — ignorada.";
            continue;
        }
        if (!isset($idsPorCodigo[$codigo])) {
            $relatorio['avisos'][] = "Linha $numeroLinha: o código $codigo não existe no painel — ignorada.";
            continue;
        }
        if (isset($linhaDoCodigo[$codigo])) {
            $relatorio['avisos'][] = "Linha $numeroLinha: o código $codigo também aparece na linha {$linhaDoCodigo[$codigo]} — valeram os dados da linha $numeroLinha.";
        }
        $linhaDoCodigo[$codigo] = $numeroLinha;

        $id = (int) $idsPorCodigo[$codigo];
        foreach ($mapaColunas as $indice => $campo) {
            if ($campo === 'codigo') {
                continue;
            }
            $bruto = desprotegerCelulaCsv((string) ($linha[$indice] ?? ''));
            if (trim($bruto) === '') {
                continue;
            }
            [$valor, $erroCampo] = normalizarCampoInterno($campo, $bruto);
            if ($erroCampo !== null) {
                $relatorio['avisos'][] = "Linha $numeroLinha ($codigo): $erroCampo Esse campo não foi importado.";
                continue;
            }
            $atualizacoes[$id][$campo] = $valor;
        }
    }

    // Nomes de coluna vêm do mapa de apelidos (lista fixa), nunca direto da planilha.
    $pdo->beginTransaction();
    try {
        foreach ($atualizacoes as $id => $campos) {
            $set = implode(', ', array_map(fn($coluna) => "$coluna = ?", array_keys($campos)));
            $stmt = $pdo->prepare("UPDATE imoveis SET $set WHERE id = ?");
            $stmt->execute(array_merge(array_values($campos), [$id]));
            $stmt->rowCount() > 0 ? $relatorio['atualizados']++ : $relatorio['sem_alteracao']++;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Erro na importação da planilha: ' . $e->getMessage());
        $relatorio['erro'] = 'Erro ao gravar no banco de dados. Nenhuma alteração foi feita — tente novamente.';
    }

    return $relatorio;
}
