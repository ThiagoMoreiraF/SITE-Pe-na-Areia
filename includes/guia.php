<?php
/**
 * Conteúdo da página "Guia & Orientações".
 * Os textos padrão ficam aqui. O que for editado pelo painel é salvo na tabela
 * guia_topicos e tem prioridade — apagar a linha da tabela volta ao texto padrão.
 */

function guiaTopicosPadrao(): array
{
    return [
        'introducao' => [
            'icone'  => 'fa-book-open',
            'titulo' => 'Guia & Orientações',
            'texto'  => "Reunimos aqui as principais orientações para quem quer comprar, vender, alugar ou investir em imóveis no Litoral Sul de SP.\nFicou com alguma dúvida? Fale com a nossa equipe pelo WhatsApp.",
        ],
        'saiba-mais' => [
            'icone'  => 'fa-circle-info',
            'titulo' => 'Saiba Mais',
            'texto'  => 'O **Grupo Pé na Areia** atua na consultoria completa para compra, venda e gestão de imóveis no Litoral Sul de SP. Oferecemos segurança jurídica com checagem rigorosa de certidões, suporte em financiamentos bancários e atendimento com hora marcada.',
        ],
        'alugar' => [
            'icone'  => 'fa-key',
            'titulo' => 'Alugar um Imóvel',
            'texto'  => 'Para alugar com tranquilidade, realizamos análise cadastral ágil do locatário. Trabalhamos com opções de garantias como caução, seguro fiança ou fiador, garantindo contrato transparente.',
        ],
        'avaliacao-residencial' => [
            'icone'  => 'fa-house-circle-check',
            'titulo' => 'Avaliação Residencial',
            'texto'  => 'A avaliação precisa calcula o valor real de mercado com base na localização, metragem, padrão de acabamento e histórico de vendas na região do Litoral Sul.',
        ],
        'avaliacao-comercial' => [
            'icone'  => 'fa-building-circle-check',
            'titulo' => 'Avaliação Comercial',
            'texto'  => 'Análise técnica para lojas, salas e galpões comerciais. Avaliamos o fluxo de pedestres e veículos, vocação da região e potencial de retorno sobre o investimento.',
        ],
        'passar-ponto' => [
            'icone'  => 'fa-store',
            'titulo' => 'Passar o Ponto',
            'texto'  => 'Trata-se da negociação do **fundo de comércio** (instalações, carteira de clientes, marca e móveis). Orientamos na transição segura do contrato junto ao proprietário.',
        ],
        'permuta' => [
            'icone'  => 'fa-arrow-right-arrow-left',
            'titulo' => 'Permuta Imobiliária',
            'texto'  => 'A permuta é a troca direta de imóveis entre proprietários. Quando os valores não são iguais, ocorre o pagamento da diferença (torna). Avaliamos ambos os imóveis para um negócio justo.',
        ],
    ];
}

/** Textos do guia com as edições do painel aplicadas por cima dos padrões. */
function carregarGuia(PDO $pdo): array
{
    $topicos = guiaTopicosPadrao();
    foreach ($topicos as $slug => $topico) {
        $topicos[$slug]['editado'] = false;
    }

    try {
        foreach ($pdo->query('SELECT slug, titulo, texto FROM guia_topicos') as $linha) {
            if (isset($topicos[$linha['slug']])) {
                $topicos[$linha['slug']]['titulo'] = $linha['titulo'];
                $topicos[$linha['slug']]['texto'] = $linha['texto'];
                $topicos[$linha['slug']]['editado'] = true;
            }
        }
    } catch (PDOException $e) {
        // Tabela ainda não criada (migração pendente): a página continua funcionando com os textos padrão.
    }

    return $topicos;
}

/** Escapa o texto, mantém as quebras de linha e transforma **texto** em negrito. */
function formatarTextoGuia(string $texto): string
{
    return preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', nl2br(h($texto)));
}
