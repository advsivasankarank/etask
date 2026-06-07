USE etaxadv_etask;

ALTER TABLE service_orders
    ADD COLUMN work_basis ENUM('ANNUAL','MONTHLY','QUARTERLY') NULL AFTER workflow_definition_id,
    ADD COLUMN compliance_subtype VARCHAR(40) NULL AFTER work_basis,
    ADD COLUMN assessment_year VARCHAR(9) NULL AFTER compliance_subtype,
    ADD COLUMN period_month TINYINT UNSIGNED NULL AFTER assessment_year,
    ADD COLUMN period_quarter ENUM('Q1','Q2','Q3','Q4') NULL AFTER period_month,
    ADD COLUMN period_year SMALLINT UNSIGNED NULL AFTER period_quarter,
    ADD COLUMN period_label VARCHAR(60) NULL AFTER period_year;
