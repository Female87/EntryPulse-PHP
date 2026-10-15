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
    if (!preg_match('/\A[A-Za-z0-9_]+\z/', $database)) {
        throw new RuntimeException('The configured database name contains unsupported characters.');
    }

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
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CHECK (role IN ('CUSTOMER', 'ADMIN'))
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
        locked_by_user_id VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
        lock_expires_at DATETIME NULL,
        ticket_serial VARCHAR(100) NULL UNIQUE,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY event_status (event_id, status),
        KEY lock_expiry (lock_expires_at),
        KEY held_by_user (locked_by_user_id),
        CONSTRAINT ep_seat_lock_user_fk FOREIGN KEY (locked_by_user_id) REFERENCES users(user_id),
        CHECK (base_price >= 0),
        CHECK (status IN ('AVAILABLE', 'HELD', 'SOLD')),
        CHECK ((status = 'HELD' AND locked_by_user_id IS NOT NULL AND lock_expires_at IS NOT NULL) OR (status <> 'HELD' AND locked_by_user_id IS NULL AND lock_expires_at IS NULL))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ep_event_catalog (
        event_id VARCHAR(50) NOT NULL PRIMARY KEY,
        event_artist VARCHAR(100) NOT NULL,
        event_title VARCHAR(150) NOT NULL,
        venue_name VARCHAR(150) NOT NULL,
        category VARCHAR(50) NOT NULL,
        vip_price DECIMAL(10,2) NOT NULL,
        lower_price DECIMAL(10,2) NOT NULL,
        upper_price DECIMAL(10,2) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        CHECK (vip_price >= 0 AND lower_price >= 0 AND upper_price >= 0),
        CHECK (is_active IN (0, 1))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $events = [
        ['TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'International', 50000, 22000, 6500],
        ['BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'International', 25000, 16000, 7500],
        ['BM-MNL', 'Bruno Mars', 'Live in Manila', 'Philippine Arena (Bulacan)', 'International', 23850, 15000, 5000],
        ['TW-AH', 'The Weeknd', 'After Hours Tour (Custom)', 'Philippine Arena (Bulacan)', 'International', 50000, 18000, 7000],
        ['WL-WD', 'Westlife', 'The Wild Dreams Tour', 'SM Mall of Asia Arena', 'International', 18000, 13500, 4500],
        ['BSB-DNA', 'Backstreet Boys', 'DNA World Tour', 'SM Mall of Asia Arena', 'International', 18350, 14000, 5000],
        ['F4-MG', 'F4 (Meteor Garden)', 'Reunion Concert (Custom)', 'SM Mall of Asia Arena', 'International', 50000, 20000, 6000],
        ['EN-FATE', 'ENHYPEN', 'FATE World Tour', 'New Clark City Stadium / MOA', 'International', 17000, 12500, 6500],
        ['BP-BP', 'BLACKPINK', 'BORN PINK World Tour', 'Philippine Arena (Bulacan)', 'International', 19400, 14500, 6000],
        ['BINI-GB', 'BINI', 'Grand BINIverse', 'Smart Araneta Coliseum', 'P-Pop & OPM', 11195, 7000, 2000],
        ['SB19-PAG', 'SB19', 'Pagtatag / Simula at Wakas', 'Smart Araneta Coliseum', 'P-Pop & OPM', 15000, 8000, 2500],
        ['KAIA-CS', 'KAIA', 'P-Pop Concert Series', 'Music Museum (Greenhills)', 'P-Pop & OPM', 5500, 3500, 1500],
        ['BD-PPOP', 'Babydolls', 'P-Pop Showcase', 'Skydome, SM North EDSA', 'P-Pop & OPM', 4500, 3000, 1200],
        ['RD-SOLO', 'Rob Deniel', 'Solo Live Concert', 'New Frontier Theater', 'P-Pop & OPM', 6000, 4000, 2000],
        ['JMC-XMAS', 'Jose Mari Chan', 'Christmas Special Live', 'Newport Performing Arts Theater', 'P-Pop & OPM', 8500, 5500, 2200],
        ['YC-BB', 'Yeng Constantino', 'Biyaheng Bente', 'Smart Araneta Coliseum', 'P-Pop & OPM', 8600, 5000, 2900],
    ];
    $catalogInsert = $pdo->prepare('INSERT IGNORE INTO ep_event_catalog (event_id, event_artist, event_title, venue_name, category, vip_price, lower_price, upper_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($events as $event) {
        $catalogInsert->execute($event);
    }

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
        KEY orders_purchased_at (purchased_at),
        CONSTRAINT ep_orders_user_fk FOREIGN KEY (user_id) REFERENCES users(user_id),
        CONSTRAINT ep_orders_seat_fk FOREIGN KEY (seat_id) REFERENCES ep_seat_inventory(seat_id),
        CONSTRAINT ep_orders_event_fk FOREIGN KEY (event_id) REFERENCES ep_event_catalog(event_id),
        CHECK (total_amount >= 0),
        CHECK (payment_method IN ('GCASH', 'MAYA', 'CARD')),
        CHECK (order_status IN ('ACTIVE', 'USED', 'REFUNDED'))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ep_refunds (
        refund_id VARCHAR(50) NOT NULL PRIMARY KEY,
        order_id VARCHAR(50) NOT NULL UNIQUE,
        user_id VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        reason VARCHAR(200) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'PROCESSED',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY user_refunds (user_id, created_at),
        KEY refund_created_at (created_at),
        CONSTRAINT ep_refunds_order_fk FOREIGN KEY (order_id) REFERENCES ep_orders(order_id),
        CONSTRAINT ep_refunds_user_fk FOREIGN KEY (user_id) REFERENCES users(user_id),
        CHECK (amount >= 0),
        CHECK (status IN ('PROCESSED'))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ep_gate_scans (
        scan_id VARCHAR(50) NOT NULL PRIMARY KEY,
        ticket_serial VARCHAR(100) NOT NULL UNIQUE,
        user_id VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        gate_id VARCHAR(30) NOT NULL,
        scanned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY user_scans (user_id, scanned_at),
        CONSTRAINT ep_gate_scans_ticket_fk FOREIGN KEY (ticket_serial) REFERENCES ep_orders(ticket_serial),
        CONSTRAINT ep_gate_scans_user_fk FOREIGN KEY (user_id) REFERENCES users(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ep_admin_audit (
        audit_id VARCHAR(50) NOT NULL PRIMARY KEY,
        actor_user_id VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
        action VARCHAR(50) NOT NULL,
        details VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY actor_audit_log (actor_user_id, created_at),
        CONSTRAINT ep_admin_audit_user_fk FOREIGN KEY (actor_user_id) REFERENCES users(user_id)
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
    if (is_string($raw) && trim($raw) !== '') {
        $data = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }
    }

    if (!empty($_POST) && is_array($_POST)) {
        return $_POST;
    }

    return [];
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