USE etaxadv_etask;

ALTER TABLE clients
    ADD COLUMN assigned_crm_id BIGINT UNSIGNED NULL AFTER default_company_id,
    ADD CONSTRAINT fk_clients_assigned_crm FOREIGN KEY (assigned_crm_id) REFERENCES users(id),
    ADD UNIQUE KEY uk_clients_pan (pan);
