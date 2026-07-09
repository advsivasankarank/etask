-- Step 32: Accounts Module — Follow-up Tracking
-- Adds accounts_followups table for collection follow-up tracking.
-- Idempotent: safe to run multiple times.

USE etaxadv_etask;

-- =============================================================================
-- A. Create accounts_followups table
-- =============================================================================

CREATE TABLE IF NOT EXISTS accounts_followups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NULL,
    invoice_id BIGINT UNSIGNED NULL,
    service_order_id BIGINT UNSIGNED NULL,
    followup_date DATE NOT NULL,
    followup_mode VARCHAR(80) NULL,
    followup_note TEXT NULL,
    next_followup_date DATE NULL,
    status ENUM('OPEN','FOLLOWED_UP','PROMISED','DISPUTED','CLOSED') NOT NULL DEFAULT 'OPEN',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_accounts_followups_client (client_id),
    KEY idx_accounts_followups_invoice (invoice_id),
    KEY idx_accounts_followups_status (status),
    KEY idx_accounts_followups_date (followup_date),
    CONSTRAINT fk_accounts_followups_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_accounts_followups_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    CONSTRAINT fk_accounts_followups_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_accounts_followups_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
