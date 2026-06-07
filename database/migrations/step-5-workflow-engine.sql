USE etaxadv_etask;

ALTER TABLE service_orders
    ADD COLUMN payment_reference_no VARCHAR(100) NULL AFTER current_stage_code,
    ADD COLUMN payment_recorded_at DATETIME NULL AFTER payment_reference_no,
    ADD COLUMN filing_reference_no VARCHAR(100) NULL AFTER payment_recorded_at,
    ADD COLUMN acknowledgement_no VARCHAR(100) NULL AFTER filing_reference_no,
    ADD COLUMN acknowledgement_captured_at DATETIME NULL AFTER acknowledgement_no,
    ADD COLUMN e_verification_completed_at DATETIME NULL AFTER acknowledgement_captured_at,
    ADD COLUMN last_stage_changed_at DATETIME NULL AFTER e_verification_completed_at;
