-- UTM Generator - Database Migration v2.2
-- Adds: clicks, login_attempts, settings tables
-- Run this on your existing database (hgsa7692_utm)
-- Date: 2026-02-09

-- Tabela de Cliques Detalhados
CREATE TABLE IF NOT EXISTS clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utm_id INT NOT NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referer TEXT,
    country VARCHAR(2),
    city VARCHAR(100),
    FOREIGN KEY (utm_id) REFERENCES urls(id) ON DELETE CASCADE,
    INDEX idx_utm_id (utm_id),
    INDEX idx_clicked_at (clicked_at),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Tentativas de Login (Rate Limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(100),
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Configurações
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configurações padrão
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('app_name', 'UTM Generator'),
('app_version', '2.2'),
('maintenance_mode', '0'),
('max_utms_per_user', '100');

-- Índices adicionais para performance
CREATE INDEX IF NOT EXISTS idx_urls_user_active ON urls(username, is_enabled);
CREATE INDEX IF NOT EXISTS idx_urls_generation_date ON urls(generation_date);
