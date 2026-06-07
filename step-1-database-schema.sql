-- Compliance Management System
-- Step 1: Full Database Schema
-- Target: PHP 8.x + MySQL 8.x (XAMPP compatible)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS etaxadv_etask
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE etaxadv_etask;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS reminder_logs;
DROP TABLE IF EXISTS reminders;
DROP TABLE IF EXISTS attendance_activity_logs;
DROP TABLE IF EXISTS attendance_sessions;
DROP TABLE IF EXISTS payment_receipt_items;
DROP TABLE IF EXISTS receipts;
DROP TABLE IF EXISTS payment_allocations;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS disbursements;
DROP TABLE IF EXISTS consultant_payments;
DROP TABLE IF EXISTS consultant_bills;
DROP TABLE IF EXISTS consultant_deliverables;
DROP TABLE IF EXISTS consultant_assignments;
DROP TABLE IF EXISTS workflow_transition_logs;
DROP TABLE IF EXISTS workflow_stage_history;
DROP TABLE IF EXISTS service_order_queries;
DROP TABLE IF EXISTS service_order_tasks;
DROP TABLE IF EXISTS service_order_closures;
DROP TABLE IF EXISTS service_order_status_flags;
DROP TABLE IF EXISTS service_orders;
DROP TABLE IF EXISTS pso_reviews;
DROP TABLE IF EXISTS pso_documents;
DROP TABLE IF EXISTS pre_service_orders;
DROP TABLE IF EXISTS document_versions;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS workflow_stage_definitions;
DROP TABLE IF EXISTS workflow_definitions;
DROP TABLE IF EXISTS service_types;
DROP TABLE IF EXISTS numbering_sequences;
DROP TABLE IF EXISTS company_service_type_map;
DROP TABLE IF EXISTS financial_years;
DROP TABLE IF EXISTS user_company_map;
DROP TABLE IF EXISTS user_role_map;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS client_contacts;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS statuses;
DROP TABLE IF EXISTS countries;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE countries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    iso_code CHAR(2) NOT NULL UNIQUE,
    phone_code VARCHAR(10) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(60) NOT NULL,
    code VARCHAR(60) NOT NULL,
    label VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_statuses_category_code (category, code)
) ENGINE=InnoDB;

CREATE TABLE companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    legal_name VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    company_type ENUM('ADVOCATE','PRIVATE_LIMITED','PARTNERSHIP','PROPRIETORSHIP','OTHER') NOT NULL DEFAULT 'OTHER',
    pan VARCHAR(20) NULL,
    gstin VARCHAR(20) NULL,
    tan VARCHAR(20) NULL,
    email VARCHAR(190) NULL,
    mobile VARCHAR(20) NULL,
    phone VARCHAR(20) NULL,
    address_line1 VARCHAR(255) NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(120) NULL,
    state_name VARCHAR(120) NULL,
    postal_code VARCHAR(20) NULL,
    country_id BIGINT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_companies_country FOREIGN KEY (country_id) REFERENCES countries(id)
) ENGINE=InnoDB;

CREATE TABLE financial_years (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    label VARCHAR(30) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_code VARCHAR(40) NOT NULL UNIQUE,
    client_type ENUM('INDIVIDUAL','PROPRIETORSHIP','PARTNERSHIP','LLP','PRIVATE_LIMITED','PUBLIC_LIMITED','TRUST','SOCIETY','OTHER') NOT NULL,
    legal_name VARCHAR(255) NOT NULL,
    trade_name VARCHAR(255) NULL,
    pan VARCHAR(20) NULL,
    tan VARCHAR(20) NULL,
    gstin VARCHAR(20) NULL,
    aadhaar_last4 CHAR(4) NULL,
    email VARCHAR(190) NULL,
    mobile VARCHAR(20) NULL,
    alternate_mobile VARCHAR(20) NULL,
    landline VARCHAR(20) NULL,
    address_line1 VARCHAR(255) NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(120) NULL,
    state_name VARCHAR(120) NULL,
    postal_code VARCHAR(20) NULL,
    country_id BIGINT UNSIGNED NULL,
    default_company_id BIGINT UNSIGNED NULL,
    onboarded_at DATETIME NULL,
    archived_at DATETIME NULL,
    archive_reason VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_clients_country FOREIGN KEY (country_id) REFERENCES countries(id),
    CONSTRAINT fk_clients_default_company FOREIGN KEY (default_company_id) REFERENCES companies(id),
    KEY idx_clients_pan (pan),
    KEY idx_clients_tan (tan),
    KEY idx_clients_mobile (mobile),
    KEY idx_clients_legal_name (legal_name)
) ENGINE=InnoDB;

CREATE TABLE client_contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    contact_name VARCHAR(190) NOT NULL,
    designation VARCHAR(120) NULL,
    email VARCHAR(190) NULL,
    mobile VARCHAR(20) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    can_login TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_client_contacts_client FOREIGN KEY (client_id) REFERENCES clients(id),
    KEY idx_client_contacts_client (client_id)
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(40) NULL UNIQUE,
    client_contact_id BIGINT UNSIGNED NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(190) NOT NULL,
    email VARCHAR(190) NOT NULL,
    mobile VARCHAR(20) NULL,
    auth_type ENUM('LOCAL') NOT NULL DEFAULT 'LOCAL',
    must_change_password TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    last_password_changed_at DATETIME NULL,
    failed_login_attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_client_contact FOREIGN KEY (client_contact_id) REFERENCES client_contacts(id)
) ENGINE=InnoDB;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(60) NOT NULL UNIQUE,
    label VARCHAR(120) NOT NULL,
    scope ENUM('SYSTEM','PORTAL') NOT NULL DEFAULT 'SYSTEM',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_code VARCHAR(60) NOT NULL,
    action_code VARCHAR(60) NOT NULL,
    label VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_permissions_module_action (module_code, action_code)
) ENGINE=InnoDB;

CREATE TABLE user_role_map (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_role (user_id, role_id),
    CONSTRAINT fk_user_role_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_user_role_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_user_role_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE user_company_map (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_company (user_id, company_id),
    CONSTRAINT fk_user_company_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_user_company_company FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB;

CREATE TABLE service_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    service_group ENUM('ITR','GST','TDS','OTHER') NOT NULL,
    requires_payment_stage TINYINT(1) NOT NULL DEFAULT 0,
    requires_e_verification TINYINT(1) NOT NULL DEFAULT 0,
    default_sla_days INT NOT NULL DEFAULT 2,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE company_service_type_map (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    service_type_id BIGINT UNSIGNED NOT NULL,
    is_default_company TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_company_service_type (company_id, service_type_id),
    CONSTRAINT fk_company_service_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_company_service_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id)
) ENGINE=InnoDB;

CREATE TABLE numbering_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    financial_year_id BIGINT UNSIGNED NOT NULL,
    sequence_type ENUM('PSO','SO','INV','RCPT') NOT NULL,
    last_number INT NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_numbering_sequence (company_id, financial_year_id, sequence_type),
    CONSTRAINT fk_numbering_sequence_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_numbering_sequence_financial_year FOREIGN KEY (financial_year_id) REFERENCES financial_years(id)
) ENGINE=InnoDB;

CREATE TABLE workflow_definitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_type_id BIGINT UNSIGNED NOT NULL,
    version_no INT NOT NULL DEFAULT 1,
    name VARCHAR(190) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NULL,
    UNIQUE KEY uk_workflow_def (service_type_id, version_no),
    CONSTRAINT fk_workflow_def_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id),
    CONSTRAINT fk_workflow_def_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE workflow_stage_definitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_definition_id BIGINT UNSIGNED NOT NULL,
    stage_code VARCHAR(60) NOT NULL,
    stage_name VARCHAR(120) NOT NULL,
    stage_group ENUM('COMMON','ITR','GST','TDS','CLOSURE') NOT NULL DEFAULT 'COMMON',
    sort_order INT NOT NULL,
    is_milestone_click_required TINYINT(1) NOT NULL DEFAULT 1,
    auto_trigger_on VARCHAR(60) NULL,
    is_terminal TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_workflow_stage_def (workflow_definition_id, stage_code),
    CONSTRAINT fk_workflow_stage_def_workflow FOREIGN KEY (workflow_definition_id) REFERENCES workflow_definitions(id)
) ENGINE=InnoDB;

CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    linked_module ENUM('PSO','SO','CONSULTANT','BILLING','CLIENT','GENERAL') NOT NULL,
    linked_id BIGINT UNSIGNED NOT NULL,
    document_category VARCHAR(80) NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    current_version_no INT NOT NULL DEFAULT 1,
    latest_file_name VARCHAR(255) NOT NULL,
    latest_file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) NULL,
    file_size BIGINT UNSIGNED NULL,
    checksum_sha256 CHAR(64) NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    archived_at DATETIME NULL,
    KEY idx_documents_module (linked_module, linked_id),
    KEY idx_documents_client (client_id),
    CONSTRAINT fk_documents_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_documents_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE document_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version_no INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) NULL,
    file_size BIGINT UNSIGNED NULL,
    checksum_sha256 CHAR(64) NULL,
    change_note VARCHAR(255) NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_document_version (document_id, version_no),
    CONSTRAINT fk_document_versions_document FOREIGN KEY (document_id) REFERENCES documents(id),
    CONSTRAINT fk_document_versions_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE pre_service_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pso_no VARCHAR(50) NOT NULL UNIQUE,
    client_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    financial_year_id BIGINT UNSIGNED NOT NULL,
    service_type_id BIGINT UNSIGNED NOT NULL,
    requested_for_period VARCHAR(60) NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    requested_by_contact_id BIGINT UNSIGNED NOT NULL,
    current_status ENUM('DRAFT','SUBMITTED','UNDER_REVIEW','APPROVED','REJECTED','CONVERTED_TO_SO') NOT NULL DEFAULT 'SUBMITTED',
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    admin_rejected_by BIGINT UNSIGNED NULL,
    admin_rejected_at DATETIME NULL,
    rejection_reason TEXT NULL,
    approved_at DATETIME NULL,
    converted_so_id BIGINT UNSIGNED NULL,
    notification_sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pso_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_pso_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_pso_financial_year FOREIGN KEY (financial_year_id) REFERENCES financial_years(id),
    CONSTRAINT fk_pso_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id),
    CONSTRAINT fk_pso_requested_by_contact FOREIGN KEY (requested_by_contact_id) REFERENCES client_contacts(id),
    CONSTRAINT fk_pso_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id),
    CONSTRAINT fk_pso_admin_rejected_by FOREIGN KEY (admin_rejected_by) REFERENCES users(id),
    KEY idx_pso_client (client_id),
    KEY idx_pso_status (current_status)
) ENGINE=InnoDB;

CREATE TABLE pso_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pso_id BIGINT UNSIGNED NOT NULL,
    document_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pso_document (pso_id, document_id),
    CONSTRAINT fk_pso_documents_pso FOREIGN KEY (pso_id) REFERENCES pre_service_orders(id),
    CONSTRAINT fk_pso_documents_document FOREIGN KEY (document_id) REFERENCES documents(id)
) ENGINE=InnoDB;

CREATE TABLE pso_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pso_id BIGINT UNSIGNED NOT NULL,
    review_action ENUM('SUBMITTED','COMMENTED','RECOMMENDED_APPROVAL','RECOMMENDED_REJECTION','APPROVED','REJECTED') NOT NULL,
    remarks TEXT NULL,
    acted_by BIGINT UNSIGNED NOT NULL,
    acted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pso_reviews_pso FOREIGN KEY (pso_id) REFERENCES pre_service_orders(id),
    CONSTRAINT fk_pso_reviews_acted_by FOREIGN KEY (acted_by) REFERENCES users(id),
    KEY idx_pso_reviews_pso (pso_id, acted_at)
) ENGINE=InnoDB;

CREATE TABLE service_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    so_no VARCHAR(50) NOT NULL UNIQUE,
    client_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    financial_year_id BIGINT UNSIGNED NOT NULL,
    pre_service_order_id BIGINT UNSIGNED NULL,
    service_type_id BIGINT UNSIGNED NOT NULL,
    workflow_definition_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    priority_level ENUM('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL DEFAULT 'MEDIUM',
    assigned_crm_id BIGINT UNSIGNED NULL,
    assigned_assistant_crm_id BIGINT UNSIGNED NULL,
    assigned_backend_id BIGINT UNSIGNED NULL,
    assigned_deo_id BIGINT UNSIGNED NULL,
    current_stage_code VARCHAR(60) NOT NULL,
    procedural_closed_at DATETIME NULL,
    accounting_closed_at DATETIME NULL,
    final_closed_at DATETIME NULL,
    final_closed_by BIGINT UNSIGNED NULL,
    is_locked TINYINT(1) NOT NULL DEFAULT 0,
    lock_reason VARCHAR(255) NULL,
    admin_override_unlocked_by BIGINT UNSIGNED NULL,
    admin_override_unlocked_at DATETIME NULL,
    sla_due_at DATETIME NULL,
    escalated_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    archived_at DATETIME NULL,
    CONSTRAINT fk_so_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_so_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_so_financial_year FOREIGN KEY (financial_year_id) REFERENCES financial_years(id),
    CONSTRAINT fk_so_pso FOREIGN KEY (pre_service_order_id) REFERENCES pre_service_orders(id),
    CONSTRAINT fk_so_service_type FOREIGN KEY (service_type_id) REFERENCES service_types(id),
    CONSTRAINT fk_so_workflow_definition FOREIGN KEY (workflow_definition_id) REFERENCES workflow_definitions(id),
    CONSTRAINT fk_so_assigned_crm FOREIGN KEY (assigned_crm_id) REFERENCES users(id),
    CONSTRAINT fk_so_assigned_assistant_crm FOREIGN KEY (assigned_assistant_crm_id) REFERENCES users(id),
    CONSTRAINT fk_so_assigned_backend FOREIGN KEY (assigned_backend_id) REFERENCES users(id),
    CONSTRAINT fk_so_assigned_deo FOREIGN KEY (assigned_deo_id) REFERENCES users(id),
    CONSTRAINT fk_so_final_closed_by FOREIGN KEY (final_closed_by) REFERENCES users(id),
    CONSTRAINT fk_so_admin_override_unlocked_by FOREIGN KEY (admin_override_unlocked_by) REFERENCES users(id),
    CONSTRAINT fk_so_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_so_client (client_id),
    KEY idx_so_company_fy (company_id, financial_year_id),
    KEY idx_so_current_stage (current_stage_code),
    KEY idx_so_assigned_crm (assigned_crm_id),
    KEY idx_so_sla_due (sla_due_at)
) ENGINE=InnoDB;

ALTER TABLE pre_service_orders
    ADD CONSTRAINT fk_pso_converted_so FOREIGN KEY (converted_so_id) REFERENCES service_orders(id);

CREATE TABLE service_order_status_flags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    is_document_pending TINYINT(1) NOT NULL DEFAULT 1,
    is_payment_pending TINYINT(1) NOT NULL DEFAULT 0,
    is_paid TINYINT(1) NOT NULL DEFAULT 0,
    is_filing_done TINYINT(1) NOT NULL DEFAULT 0,
    is_acknowledgement_captured TINYINT(1) NOT NULL DEFAULT 0,
    is_e_verification_required TINYINT(1) NOT NULL DEFAULT 0,
    is_e_verification_done TINYINT(1) NOT NULL DEFAULT 0,
    e_verification_due_date DATE NULL,
    is_overdue TINYINT(1) NOT NULL DEFAULT 0,
    is_client_paid TINYINT(1) NOT NULL DEFAULT 0,
    is_consultant_payment_pending TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_so_status_flag (service_order_id),
    CONSTRAINT fk_so_status_flag_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id)
) ENGINE=InnoDB;

CREATE TABLE service_order_closures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    closure_type ENUM('PROCEDURAL','ACCOUNTING','FINAL') NOT NULL,
    closure_status ENUM('PENDING','COMPLETED','BLOCKED') NOT NULL DEFAULT 'PENDING',
    closure_at DATETIME NULL,
    closed_by BIGINT UNSIGNED NULL,
    block_reason VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_so_closure_type (service_order_id, closure_type),
    CONSTRAINT fk_so_closure_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_so_closure_closed_by FOREIGN KEY (closed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE service_order_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    task_title VARCHAR(255) NOT NULL,
    task_type ENUM('DOCUMENT','PREPARATION','REVIEW','FILING','FOLLOW_UP','BILLING','CONSULTANT','GENERAL') NOT NULL DEFAULT 'GENERAL',
    assigned_to BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    due_at DATETIME NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    status ENUM('OPEN','IN_PROGRESS','DONE','BLOCKED') NOT NULL DEFAULT 'OPEN',
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_so_tasks_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_so_tasks_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id),
    CONSTRAINT fk_so_tasks_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_so_tasks_so (service_order_id, status)
) ENGINE=InnoDB;

CREATE TABLE service_order_queries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    raised_by BIGINT UNSIGNED NOT NULL,
    addressed_to_user_id BIGINT UNSIGNED NULL,
    addressed_to_contact_id BIGINT UNSIGNED NULL,
    query_text TEXT NOT NULL,
    response_text TEXT NULL,
    raised_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME NULL,
    status ENUM('OPEN','RESPONDED','CLOSED') NOT NULL DEFAULT 'OPEN',
    CONSTRAINT fk_so_queries_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_so_queries_raised_by FOREIGN KEY (raised_by) REFERENCES users(id),
    CONSTRAINT fk_so_queries_addressed_user FOREIGN KEY (addressed_to_user_id) REFERENCES users(id),
    CONSTRAINT fk_so_queries_addressed_contact FOREIGN KEY (addressed_to_contact_id) REFERENCES client_contacts(id),
    KEY idx_so_queries_so (service_order_id, status)
) ENGINE=InnoDB;

CREATE TABLE workflow_stage_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    stage_code VARCHAR(60) NOT NULL,
    stage_name VARCHAR(120) NOT NULL,
    entered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    exited_at DATETIME NULL,
    entered_by BIGINT UNSIGNED NOT NULL,
    remarks TEXT NULL,
    CONSTRAINT fk_workflow_stage_history_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_workflow_stage_history_entered_by FOREIGN KEY (entered_by) REFERENCES users(id),
    KEY idx_workflow_stage_history_so (service_order_id, entered_at)
) ENGINE=InnoDB;

CREATE TABLE workflow_transition_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    from_stage_code VARCHAR(60) NULL,
    to_stage_code VARCHAR(60) NOT NULL,
    transition_type ENUM('MANUAL_MILESTONE','AUTO_PAYMENT','AUTO_ARN_UPLOAD','AUTO_ACK_UPLOAD','SYSTEM') NOT NULL,
    transition_notes TEXT NULL,
    triggered_by BIGINT UNSIGNED NULL,
    triggered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_workflow_transition_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_workflow_transition_triggered_by FOREIGN KEY (triggered_by) REFERENCES users(id),
    KEY idx_workflow_transition_so (service_order_id, triggered_at)
) ENGINE=InnoDB;

CREATE TABLE consultant_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    consultant_user_id BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    internal_reviewer_id BIGINT UNSIGNED NULL,
    status ENUM('ASSIGNED','WORK_SUBMITTED','UNDER_INTERNAL_REVIEW','APPROVED','REJECTED') NOT NULL DEFAULT 'ASSIGNED',
    remarks TEXT NULL,
    UNIQUE KEY uk_consultant_assignment (service_order_id, consultant_user_id),
    CONSTRAINT fk_consultant_assignment_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_consultant_assignment_consultant FOREIGN KEY (consultant_user_id) REFERENCES users(id),
    CONSTRAINT fk_consultant_assignment_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id),
    CONSTRAINT fk_consultant_assignment_reviewer FOREIGN KEY (internal_reviewer_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE consultant_deliverables (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    consultant_assignment_id BIGINT UNSIGNED NOT NULL,
    document_id BIGINT UNSIGNED NOT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    review_status ENUM('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
    review_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_consultant_deliverable_assignment FOREIGN KEY (consultant_assignment_id) REFERENCES consultant_assignments(id),
    CONSTRAINT fk_consultant_deliverable_document FOREIGN KEY (document_id) REFERENCES documents(id),
    CONSTRAINT fk_consultant_deliverable_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE consultant_bills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    consultant_assignment_id BIGINT UNSIGNED NOT NULL,
    bill_no VARCHAR(80) NOT NULL,
    bill_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL,
    document_id BIGINT UNSIGNED NULL,
    review_status ENUM('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    review_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_consultant_bill_assignment_billno (consultant_assignment_id, bill_no),
    CONSTRAINT fk_consultant_bill_assignment FOREIGN KEY (consultant_assignment_id) REFERENCES consultant_assignments(id),
    CONSTRAINT fk_consultant_bill_document FOREIGN KEY (document_id) REFERENCES documents(id),
    CONSTRAINT fk_consultant_bill_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE consultant_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    consultant_bill_id BIGINT UNSIGNED NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_mode ENUM('CASH','BANK_TRANSFER','CHEQUE','UPI','OTHER') NOT NULL,
    reference_no VARCHAR(100) NULL,
    paid_by BIGINT UNSIGNED NOT NULL,
    proof_document_id BIGINT UNSIGNED NULL,
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_consultant_payment_bill FOREIGN KEY (consultant_bill_id) REFERENCES consultant_bills(id),
    CONSTRAINT fk_consultant_payment_paid_by FOREIGN KEY (paid_by) REFERENCES users(id),
    CONSTRAINT fk_consultant_payment_proof FOREIGN KEY (proof_document_id) REFERENCES documents(id),
    KEY idx_consultant_payments_bill (consultant_bill_id)
) ENGINE=InnoDB;

CREATE TABLE disbursements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    expense_date DATE NOT NULL,
    expense_type VARCHAR(80) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    is_recoverable TINYINT(1) NOT NULL DEFAULT 1,
    proof_document_id BIGINT UNSIGNED NULL,
    paid_to VARCHAR(190) NULL,
    notes TEXT NULL,
    added_by BIGINT UNSIGNED NOT NULL,
    invoiced_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_disbursement_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_disbursement_proof FOREIGN KEY (proof_document_id) REFERENCES documents(id),
    CONSTRAINT fk_disbursement_added_by FOREIGN KEY (added_by) REFERENCES users(id),
    KEY idx_disbursement_so (service_order_id)
) ENGINE=InnoDB;

CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    company_id BIGINT UNSIGNED NOT NULL,
    financial_year_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    service_order_id BIGINT UNSIGNED NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    invoice_type ENUM('ADVANCE','FINAL','DEBIT_NOTE') NOT NULL DEFAULT 'FINAL',
    service_fee DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    disbursement_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    gross_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    advance_adjusted DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    net_payable DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    payment_status ENUM('UNPAID','PARTIALLY_PAID','PAID') NOT NULL DEFAULT 'UNPAID',
    accounting_status ENUM('DRAFT','APPROVED','ISSUED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
    approved_by BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    issued_at DATETIME NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_invoices_financial_year FOREIGN KEY (financial_year_id) REFERENCES financial_years(id),
    CONSTRAINT fk_invoices_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_invoices_service_order FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_invoices_approved_by FOREIGN KEY (approved_by) REFERENCES users(id),
    CONSTRAINT fk_invoices_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_invoices_service_order (service_order_id),
    KEY idx_invoices_payment_status (payment_status)
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    line_type ENUM('SERVICE_FEE','DISBURSEMENT','TAX','ADJUSTMENT') NOT NULL,
    reference_type ENUM('SERVICE_ORDER','DISBURSEMENT','PAYMENT','OTHER') NOT NULL DEFAULT 'OTHER',
    reference_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    line_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    KEY idx_invoice_items_invoice (invoice_id)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    service_order_id BIGINT UNSIGNED NULL,
    invoice_id BIGINT UNSIGNED NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_mode ENUM('RAZORPAY','CASH','BANK_TRANSFER','CHEQUE','UPI','OTHER') NOT NULL,
    transaction_type ENUM('ADVANCE','INVOICE_PAYMENT','REFUND','ADJUSTMENT') NOT NULL DEFAULT 'INVOICE_PAYMENT',
    reference_no VARCHAR(120) NULL,
    gateway_order_id VARCHAR(120) NULL,
    gateway_payment_id VARCHAR(120) NULL,
    gateway_signature VARCHAR(255) NULL,
    status ENUM('INITIATED','SUCCESS','FAILED','REFUNDED','CANCELLED') NOT NULL DEFAULT 'SUCCESS',
    received_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_payments_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_payments_service_order FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    CONSTRAINT fk_payments_received_by FOREIGN KEY (received_by) REFERENCES users(id),
    KEY idx_payments_invoice (invoice_id),
    KEY idx_payments_service_order (service_order_id),
    KEY idx_payments_reference (reference_no)
) ENGINE=InnoDB;

CREATE TABLE payment_allocations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    allocated_amount DECIMAL(15,2) NOT NULL,
    allocated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    allocated_by BIGINT UNSIGNED NULL,
    CONSTRAINT fk_payment_alloc_payment FOREIGN KEY (payment_id) REFERENCES payments(id),
    CONSTRAINT fk_payment_alloc_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    CONSTRAINT fk_payment_alloc_allocated_by FOREIGN KEY (allocated_by) REFERENCES users(id),
    KEY idx_payment_alloc_payment (payment_id),
    KEY idx_payment_alloc_invoice (invoice_id)
) ENGINE=InnoDB;

CREATE TABLE receipts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(50) NOT NULL UNIQUE,
    company_id BIGINT UNSIGNED NOT NULL,
    financial_year_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NOT NULL,
    receipt_date DATE NOT NULL,
    receipt_amount DECIMAL(15,2) NOT NULL,
    generated_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_receipts_company FOREIGN KEY (company_id) REFERENCES companies(id),
    CONSTRAINT fk_receipts_financial_year FOREIGN KEY (financial_year_id) REFERENCES financial_years(id),
    CONSTRAINT fk_receipts_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_receipts_payment FOREIGN KEY (payment_id) REFERENCES payments(id),
    CONSTRAINT fk_receipts_generated_by FOREIGN KEY (generated_by) REFERENCES users(id),
    KEY idx_receipts_payment (payment_id)
) ENGINE=InnoDB;

CREATE TABLE payment_receipt_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NULL,
    allocated_amount DECIMAL(15,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_receipt_item_receipt FOREIGN KEY (receipt_id) REFERENCES receipts(id),
    CONSTRAINT fk_payment_receipt_item_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    KEY idx_payment_receipt_item_receipt (receipt_id)
) ENGINE=InnoDB;

CREATE TABLE attendance_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    login_at DATETIME NOT NULL,
    logout_at DATETIME NULL,
    total_active_seconds INT NOT NULL DEFAULT 0,
    total_idle_seconds INT NOT NULL DEFAULT 0,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_sessions_user FOREIGN KEY (user_id) REFERENCES users(id),
    KEY idx_attendance_sessions_user (user_id, login_at)
) ENGINE=InnoDB;

CREATE TABLE attendance_activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attendance_session_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    service_order_task_id BIGINT UNSIGNED NULL,
    activity_type ENUM('ACTIVE','IDLE','TASK_LINKED','TASK_UNLINKED','BREAK','RESUME') NOT NULL,
    started_at DATETIME NOT NULL,
    ended_at DATETIME NULL,
    duration_seconds INT NOT NULL DEFAULT 0,
    remarks VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_activity_session FOREIGN KEY (attendance_session_id) REFERENCES attendance_sessions(id),
    CONSTRAINT fk_attendance_activity_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_attendance_activity_task FOREIGN KEY (service_order_task_id) REFERENCES service_order_tasks(id),
    KEY idx_attendance_activity_user (user_id, started_at)
) ENGINE=InnoDB;

CREATE TABLE reminders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_order_id BIGINT UNSIGNED NOT NULL,
    reminder_type ENUM('E_VERIFICATION','SLA_ESCALATION','PAYMENT_FOLLOWUP','DOCUMENT_FOLLOWUP','GENERAL') NOT NULL,
    schedule_day_no INT NULL,
    due_at DATETIME NOT NULL,
    sent_at DATETIME NULL,
    status ENUM('PENDING','SENT','DONE','SKIPPED','OVERDUE') NOT NULL DEFAULT 'PENDING',
    assigned_to BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reminders_so FOREIGN KEY (service_order_id) REFERENCES service_orders(id),
    CONSTRAINT fk_reminders_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id),
    CONSTRAINT fk_reminders_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    KEY idx_reminders_due (due_at, status),
    KEY idx_reminders_so (service_order_id, reminder_type)
) ENGINE=InnoDB;

CREATE TABLE reminder_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reminder_id BIGINT UNSIGNED NOT NULL,
    action_type ENUM('CREATED','NOTIFIED','FOLLOW_UP_LOGGED','COMPLETED','OVERDUE_MARKED','SKIPPED') NOT NULL,
    action_by BIGINT UNSIGNED NULL,
    action_note TEXT NULL,
    action_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reminder_logs_reminder FOREIGN KEY (reminder_id) REFERENCES reminders(id),
    CONSTRAINT fk_reminder_logs_action_by FOREIGN KEY (action_by) REFERENCES users(id),
    KEY idx_reminder_logs_reminder (reminder_id, action_at)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    client_contact_id BIGINT UNSIGNED NULL,
    channel ENUM('EMAIL','SMS','WHATSAPP','IN_APP') NOT NULL DEFAULT 'IN_APP',
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    linked_module ENUM('PSO','SO','INVOICE','PAYMENT','REMINDER','GENERAL') NOT NULL DEFAULT 'GENERAL',
    linked_id BIGINT UNSIGNED NULL,
    sent_at DATETIME NULL,
    delivery_status ENUM('PENDING','SENT','FAILED','READ') NOT NULL DEFAULT 'PENDING',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_notifications_client_contact FOREIGN KEY (client_contact_id) REFERENCES client_contacts(id),
    KEY idx_notifications_user (user_id, delivery_status),
    KEY idx_notifications_contact (client_contact_id, delivery_status)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    module_code VARCHAR(60) NOT NULL,
    action_code VARCHAR(60) NOT NULL,
    entity_type VARCHAR(60) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id),
    KEY idx_activity_logs_entity (entity_type, entity_id),
    KEY idx_activity_logs_module (module_code, action_code)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id BIGINT UNSIGNED NOT NULL,
    action_type ENUM('INSERT','UPDATE','DELETE_BLOCKED','LOGIN','LOGOUT','STATUS_CHANGE') NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    action_note TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id),
    KEY idx_audit_logs_table_record (table_name, record_id),
    KEY idx_audit_logs_created_at (created_at)
) ENGINE=InnoDB;

INSERT INTO companies (code, legal_name, display_name, company_type, is_active) VALUES
('ETAX', 'E Tax Advisors Pvt Ltd', 'E Tax Advisors Pvt Ltd', 'PRIVATE_LIMITED', 1),
('ADV', 'K. Sivasankaran (Advocate)', 'K. Sivasankaran (Advocate)', 'ADVOCATE', 1);

INSERT INTO service_types (code, name, service_group, requires_payment_stage, requires_e_verification, default_sla_days) VALUES
('ITR', 'Income Tax Return', 'ITR', 1, 1, 2),
('GST', 'GST Compliance', 'GST', 1, 0, 2),
('TDS', 'TDS Compliance', 'TDS', 0, 0, 2);

INSERT INTO company_service_type_map (company_id, service_type_id, is_default_company)
SELECT c.id, s.id,
       CASE
           WHEN c.code = 'ADV' AND s.code = 'ITR' THEN 1
           WHEN c.code = 'ETAX' AND s.code IN ('GST', 'TDS') THEN 1
           ELSE 0
       END
FROM companies c
JOIN service_types s
WHERE (c.code = 'ADV' AND s.code = 'ITR')
   OR (c.code = 'ETAX' AND s.code IN ('GST', 'TDS'));

INSERT INTO roles (code, label, scope) VALUES
('SUPER_ADMIN', 'Super Admin', 'SYSTEM'),
('ADMIN', 'Admin / CEO', 'SYSTEM'),
('CRM', 'CRM', 'SYSTEM'),
('ASSISTANT_CRM', 'Assistant CRM', 'SYSTEM'),
('BACKEND_STAFF', 'Backend Staff', 'SYSTEM'),
('DEO', 'DEO', 'SYSTEM'),
('ACCOUNTS', 'Accounts', 'SYSTEM'),
('CONSULTANT', 'Consultant', 'SYSTEM'),
('CLIENT', 'Client', 'PORTAL');

INSERT INTO workflow_definitions (service_type_id, version_no, name, is_active)
SELECT id, 1, CONCAT(name, ' Default Workflow'), 1 FROM service_types;

INSERT INTO workflow_stage_definitions (workflow_definition_id, stage_code, stage_name, stage_group, sort_order, is_milestone_click_required, auto_trigger_on, is_terminal)
SELECT wd.id, stage_code, stage_name, stage_group, sort_order, click_required, auto_trigger_on, is_terminal
FROM workflow_definitions wd
JOIN service_types st ON st.id = wd.service_type_id
JOIN (
    SELECT 'DOCUMENT_PENDING' AS stage_code, 'Document Pending' AS stage_name, 'COMMON' AS stage_group, 1 AS sort_order, 1 AS click_required, NULL AS auto_trigger_on, 0 AS is_terminal, 'ALL' AS applies_to
    UNION ALL SELECT 'PREPARATION', 'Preparation', 'COMMON', 2, 1, NULL, 0, 'ALL'
    UNION ALL SELECT 'REVIEW', 'Review', 'COMMON', 3, 1, NULL, 0, 'ALL'
    UNION ALL SELECT 'PAYMENT_PENDING', 'Payment Pending', 'ITR', 4, 0, 'PAYMENT_ENTRY', 0, 'ITR'
    UNION ALL SELECT 'PAID', 'Paid', 'ITR', 5, 1, NULL, 0, 'ITR'
    UNION ALL SELECT 'PAYMENT_PENDING', 'Payment Pending', 'GST', 4, 0, 'PAYMENT_ENTRY', 0, 'GST'
    UNION ALL SELECT 'PAID', 'Paid', 'GST', 5, 1, NULL, 0, 'GST'
    UNION ALL SELECT 'FILING_PENDING', 'Filing Pending', 'COMMON', 6, 1, NULL, 0, 'ALL'
    UNION ALL SELECT 'FILING_DONE', 'Filing Done', 'COMMON', 7, 1, NULL, 0, 'ALL'
    UNION ALL SELECT 'ACKNOWLEDGEMENT_CAPTURED', 'Acknowledgement Captured', 'COMMON', 8, 0, 'ACK_UPLOAD', 0, 'ALL'
    UNION ALL SELECT 'E_VERIFICATION_PENDING', 'E-Verification Pending', 'ITR', 9, 1, NULL, 0, 'ITR'
    UNION ALL SELECT 'E_VERIFICATION_DONE', 'E-Verification Done', 'ITR', 10, 1, NULL, 0, 'ITR'
    UNION ALL SELECT 'PROCEDURALLY_CLOSED', 'Procedurally Closed', 'CLOSURE', 11, 1, NULL, 1, 'ALL'
) stage_map
ON stage_map.applies_to = 'ALL' OR stage_map.applies_to = st.code;

DELIMITER $$

CREATE TRIGGER trg_service_orders_no_delete
BEFORE DELETE ON service_orders
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of service orders is not allowed.';
END$$

CREATE TRIGGER trg_pre_service_orders_no_delete
BEFORE DELETE ON pre_service_orders
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of pre-service orders is not allowed.';
END$$

CREATE TRIGGER trg_invoices_no_delete
BEFORE DELETE ON invoices
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of invoices is not allowed.';
END$$

CREATE TRIGGER trg_payments_no_delete
BEFORE DELETE ON payments
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of payments is not allowed.';
END$$

CREATE TRIGGER trg_documents_no_delete
BEFORE DELETE ON documents
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of documents is not allowed.';
END$$

CREATE TRIGGER trg_clients_no_delete
BEFORE DELETE ON clients
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Deletion of clients is not allowed.';
END$$

CREATE TRIGGER trg_service_orders_so_no_immutable
BEFORE UPDATE ON service_orders
FOR EACH ROW
BEGIN
    IF NEW.so_no <> OLD.so_no THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Service Order number is immutable.';
    END IF;
END$$

CREATE TRIGGER trg_receipts_receipt_no_immutable
BEFORE UPDATE ON receipts
FOR EACH ROW
BEGIN
    IF NEW.receipt_no <> OLD.receipt_no THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Receipt number is immutable.';
    END IF;
END$$

CREATE TRIGGER trg_pre_service_orders_reject_admin_only
BEFORE UPDATE ON pre_service_orders
FOR EACH ROW
BEGIN
    IF NEW.current_status = 'REJECTED'
       AND OLD.current_status <> 'REJECTED'
       AND NEW.admin_rejected_by IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only Admin can reject PSO.';
    END IF;
END$$

CREATE TRIGGER trg_service_orders_lock_after_final_close
BEFORE UPDATE ON service_orders
FOR EACH ROW
BEGIN
    IF OLD.final_closed_at IS NOT NULL AND NEW.is_locked = 0 AND NEW.admin_override_unlocked_by IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Final closed service order can be unlocked only via Admin override.';
    END IF;
END$$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
