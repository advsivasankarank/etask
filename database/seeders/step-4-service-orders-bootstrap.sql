-- Step 4 Service Order Bootstrap
-- Ensures financial years exist for SO numbering on localhost.

USE etaxadv_etask;

INSERT INTO financial_years (code, label, start_date, end_date, is_active, created_at)
SELECT '2025-26', 'FY 2025-26', '2025-04-01', '2026-03-31', 1, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM financial_years WHERE code = '2025-26'
);

INSERT INTO financial_years (code, label, start_date, end_date, is_active, created_at)
SELECT '2026-27', 'FY 2026-27', '2026-04-01', '2027-03-31', 1, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM financial_years WHERE code = '2026-27'
);
