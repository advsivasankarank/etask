-- Step 34: selectable financial years for service-order periods and numbering.
-- Idempotent: existing years are refreshed and missing years are inserted.

INSERT INTO financial_years (code, label, start_date, end_date, is_active, created_at)
VALUES
    ('2024-25', 'FY 2024-25', '2024-04-01', '2025-03-31', 1, NOW()),
    ('2025-26', 'FY 2025-26', '2025-04-01', '2026-03-31', 1, NOW()),
    ('2026-27', 'FY 2026-27', '2026-04-01', '2027-03-31', 1, NOW()),
    ('2027-28', 'FY 2027-28', '2027-04-01', '2028-03-31', 1, NOW()),
    ('2028-29', 'FY 2028-29', '2028-04-01', '2029-03-31', 1, NOW()),
    ('2029-30', 'FY 2029-30', '2029-04-01', '2030-03-31', 1, NOW()),
    ('2030-31', 'FY 2030-31', '2030-04-01', '2031-03-31', 1, NOW()),
    ('2031-32', 'FY 2031-32', '2031-04-01', '2032-03-31', 1, NOW()),
    ('2032-33', 'FY 2032-33', '2032-04-01', '2033-03-31', 1, NOW()),
    ('2033-34', 'FY 2033-34', '2033-04-01', '2034-03-31', 1, NOW()),
    ('2034-35', 'FY 2034-35', '2034-04-01', '2035-03-31', 1, NOW()),
    ('2035-36', 'FY 2035-36', '2035-04-01', '2036-03-31', 1, NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    start_date = VALUES(start_date),
    end_date = VALUES(end_date),
    is_active = 1;
