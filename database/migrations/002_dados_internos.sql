-- Migração aditiva: dados internos do imóvel (endereço completo, proprietário
-- e observações internas). Esses campos aparecem SOMENTE no painel, nunca no site.
-- Não remove nem altera nenhuma coluna existente.
ALTER TABLE imoveis
  ADD COLUMN endereco_rua VARCHAR(200) NULL DEFAULT NULL,
  ADD COLUMN endereco_numero VARCHAR(20) NULL DEFAULT NULL,
  ADD COLUMN endereco_complemento VARCHAR(100) NULL DEFAULT NULL,
  ADD COLUMN endereco_cep VARCHAR(9) NULL DEFAULT NULL,
  ADD COLUMN proprietario_nome VARCHAR(150) NULL DEFAULT NULL,
  ADD COLUMN proprietario_telefone VARCHAR(30) NULL DEFAULT NULL,
  ADD COLUMN proprietario_celular VARCHAR(30) NULL DEFAULT NULL,
  ADD COLUMN proprietario_email VARCHAR(150) NULL DEFAULT NULL,
  ADD COLUMN obs_internas TEXT NULL DEFAULT NULL;
