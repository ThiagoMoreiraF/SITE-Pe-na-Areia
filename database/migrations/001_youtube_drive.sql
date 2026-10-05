-- Migração aditiva: campos opcionais de vídeo do YouTube e link do Google Drive.
-- Não remove nem altera nenhuma coluna existente — seguro pra rodar em produção
-- mesmo com imóveis já cadastrados.
ALTER TABLE imoveis
  ADD COLUMN youtube_url VARCHAR(255) NULL DEFAULT NULL,
  ADD COLUMN drive_url VARCHAR(500) NULL DEFAULT NULL;
