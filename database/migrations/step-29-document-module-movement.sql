-- Step 29: Document Module — Request & Movement Tracking
-- Adds document_requests, document_movements tables, and verification columns to documents.
-- Idempotent: safe to run multiple times.

USE etaxadv_etask;

-- =============================================================================
-- A. Add verification columns to documents table (if missing)
-- =============================================================================

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = 'verification_status');
SET @col_sql = IF(@col_exists = 0, 'ALTER TABLE documents ADD COLUMN verification_status ENUM(\'PENDING\',\'VERIFIED\',\'REJECTED\') NOT NULL DEFAULT \'PENDING\' AFTER archived_at', 'SELECT 1');
PREPARE col_stmt FROM @col_sql;
EXECUTE col_stmt;
DEALLOCATE PREPARE col_stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = 'verified_by');
SET @col_sql = IF(@col_exists = 0, 'ALTER TABLE documents ADD COLUMN verified_by BIGINT UNSIGNED NULL AFTER verification_status', 'SELECT 1');
PREPARE col_stmt FROM @col_sql;
EXECUTE col_stmt;
DEALLOCATE PREPARE col_stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = 'verified_at');
SET @col_sql = IF(@col_exists = 0, 'ALTER TABLE documents ADD COLUMN verified_at DATETIME NULL AFTER verified_by', 'SELECT 1');
PREPARE col_stmt FROM @col_sql;
EXECUTE col_stmt;
DEALLOCATE PREPARE col_stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = 'returned_at');
SET @col_sql = IF(@col_exists = 0, 'ALTER TABLE documents ADD COLUMN returned_at DATETIME NULL AFTER verified_at', 'SELECT 1');
PREPARE col_stmt FROM @col_sql;
EXECUTE col_stmt;
DEALLOCATE PREPARE col_stmt;

-- Add foreign keys for new columns (if missing)
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND CONSTRAINT_NAME = 'fk_documents_verified_by');
SET @fk_sql = IF(@fk_exists = 0, 'ALTER TABLE documents ADD CONSTRAINT fk_documents_verified_by FOREIGN KEY (verified_by) REFERENCES users(id)', 'SELECT 1');
PREPARE fk_stmt FROM @fk_sql;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

-- =============================================================================
-- B. Create document_requests table
-- =============================================================================

CREATE TABLE IF NOT EXISTS document_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    service_order_id BIGINT UNSIGNED NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    document_title VARCHAR(255) NOT NULL,
    document_category VARCHAR(80) NULL,
    description TEXT NULL,
    due_date DATE NULL,
    status ENUM('REQUESTED','RECEIVED','VERIFIED','REJECTED','CANCELLED') NOT NULL DEFAULT 'REQUESTED',
    received_document_id BIGINT UNSIGNED NULL,
    remarks TEXT NULL,
    received_at DATETIME NULL,
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_document_requests_client (client_id),
    KEY idx_document_requests_so (service_order_id),
    KEY idx_document_requests_status (status),
    KEY idx_document_requests_requested_by (requested_by),
    CONSTRAINT fk_document_requests_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_document_requests_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_document_requests_requested_by FOREIGN KEY (requested_by) REFERENCES users(id),
    CONSTRAINT fk_document_requests_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id),
    CONSTRAINT fk_document_requests_received_doc FOREIGN KEY (received_document_id) REFERENCES documents(id),
    CONSTRAINT fk_document_requests_verified_by FOREIGN KEY (verified_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- C. Create document_movements table
-- =============================================================================

CREATE TABLE IF NOT EXISTS document_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NULL,
    service_order_id BIGINT UNSIGNED NULL,
    from_user_id BIGINT UNSIGNED NULL,
    to_user_id BIGINT UNSIGNED NULL,
    from_location VARCHAR(255) NULL,
    to_location VARCHAR(255) NULL,
    movement_type ENUM('RECEIVED','ASSIGNED','TRANSFERRED','USED_FOR_WORK','RETURNED','ARCHIVED') NOT NULL,
    purpose VARCHAR(255) NULL,
    movement_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expected_return_date DATE NULL,
    returned_at DATETIME NULL,
    status ENUM('OPEN','RETURNED','ARCHIVED') NOT NULL DEFAULT 'OPEN',
    remarks TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_document_movements_document (document_id),
    KEY idx_document_movements_client (client_id),
    KEY idx_document_movements_so (service_order_id),
    KEY idx_document_movements_status (status),
    KEY idx_document_movements_type (movement_type),
    CONSTRAINT fk_document_movements_document FOREIGN KEY (document_id) REFERENCES documents(id),
    CONSTRAINT fk_document_movements_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_document_movements_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_document_movements_from_user FOREIGN KEY (from_user_id) REFERENCES users(id),
    CONSTRAINT fk_document_movements_to_user FOREIGN KEY (to_user_id) REFERENCES users(id),
    CONSTRAINT fk_document_movements_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
