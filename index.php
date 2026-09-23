<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Imóveis em destaque (renderizados direto no servidor, poucos itens, não precisa de AJAX)
$stmtDestaques = $pdo->prepare(
    'SELECT i.codigo, i.titulo, i.finalidade, i.tipo, i.preco, i.cidade_bairro,
            i.dormitorios, i.banheiros, i.vagas, i.observacoes,
            (SELECT f.caminho_arquivo FROM fotos_imovel f WHERE f.imovel_id = i.id ORDER BY f.ordem ASC LIMIT 1) AS foto_capa
     FROM imoveis i
     WHERE i.status = "ativo" AND i.destaque = 1
     ORDER BY i.created_at DESC
     LIMIT 6'
);
$stmtDestaques->execute();
$destaques = $stmtDestaques->fetchAll();

$paginaTitulo = 'Grupo Pé na Areia - Portal Imobiliário';
require __DIR__ . '/includes/header.php';
?>

    <!-- BUSCA RÁPIDA POR CÓDIGO -->
    <form class="codigo-search-bar" onsubmit="buscarPorCodigo(event)">
      <span><i class="fas fa-magnifying-glass"></i> Tem um código do imóvel? Busque aqui:</span>
      <input type="text" id="busca-codigo-input" placeholder="Ex: PNA-00001">
      <button type="submit">Buscar</button>
    </form>

    <?php if (!empty($destaques)): ?>
    <div class="destaques-section">
      <h3><i class="fas fa-star"></i> Imóveis em Destaque</h3>
      <div class="grid">
        <?php foreach ($destaques as $imovel):
          $foto = $imovel['foto_capa'] ? UPLOAD_URL . '/' . $imovel['foto_capa'] : '/assets/img/sem-foto.svg';
          $mensagem = 'Olá, tenho interesse no imóvel ' . $imovel['codigo'] . ' - ' . $imovel['titulo'] . ' (' . $imovel['cidade_bairro'] . ')';
        ?>
        <div class="card-imovel">
          <a href="/imovel.php?codigo=<?= urlencode($imovel['codigo']) ?>" class="card-img-wrapper">
            <span class="card-badge"><?= h(tipoLabel($imovel['tipo'])) ?> · <?= h(finalidadeLabel($imovel['finalidade'])) ?></span>
            <span class="destaque-ribbon">Destaque</span>
            <span class="card-codigo"><?= h($imovel['codigo']) ?></span>
            <img src="<?= h($foto) ?>" alt="<?= h($imovel['titulo']) ?>" loading="lazy">
          </a>
          <div class="card-body">
            <div class="card-price"><?= h(formatarPreco((float) $imovel['preco'], $imovel['finalidade'])) ?></div>
            <div class="card-title-text"><a href="/imovel.php?codigo=<?= urlencode($imovel['codigo']) ?>"><?= h($imovel['titulo']) ?></a></div>
            <div class="card-location"><i class="fas fa-map-marker-alt"></i> <?= h($imovel['cidade_bairro']) ?></div>
            <div class="card-details">
              <span><i class="fas fa-bed"></i> <?= (int) $imovel['dormitorios'] ?> dorm.</span>
              <span><i class="fas fa-bath"></i> <?= (int) $imovel['banheiros'] ?> banh.</span>
              <span><i class="fas fa-car"></i> <?= (int) $imovel['vagas'] ?> vaga(s)</span>
            </div>
            <div class="card-tags"><?= h($imovel['observacoes'] ?? '') ?></div>
            <div class="card-actions">
              <a href="/imovel.php?codigo=<?= urlencode($imovel['codigo']) ?>" class="btn-detalhes-card">Ver detalhes</a>
              <a href="https://wa.me/<?= h(WHATSAPP_PRINCIPAL) ?>?text=<?= urlencode($mensagem) ?>" target="_blank" rel="noopener" class="btn-whatsapp-card">
                <i class="fab fa-whatsapp"></i> Tenho Interesse
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- SEÇÃO DE BUSCA E FILTROS -->
    <div class="search-panel">
      <h3>Todos os Imóveis</h3>

      <div class="finalidade-toggle">
        <button class="btn-finalidade active" onclick="selecionarFinalidade('', this)">Todos</button>
        <button class="btn-finalidade" onclick="selecionarFinalidade('aluguel', this)">Aluguel</button>
        <button class="btn-finalidade" onclick="selecionarFinalidade('venda', this)">Venda</button>
      </div>

      <div class="type-buttons">
        <button class="btn-type active" onclick="selecionarTipo('', this)">Todos</button>
        <button class="btn-type" onclick="selecionarTipo('casa', this)">🏠 Casa</button>
        <button class="btn-type" onclick="selecionarTipo('sobrado', this)">🏘️ Sobrado</button>
        <button class="btn-type" onclick="selecionarTipo('apartamento', this)">🏢 Apartamento</button>
        <button class="btn-type" onclick="selecionarTipo('kitnet', this)">🏬 Kitnet</button>
        <button class="btn-type" onclick="selecionarTipo('chacara', this)">🏡 Chácara</button>
        <button class="btn-type" onclick="selecionarTipo('comercial', this)">🏪 Comercial</button>
        <button class="btn-type" onclick="selecionarTipo('terreno', this)">📐 Terreno</button>
      </div>

      <div class="filter-grid">
        <div class="filter-group">
          <label for="filter-city">Cidade / Bairro</label>
          <input type="text" id="filter-city" placeholder="Ex: Mongaguá, Centro..." onkeyup="aplicarFiltros()">
        </div>

        <div class="filter-group">
          <label for="filter-bedrooms">Dormitórios</label>
          <select id="filter-bedrooms" onchange="aplicarFiltros()">
            <option value="">Qualquer quantidade</option>
            <option value="1">1 Dormitório</option>
            <option value="2">2 Dormitórios</option>
            <option value="3">3+ Dormitórios</option>
          </select>
        </div>
      </div>

      <div class="filter-actions">
        <button class="btn btn-secondary" onclick="limparFiltros()">Limpar Filtros</button>
        <button class="btn btn-primary" onclick="aplicarFiltros()">Buscar Imóveis</button>
      </div>
    </div>

    <!-- GRID DE IMÓVEIS -->
    <div class="grid" id="imoveis-grid">
      <p style="text-align: center; width: 100%; grid-column: 1 / -1; font-size: 16px; color: #0284c7;">
        <i class="fas fa-spinner fa-spin"></i> Carregando imóveis...
      </p>
    </div>

    <!-- SIMULADOR DE FINANCIAMENTO -->
    <div class="simulator-card">
      <div class="sim-line-1">
        <h3><i class="fas fa-calculator"></i> Simulador de Financiamento Imobiliário</h3>
      </div>

      <div class="sim-content-below">
        <div class="sim-line-2">
          <div class="filter-group">
            <label for="sim-val-imovel">Valor Imóvel (R$)</label>
            <input type="text" id="sim-val-imovel" value="250.000,00" oninput="mascaraMoeda(this)">
          </div>

          <div class="filter-group">
            <label for="sim-val-entrada">Entrada (R$)</label>
            <input type="text" id="sim-val-entrada" value="50.000,00" oninput="mascaraMoeda(this)">
          </div>

          <div class="filter-group">
            <label for="sim-prazo">Prazo (Anos)</label>
            <select id="sim-prazo">
              <option value="30" selected>30 anos (360 meses)</option>
              <option value="25">25 anos (300 meses)</option>
              <option value="20">20 anos (240 meses)</option>
              <option value="15">15 anos (180 meses)</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="sim-sistema">Sistema</label>
            <select id="sim-sistema">
              <option value="SAC" selected>SAC (Decrescente)</option>
              <option value="PRICE">PRICE (Fixas)</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="sim-taxa">Juros Anual (%)</label>
            <input type="number" id="sim-taxa" value="10.5" step="0.1" min="0">
          </div>
        </div>

        <div class="sim-line-3">
          <button class="btn btn-primary btn-simular" onclick="calcularFinanciamento()">
            <i class="fas fa-play"></i> Simular Agora
          </button>
        </div>

        <div class="sim-line-4" id="sim-line-4">
          <div class="sim-res-box">
            <span>VALOR FINANCIADO</span>
            <strong id="res-financiado">R$ 0,00</strong>
          </div>
          <div class="sim-res-box">
            <span>1ª PARCELA (ESTIMADA)</span>
            <strong id="res-p1">R$ 0,00</strong>
          </div>
          <div class="sim-res-box" id="box-p-ultima">
            <span>ÚLTIMA PARCELA (ESTIMADA)</span>
            <strong id="res-p-ult">R$ 0,00</strong>
          </div>
          <div class="sim-res-box">
            <span>CARTÓRIO / ITBI (~4%)</span>
            <strong id="res-itbi">R$ 0,00</strong>
          </div>
        </div>

        <div class="sim-line-5" id="sim-line-5">
          * As simulações exibidas são estimativas educativas e não constituem proposta firme de financiamento. Os valores reais podem variar conforme a análise de crédito, seguros e tarifas de cada instituição financeira.
        </div>
      </div>
    </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
