-- Parte 3 - Admin Panel Multi-Domínio
-- Versão: 2.5 - Tabelas de Controle de Domínios

-- Tabela de Domínios Configurados
CREATE TABLE IF NOT EXISTS `domains` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Nome exibível (Sales Prime, Prosperus Club)',
  `domain_url` VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL do domínio (salesprime.com.br)',
  `logo_light` VARCHAR(255) COMMENT 'Caminho da logo para tema light',
  `logo_dark` VARCHAR(255) COMMENT 'Caminho da logo para tema dark',
  `brand_color` VARCHAR(7) DEFAULT '#0D6EFD' COMMENT 'Cor primária da marca',
  `is_active` TINYINT(1) DEFAULT 1 COMMENT '1 = ativo, 0 = inativo',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`),
  INDEX `idx_domain_url` (`domain_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Permissões: Usuário x Domínio
CREATE TABLE IF NOT EXISTS `user_domain_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL COMMENT 'Usuário (email)',
  `domain_id` INT NOT NULL COMMENT 'Referência para tabela domains',
  `can_create` TINYINT(1) DEFAULT 1 COMMENT 'Pode criar UTMs neste domínio',
  `can_edit` TINYINT(1) DEFAULT 0 COMMENT 'Pode editar UTMs neste domínio',
  `can_delete` TINYINT(1) DEFAULT 0 COMMENT 'Pode deletar UTMs neste domínio',
  `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` VARCHAR(255) COMMENT 'Quem atribuiu a permissão',
  UNIQUE KEY `unique_user_domain` (`username`, `domain_id`),
  FOREIGN KEY (`domain_id`) REFERENCES `domains`(`id`) ON DELETE CASCADE,
  INDEX `idx_username` (`username`),
  INDEX `idx_domain_id` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir domínios padrão
INSERT INTO `domains` (`name`, `domain_url`, `logo_light`, `logo_dark`, `brand_color`, `is_active`) 
VALUES 
  ('Sales Prime', 'salesprime.com.br', 'images/logo_sales_prime.png', 'images/logo-dark.png', '#0D6EFD', 1),
  ('Prosperus Club', 'prosperusclub.com.br', 'images/logo_prosperus_light.png', 'images/logo_prosperus_dark.png', '#FFC107', 1)
ON DUPLICATE KEY UPDATE is_active = 1;
