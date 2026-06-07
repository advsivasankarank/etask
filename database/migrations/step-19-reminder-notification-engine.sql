USE etaxadv_etask;

CREATE TABLE IF NOT EXISTS reminder_templates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(100) NOT NULL,
    reminder_type VARCHAR(80) NOT NULL,
    channel ENUM('IN_APP', 'EMAIL', 'WHATSAPP') NOT NULL DEFAULT 'IN_APP',
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_reminder_templates_code (code),
    KEY idx_reminder_templates_type_channel (reminder_type, channel, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reminder_escalation_rules (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reminder_type VARCHAR(80) NOT NULL,
    day_offset INT NOT NULL,
    target_type ENUM('ASSIGNED_USER', 'CLIENT_CONTACT', 'ROLE') NOT NULL DEFAULT 'ASSIGNED_USER',
    target_role_code VARCHAR(60) NULL,
    channel ENUM('IN_APP', 'EMAIL', 'WHATSAPP') NOT NULL DEFAULT 'IN_APP',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_reminder_escalation_rule (reminder_type, day_offset, target_type, channel, target_role_code),
    KEY idx_reminder_escalation_type_day (reminder_type, day_offset, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reminder_delivery_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reminder_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED NULL,
    recipient_user_id BIGINT UNSIGNED NULL,
    recipient_contact_id BIGINT UNSIGNED NULL,
    recipient_email VARCHAR(190) NULL,
    delivery_channel ENUM('IN_APP', 'EMAIL', 'WHATSAPP') NOT NULL,
    triggered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    delivery_status ENUM('PENDING', 'SENT', 'FAILED', 'SKIPPED') NOT NULL DEFAULT 'PENDING',
    error_message TEXT NULL,
    notification_id BIGINT UNSIGNED NULL,
    PRIMARY KEY (id),
    KEY idx_reminder_delivery_logs_reminder (reminder_id, triggered_at),
    KEY idx_reminder_delivery_logs_status (delivery_status, delivery_channel),
    CONSTRAINT fk_reminder_delivery_logs_reminder FOREIGN KEY (reminder_id) REFERENCES reminders(id),
    CONSTRAINT fk_reminder_delivery_logs_template FOREIGN KEY (template_id) REFERENCES reminder_templates(id),
    CONSTRAINT fk_reminder_delivery_logs_user FOREIGN KEY (recipient_user_id) REFERENCES users(id),
    CONSTRAINT fk_reminder_delivery_logs_contact FOREIGN KEY (recipient_contact_id) REFERENCES client_contacts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE reminders
    MODIFY service_order_id BIGINT UNSIGNED NULL,
    MODIFY reminder_type VARCHAR(80) NOT NULL;

ALTER TABLE reminders
    ADD COLUMN IF NOT EXISTS reminder_code VARCHAR(80) NULL AFTER reminder_type,
    ADD COLUMN IF NOT EXISTS client_id BIGINT UNSIGNED NULL AFTER service_order_id,
    ADD COLUMN IF NOT EXISTS pso_id BIGINT UNSIGNED NULL AFTER client_id,
    ADD COLUMN IF NOT EXISTS invoice_id BIGINT UNSIGNED NULL AFTER pso_id,
    ADD COLUMN IF NOT EXISTS consultant_assignment_id BIGINT UNSIGNED NULL AFTER invoice_id,
    ADD COLUMN IF NOT EXISTS template_id BIGINT UNSIGNED NULL AFTER consultant_assignment_id,
    ADD COLUMN IF NOT EXISTS linked_module VARCHAR(30) NULL AFTER template_id,
    ADD COLUMN IF NOT EXISTS linked_id BIGINT UNSIGNED NULL AFTER linked_module,
    ADD COLUMN IF NOT EXISTS title VARCHAR(255) NULL AFTER linked_id,
    ADD COLUMN IF NOT EXISTS escalation_level INT UNSIGNED NOT NULL DEFAULT 0 AFTER title,
    ADD COLUMN IF NOT EXISTS recipient_contact_id BIGINT UNSIGNED NULL AFTER assigned_to,
    ADD COLUMN IF NOT EXISTS recipient_email VARCHAR(190) NULL AFTER recipient_contact_id,
    ADD COLUMN IF NOT EXISTS dedupe_key VARCHAR(190) NULL AFTER recipient_email,
    ADD COLUMN IF NOT EXISTS resolved_at DATETIME NULL AFTER sent_at,
    ADD COLUMN IF NOT EXISTS last_triggered_at DATETIME NULL AFTER resolved_at,
    ADD COLUMN IF NOT EXISTS created_via ENUM('WORKFLOW', 'SCHEDULER', 'MANUAL') NOT NULL DEFAULT 'SCHEDULER' AFTER notes;

ALTER TABLE reminders
    ADD UNIQUE KEY IF NOT EXISTS uk_reminders_dedupe_key (dedupe_key),
    ADD KEY IF NOT EXISTS idx_reminders_status_type (status, reminder_type, due_at),
    ADD KEY IF NOT EXISTS idx_reminders_client (client_id, reminder_type),
    ADD CONSTRAINT fk_reminders_client FOREIGN KEY (client_id) REFERENCES clients(id),
    ADD CONSTRAINT fk_reminders_pso FOREIGN KEY (pso_id) REFERENCES pre_service_orders(id),
    ADD CONSTRAINT fk_reminders_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    ADD CONSTRAINT fk_reminders_consultant_assignment FOREIGN KEY (consultant_assignment_id) REFERENCES consultant_assignments(id),
    ADD CONSTRAINT fk_reminders_template FOREIGN KEY (template_id) REFERENCES reminder_templates(id),
    ADD CONSTRAINT fk_reminders_recipient_contact FOREIGN KEY (recipient_contact_id) REFERENCES client_contacts(id);

ALTER TABLE reminder_logs
    MODIFY action_type VARCHAR(60) NOT NULL;

ALTER TABLE notifications
    ADD COLUMN IF NOT EXISTS reminder_id BIGINT UNSIGNED NULL AFTER client_contact_id,
    ADD COLUMN IF NOT EXISTS template_id BIGINT UNSIGNED NULL AFTER reminder_id,
    ADD COLUMN IF NOT EXISTS recipient_email VARCHAR(190) NULL AFTER message,
    ADD COLUMN IF NOT EXISTS error_message TEXT NULL AFTER recipient_email,
    ADD COLUMN IF NOT EXISTS payload_json JSON NULL AFTER error_message,
    ADD COLUMN IF NOT EXISTS read_at DATETIME NULL AFTER sent_at,
    ADD COLUMN IF NOT EXISTS delivery_attempts INT UNSIGNED NOT NULL DEFAULT 0 AFTER delivery_status;

ALTER TABLE notifications
    ADD KEY IF NOT EXISTS idx_notifications_reminder (reminder_id, delivery_status),
    ADD CONSTRAINT fk_notifications_reminder FOREIGN KEY (reminder_id) REFERENCES reminders(id),
    ADD CONSTRAINT fk_notifications_template FOREIGN KEY (template_id) REFERENCES reminder_templates(id);

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('reminders.view', 'REMINDERS', 'VIEW', 'View Reminders', 'View reminder registers, notifications, templates, and scheduler status', 1, NOW(), NOW()),
('reminders.create', 'REMINDERS', 'CREATE', 'Create Reminder Rules', 'Create reminder templates, escalation rules, and manual reminder definitions', 1, NOW(), NOW()),
('reminders.edit', 'REMINDERS', 'EDIT', 'Edit Reminder Rules', 'Edit reminder templates, escalation rules, and reminder settings', 1, NOW(), NOW()),
('reminders.send', 'REMINDERS', 'SEND', 'Run Reminder Scheduler', 'Execute the reminder scheduler and send notifications', 1, NOW(), NOW()),
('reminders.report', 'REMINDERS', 'REPORT', 'View Reminder Reports', 'View reminder register, effectiveness, pending, and escalation reports', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('reminders.view', 'reminders.create', 'reminders.edit', 'reminders.send', 'reminders.report')
WHERE r.code IN ('SUPER_ADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('reminders.view', 'reminders.send', 'reminders.report')
WHERE r.code IN ('CRM', 'ASSISTANT_CRM', 'BACKEND_STAFF', 'ACCOUNTS')
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code = 'reminders.view'
WHERE r.code = 'CONSULTANT'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO reminder_templates (code, reminder_type, channel, subject, message, is_active, created_at, updated_at)
VALUES
('PENDING_DOCUMENTS_IN_APP', 'PENDING_DOCUMENTS', 'IN_APP', 'Pending documents', 'Documents are pending for {{client_name}} / {{so_no}}. Current stage: {{current_stage}}.', 1, NOW(), NOW()),
('PENDING_DOCUMENTS_EMAIL', 'PENDING_DOCUMENTS', 'EMAIL', 'Pending documents for {{client_name}}', 'Documents are still pending for {{client_name}} / {{so_no}}. Please upload the required papers.', 1, NOW(), NOW()),
('PENDING_PSO_IN_APP', 'PENDING_PSO', 'IN_APP', 'Pending PSO review', 'PSO {{pso_no}} for {{client_name}} is awaiting review.', 1, NOW(), NOW()),
('PENDING_PSO_EMAIL', 'PENDING_PSO', 'EMAIL', 'PSO review pending: {{pso_no}}', 'PSO {{pso_no}} for {{client_name}} is still pending review.', 1, NOW(), NOW()),
('PENDING_SERVICE_ORDERS_IN_APP', 'PENDING_SERVICE_ORDERS', 'IN_APP', 'Pending service order', 'Service order {{so_no}} for {{client_name}} is still open and requires attention.', 1, NOW(), NOW()),
('PENDING_SERVICE_ORDERS_EMAIL', 'PENDING_SERVICE_ORDERS', 'EMAIL', 'Service order follow-up: {{so_no}}', 'Service order {{so_no}} for {{client_name}} is still pending.', 1, NOW(), NOW()),
('WORKFLOW_FOLLOW_UP_IN_APP', 'WORKFLOW_FOLLOW_UP', 'IN_APP', 'Workflow follow-up due', 'Workflow follow-up is due for {{client_name}} / {{so_no}} at stage {{current_stage}}.', 1, NOW(), NOW()),
('WORKFLOW_FOLLOW_UP_EMAIL', 'WORKFLOW_FOLLOW_UP', 'EMAIL', 'Workflow follow-up due: {{so_no}}', 'Workflow follow-up is due for {{client_name}} / {{so_no}}.', 1, NOW(), NOW()),
('INVOICE_DUE_IN_APP', 'INVOICE_DUE', 'IN_APP', 'Invoice due reminder', 'Invoice {{invoice_no}} for {{client_name}} is due on {{due_at}}.', 1, NOW(), NOW()),
('INVOICE_DUE_EMAIL', 'INVOICE_DUE', 'EMAIL', 'Invoice due: {{invoice_no}}', 'Invoice {{invoice_no}} for {{client_name}} is due on {{due_at}}. Outstanding amount: {{amount}}.', 1, NOW(), NOW()),
('OVERDUE_INVOICE_IN_APP', 'OVERDUE_INVOICE', 'IN_APP', 'Overdue invoice alert', 'Invoice {{invoice_no}} for {{client_name}} is overdue since {{due_at}}.', 1, NOW(), NOW()),
('OVERDUE_INVOICE_EMAIL', 'OVERDUE_INVOICE', 'EMAIL', 'Overdue invoice: {{invoice_no}}', 'Invoice {{invoice_no}} for {{client_name}} is overdue since {{due_at}}. Outstanding amount: {{amount}}.', 1, NOW(), NOW()),
('CONSULTANT_DELIVERABLES_IN_APP', 'CONSULTANT_DELIVERABLES', 'IN_APP', 'Consultant deliverable pending', 'Consultant deliverable is pending for {{client_name}} / {{so_no}}.', 1, NOW(), NOW()),
('CONSULTANT_DELIVERABLES_EMAIL', 'CONSULTANT_DELIVERABLES', 'EMAIL', 'Consultant deliverable pending', 'A consultant deliverable is pending for {{client_name}} / {{so_no}}.', 1, NOW(), NOW()),
('CLIENT_CLARIFICATION_PENDING_IN_APP', 'CLIENT_CLARIFICATION_PENDING', 'IN_APP', 'Client clarification pending', 'Client clarification is pending for {{client_name}} / {{so_no}}.', 1, NOW(), NOW()),
('CLIENT_CLARIFICATION_PENDING_EMAIL', 'CLIENT_CLARIFICATION_PENDING', 'EMAIL', 'Clarification pending: {{so_no}}', 'A clarification response is pending for {{client_name}} / {{so_no}}.', 1, NOW(), NOW()),
('COMPLIANCE_DUE_DATES_IN_APP', 'COMPLIANCE_DUE_DATES', 'IN_APP', 'Compliance due date reminder', 'Compliance due date is approaching for {{client_name}} / {{so_no}} on {{due_at}}.', 1, NOW(), NOW()),
('COMPLIANCE_DUE_DATES_EMAIL', 'COMPLIANCE_DUE_DATES', 'EMAIL', 'Compliance due date: {{so_no}}', 'Compliance due date is approaching for {{client_name}} / {{so_no}} on {{due_at}}.', 1, NOW(), NOW()),
('E_VERIFICATION_IN_APP', 'E_VERIFICATION', 'IN_APP', 'E-verification follow-up', 'ITR e-verification follow-up is pending for {{client_name}} / {{so_no}}.', 1, NOW(), NOW()),
('E_VERIFICATION_EMAIL', 'E_VERIFICATION', 'EMAIL', 'E-verification pending: {{so_no}}', 'ITR e-verification is pending for {{client_name}} / {{so_no}}.', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    reminder_type = VALUES(reminder_type),
    channel = VALUES(channel),
    subject = VALUES(subject),
    message = VALUES(message),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT INTO reminder_escalation_rules (reminder_type, day_offset, target_type, target_role_code, channel, is_active, created_at, updated_at)
VALUES
('PENDING_DOCUMENTS', 1, 'ASSIGNED_USER', NULL, 'IN_APP', 1, NOW(), NOW()),
('PENDING_DOCUMENTS', 3, 'ROLE', 'CRM', 'IN_APP', 1, NOW(), NOW()),
('PENDING_DOCUMENTS', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('PENDING_PSO', 1, 'ROLE', 'CRM', 'IN_APP', 1, NOW(), NOW()),
('PENDING_PSO', 3, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('PENDING_PSO', 7, 'ROLE', 'SUPER_ADMIN', 'IN_APP', 1, NOW(), NOW()),
('PENDING_SERVICE_ORDERS', 1, 'ASSIGNED_USER', NULL, 'IN_APP', 1, NOW(), NOW()),
('PENDING_SERVICE_ORDERS', 3, 'ROLE', 'CRM', 'IN_APP', 1, NOW(), NOW()),
('PENDING_SERVICE_ORDERS', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('WORKFLOW_FOLLOW_UP', 1, 'ASSIGNED_USER', NULL, 'IN_APP', 1, NOW(), NOW()),
('WORKFLOW_FOLLOW_UP', 3, 'ROLE', 'CRM', 'IN_APP', 1, NOW(), NOW()),
('WORKFLOW_FOLLOW_UP', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('INVOICE_DUE', 1, 'ROLE', 'ACCOUNTS', 'IN_APP', 1, NOW(), NOW()),
('INVOICE_DUE', 3, 'CLIENT_CONTACT', NULL, 'EMAIL', 1, NOW(), NOW()),
('INVOICE_DUE', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('OVERDUE_INVOICE', 1, 'ROLE', 'ACCOUNTS', 'IN_APP', 1, NOW(), NOW()),
('OVERDUE_INVOICE', 3, 'CLIENT_CONTACT', NULL, 'EMAIL', 1, NOW(), NOW()),
('OVERDUE_INVOICE', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('CONSULTANT_DELIVERABLES', 1, 'ASSIGNED_USER', NULL, 'IN_APP', 1, NOW(), NOW()),
('CONSULTANT_DELIVERABLES', 3, 'ROLE', 'CRM', 'IN_APP', 1, NOW(), NOW()),
('CONSULTANT_DELIVERABLES', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('CLIENT_CLARIFICATION_PENDING', 1, 'CLIENT_CONTACT', NULL, 'EMAIL', 1, NOW(), NOW()),
('CLIENT_CLARIFICATION_PENDING', 3, 'ROLE', 'CRM', 'IN_APP', 1, NOW(), NOW()),
('CLIENT_CLARIFICATION_PENDING', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('COMPLIANCE_DUE_DATES', 1, 'ASSIGNED_USER', NULL, 'IN_APP', 1, NOW(), NOW()),
('COMPLIANCE_DUE_DATES', 3, 'ROLE', 'CRM', 'IN_APP', 1, NOW(), NOW()),
('COMPLIANCE_DUE_DATES', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('E_VERIFICATION', 1, 'ASSIGNED_USER', NULL, 'IN_APP', 1, NOW(), NOW()),
('E_VERIFICATION', 3, 'ROLE', 'CRM', 'IN_APP', 1, NOW(), NOW()),
('E_VERIFICATION', 7, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    target_role_code = VALUES(target_role_code),
    channel = VALUES(channel),
    is_active = VALUES(is_active),
    updated_at = NOW();
