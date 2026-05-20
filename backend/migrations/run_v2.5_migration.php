<?php
/**
 * Script de Migração - Criar Tabelas de Domínios
 * Versão: 2.5
 */

require __DIR__ . '/../includes/db.php';

try {
    // Tabela domains
    $pdo->exec("CREATE TABLE IF NOT EXISTS `domains` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(255) NOT NULL UNIQUE,
      `domain_url` VARCHAR(255) NOT NULL UNIQUE,
      `logo_light` VARCHAR(255),
      `logo_dark` VARCHAR(255),
      `brand_color` VARCHAR(7) DEFAULT '#0D6EFD',
      `is_active` TINYINT(1) DEFAULT 1,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_active` (`is_active`),
      INDEX `idx_domain_url` (`domain_url`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabela user_domain_permissions
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_domain_permissions` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `username` VARCHAR(255) NOT NULL,
      `domain_id` INT NOT NULL,
      `can_create` TINYINT(1) DEFAULT 1,
      `can_edit` TINYINT(1) DEFAULT 0,
      `can_delete` TINYINT(1) DEFAULT 0,
      `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `assigned_by` VARCHAR(255),
      UNIQUE KEY `unique_user_domain` (`username`, `domain_id`),
      FOREIGN KEY (`domain_id`) REFERENCES `domains`(`id`) ON DELETE CASCADE,
      INDEX `idx_username` (`username`),
      INDEX `idx_domain_id` (`domain_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Inserir domínios padrão
    $pdo->exec("INSERT INTO `domains` (`name`, `domain_url`, `logo_light`, `logo_dark`, `brand_color`, `is_active`) 
    VALUES 
      ('Sales Prime', 'salesprime.com.br', 'images/logo_sales_prime.png', 'images/logo-dark.png', '#0D6EFD', 1),
      ('Prosperus Club', 'prosperusclub.com.br', 'images/logo_prosperus_light.png', 'images/logo_prosperus_dark.png', '#FFC107', 1)
    ON DUPLICATE KEY UPDATE is_active = 1");

    echo "✅ Migração executada com sucesso!\n";
    echo "✅ Tabela 'domains' criada\n";
    echo "✅ Tabela 'user_domain_permissions' criada\n";
    echo "✅ Domínios padrão inseridos\n";

} catch (Exception $e) {
    die("❌ Erro na migração: " . $e->getMessage());
}
