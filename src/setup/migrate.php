<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$env = $_ENV['APP_ENV'] ?? 'production';

if ($env !== 'local') {
    die("🚨 Migrations are disabled in $env environment.\n");
}

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

try {

    // Load and run schema.sql
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schemaSql);
    echo "Schema applied.\n";

    // Load and run seed.sql
    $seedSql = file_get_contents(__DIR__ . '/seed.sql');
    $pdo->exec($seedSql);
    echo "Test data seeded.\n";

} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
