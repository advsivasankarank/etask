CREATE TABLE IF NOT EXISTS service_order_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    stage_code VARCHAR(60) NOT NULL,
    stage_name VARCHAR(120) NOT NULL,
    tracking_status ENUM('PENDING','DONE','DOCS_RECD','QUERY_PENDING','QUERY_COMPLIED') NOT NULL DEFAULT 'PENDING',
    remarks TEXT NULL,
    completed_at DATETIME NULL,
    completed_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_service_order_stage (service_order_id, stage_code),
    KEY idx_service_order_milestones_status (service_order_id, tracking_status),
    CONSTRAINT fk_service_order_milestones_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_service_order_milestones_completed_by FOREIGN KEY (completed_by) REFERENCES users(id),
    CONSTRAINT fk_service_order_milestones_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB;

ALTER TABLE workflow_transition_logs
    MODIFY transition_type ENUM('MANUAL_MILESTONE','AUTO_PAYMENT','AUTO_ARN_UPLOAD','AUTO_ACK_UPLOAD','SYSTEM','REOPEN') NOT NULL;
