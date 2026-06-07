<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 2);
$envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
$env = is_file($envFile) ? parse_ini_file($envFile, false, INI_SCANNER_TYPED) : [];

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        (string) ($env['DB_HOST'] ?? '127.0.0.1'),
        (string) ($env['DB_PORT'] ?? '3306'),
        (string) ($env['DB_DATABASE'] ?? 'etaxadv_etask')
    ),
    (string) ($env['DB_USERNAME'] ?? 'etaxadv_etaskdb'),
    (string) ($env['DB_PASSWORD'] ?? ''),
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$pdo->beginTransaction();

$clientCode = 'CLT-DEMO-001';
$clientEmail = 'client.demo@localhost.test';
$clientUsername = 'clientdemo';
$passwordHash = password_hash('Client@123', PASSWORD_DEFAULT);

$checkClient = $pdo->prepare('SELECT id FROM clients WHERE client_code = :client_code LIMIT 1');
$checkClient->execute(['client_code' => $clientCode]);
$client = $checkClient->fetch();

if ($client === false) {
    $insertClient = $pdo->prepare(
        "INSERT INTO clients
        (client_code, client_type, legal_name, pan, email, mobile, default_company_id, onboarded_at, is_active, created_at, updated_at)
        VALUES
        (:client_code, 'INDIVIDUAL', 'Demo Client', 'ABCDE1234F', :email, '9876543210',
         (SELECT id FROM companies WHERE code = 'ADV' LIMIT 1), NOW(), 1, NOW(), NOW())"
    );
    $insertClient->execute([
        'client_code' => $clientCode,
        'email' => $clientEmail,
    ]);
    $clientId = (int) $pdo->lastInsertId();
} else {
    $clientId = (int) $client['id'];
}

$checkContact = $pdo->prepare('SELECT id FROM client_contacts WHERE client_id = :client_id AND email = :email LIMIT 1');
$checkContact->execute([
    'client_id' => $clientId,
    'email' => $clientEmail,
]);
$contact = $checkContact->fetch();

if ($contact === false) {
    $insertContact = $pdo->prepare(
        "INSERT INTO client_contacts
        (client_id, contact_name, designation, email, mobile, is_primary, can_login, created_at, updated_at)
        VALUES
        (:client_id, 'Demo Client User', 'Authorized Signatory', :email, '9876543210', 1, 1, NOW(), NOW())"
    );
    $insertContact->execute([
        'client_id' => $clientId,
        'email' => $clientEmail,
    ]);
    $contactId = (int) $pdo->lastInsertId();
} else {
    $contactId = (int) $contact['id'];
}

$checkUser = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
$checkUser->execute(['username' => $clientUsername]);
$user = $checkUser->fetch();

if ($user === false) {
    $insertUser = $pdo->prepare(
        "INSERT INTO users
        (client_contact_id, username, password_hash, full_name, email, mobile, auth_type, must_change_password, is_active, created_at, updated_at)
        VALUES
        (:client_contact_id, :username, :password_hash, 'Demo Client User', :email, '9876543210', 'LOCAL', 1, 1, NOW(), NOW())"
    );
    $insertUser->execute([
        'client_contact_id' => $contactId,
        'username' => $clientUsername,
        'password_hash' => $passwordHash,
        'email' => $clientEmail,
    ]);
    $userId = (int) $pdo->lastInsertId();
} else {
    $userId = (int) $user['id'];

    $updateUser = $pdo->prepare(
        'UPDATE users SET client_contact_id = :client_contact_id, password_hash = :password_hash, updated_at = NOW() WHERE id = :id'
    );
    $updateUser->execute([
        'client_contact_id' => $contactId,
        'password_hash' => $passwordHash,
        'id' => $userId,
    ]);
}

$role = $pdo->query("SELECT id FROM roles WHERE code = 'CLIENT' LIMIT 1")->fetch();
if ($role !== false) {
    $assignRole = $pdo->prepare(
        'INSERT INTO user_role_map (user_id, role_id, assigned_by, assigned_at)
         SELECT :user_id, :role_id, NULL, NOW()
         WHERE NOT EXISTS (
             SELECT 1 FROM user_role_map WHERE user_id = :user_id_check AND role_id = :role_id_check
         )'
    );
    $assignRole->execute([
        'user_id' => $userId,
        'role_id' => (int) $role['id'],
        'user_id_check' => $userId,
        'role_id_check' => (int) $role['id'],
    ]);
}

$pdo->commit();

echo "Demo client portal user seeded successfully." . PHP_EOL;
echo "Username: clientdemo" . PHP_EOL;
echo "Password: Client@123" . PHP_EOL;
