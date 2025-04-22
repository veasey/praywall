<?php
// src/container.php
use DI\ContainerBuilder;
use Slim\Views\Twig;
use Twig\Loader\FilesystemLoader;

return (function() {
    $builder = new ContainerBuilder();
    $builder->addDefinitions([
        // Twig service
        Twig::class => \DI\factory(function() {
            return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
        }),

        // PDO service
        PDO::class => \DI\factory(function() {
            $host = $_ENV['DB_HOST']     ?? 'db';
            $port = $_ENV['DB_PORT']     ?? '3306';
            $db   = $_ENV['DB_DATABASE'] ?? 'praywall';
            $user = $_ENV['DB_USERNAME'] ?? 'root';
            $pass = $_ENV['DB_PASSWORD'] ?? 'root';

            $dsn  = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
            $pdo  = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return $pdo;
        }),
    ]);
    return $builder->build();
})();
