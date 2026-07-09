-- Step 30: DSC Module — Digital Signature Certificate Control
-- Adds dsc_register, dsc_movements, dsc_usage_logs, dsc_renewals tables.
-- Idempotent: safe to run multiple times.

USE etaxadv_etask;

-- =============================================================================
-- A. Create dsc_register table
-- =============================================================================

CREATE TABLE IF NOT EXISTS dsc_register (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NULL,
    holder_name VARCHAR(255) NOT NULL,
    holder_pan VARCHAR(20) NULL,
    holder_email VARCHAR(190) NULL,
    holder_mobile VARCHAR(20) NULL,
    token_serial_no VARCHAR(255) NULL,
    dsc_type VARCHAR(80) NULL,
    provider_name VARCHAR(255) NULL,
    valid_from DATE NULL,
    valid_to DATE NULL,
    custody_status ENUM('WITH_CLIENT','WITH_OFFICE','WITH_STAFF','RETURNED','EXPIRED','ARCHIVED') NOT NULL DEFAULT 'WITH_CLIENT',
    assigned_user_id BIGINT UNSIGNED NULL,
    storage_location VARCHAR(255) NULL,
    password_status ENUM('NOT_STORED','CLIENT_RETAINED','SECURE_CUSTODY') NOT NULL DEFAULT 'NOT_STORED',
    remarks TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    archived_at DATETIME NULL,
    KEY idx_dsc_register_client (client_id),
    KEY idx_dsc_register_holder (holder_name),
    KEY idx_dsc_register_pan (holder_pan),
    KEY idx_dsc_register_custody (custody_status),
    KEY idx_dsc_register_valid_to (valid_to),
    KEY idx_dsc_register_assigned (assigned_user_id),
    CONSTRAINT fk_dsc_register_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_dsc_register_assigned FOREIGN KEY (assigned_user_id) REFERENCES users(id),
    CONSTRAINT fk_dsc_register_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- B. Create dsc_movements table
-- =============================================================================

CREATE TABLE IF NOT EXISTS dsc_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dsc_id BIGINT UNSIGNED NOT NULL,
    from_user_id BIGINT UNSIGNED NULL,
    to_user_id BIGINT UNSIGNED NULL,
    from_location VARCHAR(255) NULL,
    to_location VARCHAR(255) NULL,
    movement_type ENUM('RECEIVED','ASSIGNED','TRANSFERRED','RETURNED','ARCHIVED') NOT NULL,
    movement_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expected_return_date DATE NULL,
    returned_at DATETIME NULL,
    status ENUM('OPEN','RETURNED','ARCHIVED') NOT NULL DEFAULT 'OPEN',
    purpose VARCHAR(255) NULL,
    remarks TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_dsc_movements_dsc (dsc_id),
    KEY idx_dsc_movements_status (status),
    KEY idx_dsc_movements_type (movement_type),
    CONSTRAINT fk_dsc_movements_dsc FOREIGN KEY (dsc_id) REFERENCES dsc_register(id),
    CONSTRAINT fk_dsc_movements_from_user FOREIGN KEY (from_user_id) REFERENCES users(id),
    CONSTRAINT fk_dsc_movements_to_user FOREIGN KEY (to_user_id) REFERENCES users(id),
    CONSTRAINT fk_dsc_movements_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- C. Create dsc_usage_logs table
-- =============================================================================

CREATE TABLE IF NOT EXISTS dsc_usage_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dsc_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NULL,
    service_order_id BIGINT UNSIGNED NULL,
    used_by BIGINT UNSIGNED NULL,
    usage_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    purpose VARCHAR(255) NOT NULL,
    portal_or_department VARCHAR(255) NULL,
    filing_reference VARCHAR(255) NULL,
    acknowledgement_no VARCHAR(255) NULL,
    remarks TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_dsc_usage_dsc (dsc_id),
    KEY idx_dsc_usage_client (client_id),
    KEY idx_dsc_usage_so (service_order_id),
    KEY idx_dsc_usage_date (usage_date),
    CONSTRAINT fk_dsc_usage_dsc FOREIGN KEY (dsc_id) REFERENCES dsc_register(id),
    CONSTRAINT fk_dsc_usage_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_dsc_usage_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_dsc_usage_used_by FOREIGN KEY (used_by) REFERENCES users(id),
    CONSTRAINT fk_dsc_usage_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- D. Create dsc_renewals table
-- =============================================================================

CREATE TABLE IF NOT EXISTS dsc_renewals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dsc_id BIGINT UNSIGNED NOT NULL,
    renewal_due_date DATE NULL,
    renewal_status ENUM('NOT_DUE','DUE','IN_PROGRESS','RENEWED','EXPIRED','CANCELLED') NOT NULL DEFAULT 'NOT_DUE',
    renewal_requested_at DATETIME NULL,
    renewed_at DATETIME NULL,
    new_valid_from DATE NULL,
    new_valid_to DATE NULL,
    remarks TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_dsc_renewals_dsc (dsc_id),
    KEY idx_dsc_renewals_status (renewal_status),
    KEY idx_dsc_renewals_due (renewal_due_date),
    CONSTRAINT fk_dsc_renewals_dsc FOREIGN KEY (dsc_id) REFERENCES dsc_register(id),
    CONSTRAINT fk_dsc_renewals_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
