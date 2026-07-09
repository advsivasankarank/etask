-- Step 31: Workforce Module — Consultant Register
-- Adds consultants table for external workforce management.
-- Idempotent: safe to run multiple times.

USE etaxadv_etask;

-- =============================================================================
-- A. Create consultants table
-- =============================================================================

CREATE TABLE IF NOT EXISTS consultants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    firm_name VARCHAR(255) NULL,
    mobile VARCHAR(20) NULL,
    email VARCHAR(190) NULL,
    pan VARCHAR(20) NULL,
    gstin VARCHAR(30) NULL,
    address TEXT NULL,
    expertise VARCHAR(255) NULL,
    status ENUM('ACTIVE','INACTIVE','BLACKLISTED') NOT NULL DEFAULT 'ACTIVE',
    remarks TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    archived_at DATETIME NULL,
    KEY idx_consultants_name (name),
    KEY idx_consultants_status (status),
    CONSTRAINT fk_consultants_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
