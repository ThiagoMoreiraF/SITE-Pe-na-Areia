-- Migração aditiva: tabela NOVA para os textos editáveis da página "Guia & Orientações".
-- Não altera nenhuma tabela existente. Começa vazia: enquanto um tema não for
-- editado pelo painel, o site mostra o texto padrão que está no código.
CREATE TABLE IF NOT EXISTS guia_topicos (
  slug VARCHAR(50) NOT NULL PRIMARY KEY,
  titulo VARCHAR(120) NOT NULL,
  texto TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
