<?php
/**
 * Dados internos do imóvel (endereço completo, proprietário e observações).
 * Usados SOMENTE no painel — nenhum desses campos pode ser exibido no site público.
 */

function camposInternos(): array
{
    return [
        'endereco_rua'          => ['rotulo' => 'Rua / Logradouro',      'max' => 200],
        'endereco_numero'       => ['rotulo' => 'Número',                'max' => 20],
        'endereco_complemento'  => ['rotulo' => 'Complemento',           'max' => 100],
        'endereco_cep'          => ['rotulo' => 'CEP',                   'max' => 9],
        'proprietario_nome'     => ['rotulo' => 'Nome do proprietário',  'max' => 150],
        'proprietario_telefone' => ['rotulo' => 'Telefone',              'max' => 30],
        'proprietario_celular'  => ['rotulo' => 'Celular',               'max' => 30],
        'proprietario_email'    => ['rotulo' => 'E-mail do proprietário', 'max' => 150],
        'obs_internas'          => ['rotulo' => 'Observações internas',  'max' => 5000],
    ];
}

/**
 * Valida e normaliza um campo interno.
 * Retorna [valor normalizado ou null se vazio, mensagem de erro ou null].
 */
function normalizarCampoInterno(string $campo, string $valor): array
{
    $valor = trim($valor);
    if ($valor === '') {
        return [null, null];
    }

    $definicao = camposInternos()[$campo];

    if ($campo === 'endereco_cep') {
        $digitos = preg_replace('/\D/', '', $valor);
        if (strlen($digitos) === 7) {
            $digitos = '0' . $digitos; // o Excel remove o zero à esquerda de CEPs numéricos
        }
        if (strlen($digitos) !== 8) {
            return [null, 'CEP inválido ("' . $valor . '") — use 8 dígitos, ex: 11730-000.'];
        }
        return [substr($digitos, 0, 5) . '-' . substr($digitos, 5), null];
    }

    if ($campo === 'proprietario_email' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
        return [null, 'E-mail do proprietário inválido ("' . $valor . '").'];
    }

    if (mb_strlen($valor) > $definicao['max']) {
        return [null, $definicao['rotulo'] . ' muito longo (máximo ' . $definicao['max'] . ' caracteres).'];
    }

    return [$valor, null];
}
