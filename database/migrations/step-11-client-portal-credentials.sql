USE etaxadv_etask;

ALTER TABLE clients
    ADD COLUMN aadhaar_no VARCHAR(20) NULL AFTER gstin;

CREATE TABLE client_portal_credentials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    portal_code VARCHAR(50) NOT NULL,
    user_identifier VARCHAR(190) NULL,
    password_ciphertext TEXT NULL,
    password_iv VARCHAR(255) NULL,
    portal_url VARCHAR(255) NULL,
    remarks VARCHAR(255) NULL,
    last_verified_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_client_portal (client_id, portal_code),
    KEY idx_client_portal_client (client_id),
    CONSTRAINT fk_client_portal_credentials_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_client_portal_credentials_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_client_portal_credentials_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB;
