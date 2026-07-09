-- Step 26: Foundation Expansion
-- Adds task management columns, daily work reports table, new service types, and generic workflows.
-- Idempotent: safe to run multiple times.

USE etaxadv_etask;

-- =============================================================================
-- A. Expand service_order_tasks table
-- =============================================================================

ALTER TABLE service_order_tasks
    ADD COLUMN IF NOT EXISTS reviewer_id BIGINT UNSIGNED NULL AFTER created_by,
    ADD COLUMN IF NOT EXISTS priority ENUM('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL DEFAULT 'MEDIUM' AFTER task_type,
    ADD COLUMN IF NOT EXISTS submitted_at DATETIME NULL AFTER completed_at,
    ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL AFTER submitted_at,
    ADD COLUMN IF NOT EXISTS review_remarks TEXT NULL AFTER reviewed_at,
    ADD COLUMN IF NOT EXISTS completion_proof_document_id BIGINT UNSIGNED NULL AFTER review_remarks,
    ADD COLUMN IF NOT EXISTS approved_by BIGINT UNSIGNED NULL AFTER completion_proof_document_id,
    ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL AFTER approved_by;

-- Add indexes (IF NOT EXISTS supported in MySQL 8.0.19+ and MariaDB 10.5+)

ALTER TABLE service_order_tasks
    ADD INDEX IF NOT EXISTS idx_so_tasks_reviewer (reviewer_id),
    ADD INDEX IF NOT EXISTS idx_so_tasks_priority (priority),
    ADD INDEX IF NOT EXISTS idx_so_tasks_due_priority (due_at, priority);

-- Add foreign keys using prepared statements for idempotency

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'service_order_tasks' AND CONSTRAINT_NAME = 'fk_so_tasks_reviewer');
SET @fk_sql = IF(@fk_exists = 0, 'ALTER TABLE service_order_tasks ADD CONSTRAINT fk_so_tasks_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id)', 'SELECT 1');
PREPARE fk_stmt FROM @fk_sql;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'service_order_tasks' AND CONSTRAINT_NAME = 'fk_so_tasks_proof_doc');
SET @fk_sql = IF(@fk_exists = 0, 'ALTER TABLE service_order_tasks ADD CONSTRAINT fk_so_tasks_proof_doc FOREIGN KEY (completion_proof_document_id) REFERENCES documents(id)', 'SELECT 1');
PREPARE fk_stmt FROM @fk_sql;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'service_order_tasks' AND CONSTRAINT_NAME = 'fk_so_tasks_approved_by');
SET @fk_sql = IF(@fk_exists = 0, 'ALTER TABLE service_order_tasks ADD CONSTRAINT fk_so_tasks_approved_by FOREIGN KEY (approved_by) REFERENCES users(id)', 'SELECT 1');
PREPARE fk_stmt FROM @fk_sql;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

-- =============================================================================
-- B. Create daily_work_reports table
-- =============================================================================

CREATE TABLE IF NOT EXISTS daily_work_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    attendance_session_id BIGINT UNSIGNED NULL,
    report_date DATE NOT NULL,
    work_done_today TEXT NOT NULL,
    pending_work TEXT NULL,
    tomorrow_plan TEXT NULL,
    issues_faced TEXT NULL,
    admin_remarks TEXT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    status ENUM('DRAFT','SUBMITTED','REVIEWED','REOPENED') NOT NULL DEFAULT 'SUBMITTED',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_daily_work_report_user_date (user_id, report_date),
    KEY idx_daily_work_report_date (report_date),
    KEY idx_daily_work_report_status (status),
    KEY idx_daily_work_report_reviewed_by (reviewed_by),
    KEY idx_daily_work_report_attendance (attendance_session_id),
    CONSTRAINT fk_daily_work_report_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_daily_work_report_session FOREIGN KEY (attendance_session_id) REFERENCES attendance_sessions(id),
    CONSTRAINT fk_daily_work_report_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- C. Seed Missing Service Types
-- =============================================================================

INSERT INTO service_types (code, name, service_group, requires_payment_stage, requires_e_verification, default_sla_days, description, is_active, created_at)
VALUES
('ACCOUNTING', 'Accounting / Bookkeeping', 'OTHER', 1, 0, 3, 'Accounting, bookkeeping, and financial statement preparation services', 1, NOW()),
('ROC_MCA', 'ROC / MCA Compliance', 'OTHER', 1, 0, 3, 'Registrar of Companies and Ministry of Corporate Affairs compliance filings', 1, NOW()),
('LEGAL', 'Legal Services', 'OTHER', 1, 0, 5, 'Legal consultation, drafting, and advisory services', 1, NOW()),
('LABOUR_HR', 'Labour / HR Compliance', 'OTHER', 1, 0, 3, 'Labour law compliance, PF, ESI, professional tax, and HR advisory', 1, NOW()),
('REGISTRATION', 'Registration Services', 'OTHER', 1, 0, 3, 'Business registrations, licenses, and regulatory filings', 1, NOW()),
('CONSULTANCY', 'Consultancy / Advisory', 'OTHER', 0, 0, 2, 'General consultancy and advisory services', 1, NOW()),
('NOTICE_REPLY', 'Notice Reply / Department Proceedings', 'OTHER', 1, 0, 5, 'Reply to government notices, departmental proceedings, and representations', 1, NOW()),
('APPEAL_LITIGATION', 'Appeal / Litigation Support', 'OTHER', 1, 0, 7, 'Appeal filings, tribunal matters, and litigation support', 1, NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description);

-- =============================================================================
-- D. Workflow Definitions for New Service Types
-- =============================================================================

-- Create workflow definitions for new service types (one workflow per type, version 1)
INSERT INTO workflow_definitions (service_type_id, version_no, name, is_active, created_at)
SELECT st.id, 1, CONCAT(st.name, ' Default Workflow'), 1, NOW()
FROM service_types st
WHERE st.code IN ('ACCOUNTING','ROC_MCA','LEGAL','LABOUR_HR','REGISTRATION','CONSULTANCY','NOTICE_REPLY','APPEAL_LITIGATION')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    is_active = VALUES(is_active);

-- Create generic workflow stages for each new service type workflow
-- Using stage_group COMMON and CLOSURE to avoid ENUM changes
INSERT INTO workflow_stage_definitions (workflow_definition_id, stage_code, stage_name, stage_group, sort_order, is_milestone_click_required, auto_trigger_on, is_terminal, created_at)
SELECT wd.id, stage_def.stage_code, stage_def.stage_name, stage_def.stage_group, stage_def.sort_order, stage_def.is_milestone_click_required, stage_def.auto_trigger_on, stage_def.is_terminal, NOW()
FROM workflow_definitions wd
INNER JOIN service_types st ON st.id = wd.service_type_id
INNER JOIN (
    SELECT 'DOCUMENT_COLLECTION' AS stage_code, 'Document Collection' AS stage_name, 'COMMON' AS stage_group, 10 AS sort_order, 1 AS is_milestone_click_required, NULL AS auto_trigger_on, 0 AS is_terminal
    UNION ALL SELECT 'PREPARATION', 'Preparation / Drafting', 'COMMON', 20, 1, NULL, 0
    UNION ALL SELECT 'REVIEW', 'Review', 'COMMON', 30, 1, NULL, 0
    UNION ALL SELECT 'CLIENT_CONFIRMATION', 'Client Confirmation', 'COMMON', 40, 1, NULL, 0
    UNION ALL SELECT 'FILING_OR_DELIVERY', 'Filing / Delivery', 'COMMON', 50, 1, NULL, 0
    UNION ALL SELECT 'BILLING', 'Billing', 'COMMON', 60, 1, NULL, 0
    UNION ALL SELECT 'CLOSURE', 'Closure', 'CLOSURE', 70, 1, NULL, 1
) stage_def
WHERE st.code IN ('ACCOUNTING','ROC_MCA','LEGAL','LABOUR_HR','REGISTRATION','CONSULTANCY','NOTICE_REPLY','APPEAL_LITIGATION')
ON DUPLICATE KEY UPDATE
    stage_name = VALUES(stage_name),
    sort_order = VALUES(sort_order),
    is_milestone_click_required = VALUES(is_milestone_click_required);
