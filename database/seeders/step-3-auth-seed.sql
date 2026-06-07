-- Step 3 Authentication Seeder
-- Run this after importing step-1-database-schema.sql

USE etaxadv_etask;

SET @password_hash = '$2y$12$3vSIHhQWpYb0TnH94mNQ4.4X8vW0fvkYlCYwH14r2raekkUQun7Hm';

INSERT INTO users (
    employee_code,
    username,
    password_hash,
    full_name,
    email,
    mobile,
    auth_type,
    must_change_password,
    is_active,
    created_at,
    updated_at
)
SELECT
    'EMP-SUPER-001',
    'superadmin',
    @password_hash,
    'System Super Admin',
    'superadmin@localhost.test',
    '9999999999',
    'LOCAL',
    1,
    1,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE username = 'superadmin'
);

INSERT INTO user_role_map (user_id, role_id, assigned_by, assigned_at)
SELECT u.id, r.id, NULL, NOW()
FROM users u
INNER JOIN roles r ON r.code = 'SUPER_ADMIN'
WHERE u.username = 'superadmin'
  AND NOT EXISTS (
      SELECT 1
      FROM user_role_map urm
      WHERE urm.user_id = u.id
        AND urm.role_id = r.id
  );

-- Default password: ChangeMe@123
