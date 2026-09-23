-- Grupo Pé na Areia - Estrutura do banco de dados
-- Importe este arquivo no phpMyAdmin (cPanel > Bancos de Dados MySQL) antes de configurar o config.php

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  tentativas_falhas TINYINT UNSIGNED NOT NULL DEFAULT 0,
  bloqueado_ate DATETIME NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS imoveis (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL DEFAULT '',
  titulo VARCHAR(150) NOT NULL,
  finalidade ENUM('aluguel','venda') NOT NULL,
  tipo ENUM('casa','sobrado','apartamento','kitnet','chacara','comercial','terreno') NOT NULL,
  preco DECIMAL(12,2) NOT NULL,
  iptu DECIMAL(12,2) NULL DEFAULT NULL,
  condominio DECIMAL(12,2) NULL DEFAULT NULL,
  observacoes VARCHAR(200) NULL DEFAULT NULL,
  descricao TEXT NOT NULL,
  cidade_bairro VARCHAR(150) NOT NULL,
  dormitorios TINYINT UNSIGNED NOT NULL DEFAULT 0,
  banheiros TINYINT UNSIGNED NOT NULL DEFAULT 0,
  vagas TINYINT UNSIGNED NOT NULL DEFAULT 0,
  aceita_financiamento TINYINT(1) NOT NULL DEFAULT 0,
  aceita_permuta TINYINT(1) NOT NULL DEFAULT 0,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_codigo (codigo),
  INDEX idx_finalidade (finalidade),
  INDEX idx_tipo (tipo),
  INDEX idx_status (status),
  INDEX idx_destaque (destaque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fotos_imovel (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  imovel_id INT UNSIGNED NOT NULL,
  caminho_arquivo VARCHAR(255) NOT NULL,
  ordem TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fotos_imovel FOREIGN KEY (imovel_id) REFERENCES imoveis(id) ON DELETE CASCADE,
  INDEX idx_imovel (imovel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- O usuário administrador (dono) é criado depois, pelo /painel/setup.php
-- (não insira senha em texto puro aqui; o setup.php gera o hash com password_hash)
