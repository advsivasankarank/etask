USE etaxadv_etask;

UPDATE reminder_escalation_rules
SET is_active = 0,
    updated_at = NOW()
WHERE reminder_type = 'PENDING_PSO';

INSERT INTO reminder_escalation_rules (
    reminder_type,
    day_offset,
    target_type,
    target_role_code,
    channel,
    is_active,
    created_at,
    updated_at
) VALUES
('PENDING_PSO', 1, 'ROLE', 'ADMIN', 'IN_APP', 1, NOW(), NOW()),
('PENDING_PSO', 1, 'ROLE', 'SUPER_ADMIN', 'IN_APP', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    target_role_code = VALUES(target_role_code),
    channel = VALUES(channel),
    is_active = VALUES(is_active),
    updated_at = NOW();
