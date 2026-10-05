# Diretrizes de Atualização - Projeto Imobiliária "Grupo Pé na Areia"

## ⚠️️ CONTEXTO E REGRAS CRÍTICAS (LEIA COM ATENÇÃO)

Você está atuando em um sistema PHP/MySQL já em produção na Hostgator.
**REGRA DE OURO DO BANCO DE DADOS:** NENHUMA tabela ou coluna existente do sistema de cadastro atual deve ser alterada de forma destrutiva. O cliente já possui imóveis cadastrados. Qualquer nova necessidade de dados (como links de vídeo, link do Google Drive, ou aumento do limite de fotos) deve ser feita através de **adição** de novas colunas (ex: `ALTER TABLE ... ADD COLUMN ...`) ou criação de tabelas relacionais novas.

Ao final de TODAS as implementações, você deve realizar testes exaustivos no ambiente local do usuário (que utiliza XAMPP já configurado).

## 🛠️ TAREFAS A SEREM EXECUTADAS

### Tarefa 1: Ajuste e Destaque do Header (Topo do Site)
* **Inversão de Lados:** Atualmente os links (Início, Quem Somos) estão na direita e o texto "Litoral Sul – SP | Mongaguá..." na esquerda. Inverta isso. Coloque os links de navegação na esquerda e o texto de localização na direita.
* **Destaque:** Aumente o tamanho geral do header (padding/height) e aumente o tamanho e peso da fonte. Torne a navegação mais óbvia e amigável. Revise o contraste de cores se necessário.

### Tarefa 2: Atualização do Simulador de Financiamento
* Encontre a lógica do simulador de financiamento atual.
* No select de "Prazo (Anos)", adicione as opções de **5 anos (60 meses)** e **10 anos (120 meses)**.
* Certifique-se de que a fórmula matemática de simulação (SAC ou Price) funcione corretamente para esses novos prazos curtos.

### Tarefa 3: Estilização dos Cards "Quem Somos"
* Na página "Quem Somos", os cards de "Missão", "Visão" e "Valores" precisam receber imagens de fundo de alta qualidade (busque em bancos de imagens gratuitos, como Unsplash, imagens que remetam aos temas).
* **Legibilidade e Performance:** Aplique um overlay escuro (ex: `linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.8))`) sobre as imagens de fundo para garantir a leitura do texto. Tome muito cuidado com o tamanho e as cores das fontes. Adicione `box-shadow` e `border-radius`. Otimize o carregamento dessas imagens.

### Tarefa 4: Expansão de Mídia (15 Fotos, Vídeo no YouTube e Link do Drive)
* **Aumento de Fotos (ATÉ 15):** Ajuste a lógica de upload e o banco de dados para aceitar até 15 fotos por imóvel.
  * *Obrigatório 1:* Implemente lógica de **compressão de imagem** no PHP no momento do upload (diminuindo o peso na hora de salvar) para não estourar o limite de disco da hospedagem Hostgator.
  * *Obrigatório 2:* No frontend, adicione o atributo `loading="lazy"` nas imagens do carrossel (exceto a primeira) para só carregar as imagens do final quando o usuário interagir, não prejudicando o carregamento do site.

* **Novos Campos Opcionais no Painel:**
  Crie dois novos campos no painel de cadastro do imóvel e suas respectivas colunas no banco de dados:
  1. **Link do Vídeo no YouTube:** 
     * O corretor colará apenas a URL do YouTube.
     * *Regra:* **NUNCA** faça upload de arquivos de vídeo (`.mp4`, etc) direto para a Hostgator, pois esgotará o espaço e deixará o site lento. O site deve apenas embutir (iframe) o vídeo do YouTube no final do carrossel.
  2. **Link para pasta do Google Drive:** 
     * Solução ideal para quando o corretor tiver mais de 15 fotos ou vídeos extras.
     * No frontend do site, se esse campo for preenchido, exiba um botão destacado com o texto "Ver galeria completa".
     * *Regra:* O botão OBRIGATORIAMENTE deve abrir em uma nova aba (`target="_blank"`).

### Tarefa 5: Otimização Severa de SEO
O site não está aparecendo para "grupo pe na areia imobiliaria", "pe na areia mongagua", etc. Aplique as seguintes melhorias:
* **Title e Meta Description:** Atualize as tags principais (Ex Title: `Grupo Pé na Areia | Imobiliária em Mongaguá e Litoral Sul - SP`).
* **Open Graph e Twitter Cards:** Implemente as tags `og:title`, `og:description`, `og:image`, `og:url`. Na página de detalhes do imóvel, elas devem ser dinâmicas.
* **Sitemap.xml e Robots.txt:** Crie um script PHP que gere um `sitemap.xml` dinâmico contendo todas as URLs, e um `robots.txt` padrão.
* **URL Amigável:** Aplique regras no `.htaccess` para URLs limpas, se necessário.
* **Schema Markup (JSON-LD):** Adicione na home o JSON-LD de `RealEstateAgent` ("Grupo Pé na Areia", "Mongaguá") e marcação de `Product/Offer` nas páginas de imóveis.

### Tarefa 6: Testes Locais (XAMPP)
* Antes de finalizar, você DEVE testar todas as funcionalidades no ambiente XAMPP local.
* Cadastre um imóvel simulando 15 fotos para testar a compressão e o lazy loading.
* Insira um link do YouTube e um do Drive e verifique se renderizam corretamente no frontend.
* Teste o cálculo do simulador para 5 e 10 anos.
* Valide visualmente a responsividade do Header e os cards "Quem Somos".