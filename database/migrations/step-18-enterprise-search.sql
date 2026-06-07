USE etaxadv_etask;

CREATE TABLE IF NOT EXISTS search_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    search_mode ENUM('QUICK', 'GLOBAL', 'ADVANCED') NOT NULL,
    query_text VARCHAR(255) NOT NULL DEFAULT '',
    source_scope VARCHAR(120) NOT NULL,
    filters_json JSON NULL,
    result_count INT UNSIGNED NOT NULL DEFAULT 0,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_search_history_user FOREIGN KEY (user_id) REFERENCES users(id),
    KEY idx_search_history_user_created (user_id, created_at),
    KEY idx_search_history_mode_source (search_mode, source_scope),
    KEY idx_search_history_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('search.view', 'SEARCH', 'VIEW', 'View Enterprise Search', 'Access the enterprise search workspace and global results', 1, NOW(), NOW()),
('search.quick', 'SEARCH', 'QUICK', 'Use Quick Search', 'Use header quick search across permitted sources', 1, NOW(), NOW()),
('search.advanced', 'SEARCH', 'ADVANCED', 'Use Advanced Search', 'Run advanced filtered searches across permitted sources', 1, NOW(), NOW()),
('search.history', 'SEARCH', 'HISTORY', 'View Search History', 'View search history for the current user', 1, NOW(), NOW()),
('search.audit', 'SEARCH', 'AUDIT', 'Audit Search Activity', 'View search history across users for audit purposes', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('search.view', 'search.quick', 'search.advanced', 'search.history', 'search.audit')
WHERE r.code IN ('SUPER_ADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('search.view', 'search.quick', 'search.advanced', 'search.history')
WHERE r.code IN ('CRM', 'ASSISTANT_CRM', 'BACKEND_STAFF', 'ACCOUNTS')
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN ('search.view', 'search.quick', 'search.history')
WHERE r.code IN ('CONSULTANT', 'CLIENT')
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;
