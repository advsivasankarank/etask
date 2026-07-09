-- Step 33: Settings Module — Configuration Storage
-- Adds app_settings table for generic key-value settings storage.
-- Idempotent: safe to run multiple times.

USE etaxadv_etask;

-- =============================================================================
-- A. Create app_settings table
-- =============================================================================

CREATE TABLE IF NOT EXISTS app_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_group VARCHAR(100) NOT NULL,
    setting_key VARCHAR(150) NOT NULL,
    setting_value TEXT NULL,
    setting_type VARCHAR(30) NULL DEFAULT 'text',
    is_sensitive TINYINT(1) NOT NULL DEFAULT 0,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_app_settings_group_key (setting_group, setting_key),
    KEY idx_app_settings_group (setting_group),
    CONSTRAINT fk_app_settings_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- B. Create maintenance_logs table
-- =============================================================================

CREATE TABLE IF NOT EXISTS maintenance_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action_type VARCHAR(80) NOT NULL,
    action_note TEXT NULL,
    performed_by BIGINT UNSIGNED NULL,
    performed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_maintenance_logs_type (action_type),
    CONSTRAINT fk_maintenance_logs_performed_by FOREIGN KEY (performed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
