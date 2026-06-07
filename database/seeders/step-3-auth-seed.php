<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 2);

$envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
$env = is_file($envFile) ? parse_ini_file($envFile, false, INI_SCANNER_TYPED) : [];

$host = (string) ($env['DB_HOST'] ?? '127.0.0.1');
$port = (string) ($env['DB_PORT'] ?? '3306');
$database = (string) ($env['DB_DATABASE'] ?? 'etaxadv_etask');
$username = (string) ($env['DB_USERNAME'] ?? 'etaxadv_etaskdb');
$password = (string) ($env['DB_PASSWORD'] ?? '');

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$passwordHash = password_hash('ChangeMe@123', PASSWORD_DEFAULT);

$pdo->beginTransaction();

$check = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
$check->execute(['username' => 'superadmin']);
$user = $check->fetch();

if ($user === false) {
    $insertUser = $pdo->prepare(
        "INSERT INTO users
        (employee_code, username, password_hash, full_name, email, mobile, auth_type, must_change_password, is_active, created_at, updated_at)
        VALUES
        (:employee_code, :username, :password_hash, :full_name, :email, :mobile, 'LOCAL', 1, 1, NOW(), NOW())"
    );
    $insertUser->execute([
        'employee_code' => 'EMP-SUPER-001',
        'username' => 'superadmin',
        'password_hash' => $passwordHash,
        'full_name' => 'System Super Admin',
        'email' => 'superadmin@localhost.test',
        'mobile' => '9999999999',
    ]);

    $userId = (int) $pdo->lastInsertId();
} else {
    $userId = (int) $user['id'];

    $updatePassword = $pdo->prepare(
        'UPDATE users
         SET password_hash = :password_hash, must_change_password = 1, is_active = 1, updated_at = NOW()
         WHERE id = :id'
    );
    $updatePassword->execute([
        'password_hash' => $passwordHash,
        'id' => $userId,
    ]);
}

$role = $pdo->query("SELECT id FROM roles WHERE code = 'SUPER_ADMIN' LIMIT 1")->fetch();

if ($role !== false) {
    $assign = $pdo->prepare(
        'INSERT INTO user_role_map (user_id, role_id, assigned_by, assigned_at)
         SELECT :user_id, :role_id, NULL, NOW()
         WHERE NOT EXISTS (
             SELECT 1 FROM user_role_map WHERE user_id = :user_id_check AND role_id = :role_id_check
         )'
    );
    $assign->execute([
        'user_id' => $userId,
        'role_id' => (int) $role['id'],
        'user_id_check' => $userId,
        'role_id_check' => (int) $role['id'],
    ]);
}

$pdo->commit();

echo "Super Admin seeded successfully." . PHP_EOL;
echo "Username: superadmin" . PHP_EOL;
echo "Password: ChangeMe@123" . PHP_EOL;
