<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('ENTRYPULSE_DB_HOST') ?: '127.0.0.1';
    $database = getenv('ENTRYPULSE_DB_NAME') ?: 'entrypulse_db';
    $username = getenv('ENTRYPULSE_DB_USER') ?: 'root';
    $password = getenv('ENTRYPULSE_DB_PASSWORD') ?: '';

    $server = new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    ensureApiTables($pdo);
    return $pdo;
}

function ensureApiTables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id VARCHAR(50) NOT NULL PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'CUSTOMER',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ep_seat_inventory (
        seat_id VARCHAR(100) NOT NULL PRIMARY KEY,
        event_id VARCHAR(50) NOT NULL,
        event_artist VARCHAR(100) NOT NULL,
        event_title VARCHAR(150) NOT NULL,
        venue_name VARCHAR(150) NOT NULL,
        section_id VARCHAR(30) NOT NULL,
        section_name VARCHAR(80) NOT NULL,
        seat_label VARCHAR(150) NOT NULL,
        base_price DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'AVAILABLE',
        locked_by_user_id VARCHAR(50) NULL,
        lock_expires_at DATETIME NULL,
        ticket_serial VARCHAR(100) NULL UNIQUE,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY event_status (event_id, status),
        KEY lock_expiry (lock_expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ep_orders (
        order_id VARCHAR(50) NOT NULL PRIMARY KEY,
        user_id VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        event_id VARCHAR(50) NOT NULL,
        event_artist VARCHAR(100) NOT NULL,
        event_title VARCHAR(150) NOT NULL,
        venue_name VARCHAR(150) NOT NULL,
        seat_id VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        seat_label VARCHAR(150) NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(30) NOT NULL,
        ticket_serial VARCHAR(100) NOT NULL UNIQUE,
        order_status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
        purchased_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY user_orders (user_id, purchased_at),
        CONSTRAINT ep_orders_user_fk FOREIGN KEY (user_id) REFERENCES users(user_id),
        CONSTRAINT ep_orders_seat_fk FOREIGN KEY (seat_id) REFERENCES ep_seat_inventory(seat_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requestData(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

function requireUser(PDO $pdo): array
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        jsonResponse(['error' => 'Please log in to continue.'], 401);
    }

    $stmt = $pdo->prepare('SELECT user_id, username, email, role FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        session_destroy();
        jsonResponse(['error' => 'Your session has expired. Please log in again.'], 401);
    }
    return $user;
}

function newId(string $prefix): string
{
    return $prefix . '_' . bin2hex(random_bytes(12));
}