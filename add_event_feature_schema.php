<?php
declare(strict_types=1);

/**
 * ENTRYPULSE SQL SCHEMA MIGRATION SCRIPT
 * Feature: Admin Ticket Event Creation & Auto 60-Seat Inventory Generation
 * Target Database: entrypulse_db (MariaDB / MySQL compatible for phpMyAdmin)
 */

$migrationSql = <<<'SQL'
-- =========================================================================
-- ENTRYPULSE DATABASE SCHEMA MIGRATION: ADD EVENT & INVENTORY FEATURE
-- Compatible with MariaDB / MySQL (phpMyAdmin)
-- =========================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Ensure ep_event_catalog table exists with correct constraints
CREATE TABLE IF NOT EXISTS `ep_event_catalog` (
    `event_id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `event_artist` VARCHAR(100) NOT NULL,
    `event_title` VARCHAR(150) NOT NULL,
    `venue_name` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `vip_price` DECIMAL(10,2) NOT NULL,
    `lower_price` DECIMAL(10,2) NOT NULL,
    `upper_price` DECIMAL(10,2) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    CHECK (`vip_price` >= 0 AND `lower_price` >= 0 AND `upper_price` >= 0),
    CHECK (`is_active` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Ensure ep_seat_inventory table exists with correct relational constraints
CREATE TABLE IF NOT EXISTS `ep_seat_inventory` (
    `seat_id` VARCHAR(100) NOT NULL PRIMARY KEY,
    `event_id` VARCHAR(50) NOT NULL,
    `event_artist` VARCHAR(100) NOT NULL,
    `event_title` VARCHAR(150) NOT NULL,
    `venue_name` VARCHAR(150) NOT NULL,
    `section_id` VARCHAR(30) NOT NULL,
    `section_name` VARCHAR(80) NOT NULL,
    `seat_label` VARCHAR(150) NOT NULL,
    `base_price` DECIMAL(10,2) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'AVAILABLE',
    `locked_by_user_id` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    `lock_expires_at` DATETIME NULL,
    `ticket_serial` VARCHAR(100) NULL UNIQUE,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `event_status` (`event_id`, `status`),
    KEY `lock_expiry` (`lock_expires_at`),
    KEY `held_by_user` (`locked_by_user_id`),
    CONSTRAINT `ep_seat_lock_user_fk` FOREIGN KEY (`locked_by_user_id`) REFERENCES `users`(`user_id`),
    CHECK (`base_price` >= 0),
    CHECK (`status` IN ('AVAILABLE', 'HELD', 'SOLD')),
    CHECK ((`status` = 'HELD' AND `locked_by_user_id` IS NOT NULL AND `lock_expires_at` IS NOT NULL) OR (`status` <> 'HELD' AND `locked_by_user_id` IS NULL AND `lock_expires_at` IS NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Ensure ep_admin_audit table exists for logging administrative actions
CREATE TABLE IF NOT EXISTS `ep_admin_audit` (
    `audit_id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `actor_user_id` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `details` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `actor_audit_log` (`actor_user_id`, `created_at`),
    CONSTRAINT `ep_admin_audit_user_fk` FOREIGN KEY (`actor_user_id`) REFERENCES `users`(`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- STORED PROCEDURE: sp_create_event_with_inventory
-- Atomically inserts an event into ep_event_catalog and generates exactly
-- 60 seats in ep_seat_inventory across 6 sections (VIP-A, VIP-B, LB-1, LB-2, UB, GA),
-- 2 rows per section (R1-R2), and 6 seats per row (S1-S6) with precise pricing rules.
-- =========================================================================

DELIMITER //

DROP PROCEDURE IF EXISTS `sp_create_event_with_inventory`//

CREATE PROCEDURE `sp_create_event_with_inventory`(
    IN p_event_id VARCHAR(50),
    IN p_artist VARCHAR(100),
    IN p_title VARCHAR(150),
    IN p_venue VARCHAR(150),
    IN p_category VARCHAR(50),
    IN p_vip_price DECIMAL(10,2),
    IN p_lower_price DECIMAL(10,2),
    IN p_upper_price DECIMAL(10,2),
    IN p_admin_user_id VARCHAR(50)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- 1. Insert into event catalog
    INSERT INTO `ep_event_catalog` (
        `event_id`, `event_artist`, `event_title`, `venue_name`, `category`,
        `vip_price`, `lower_price`, `upper_price`, `is_active`
    ) VALUES (
        p_event_id, p_artist, p_title, p_venue, p_category,
        p_vip_price, p_lower_price, p_upper_price, 1
    );

    -- 2. Generate 60 seats across 6 sections (2 rows, 6 seats per row)
    -- Section 1: VIP-A (Floor Front) -> Price: p_vip_price
    CALL `sp_generate_section_seats`(p_event_id, p_artist, p_title, p_venue, 'VIP-A', 'VIP A (Floor Front)', p_vip_price);

    -- Section 2: VIP-B (Floor Soundboard) -> Price: p_vip_price
    CALL `sp_generate_section_seats`(p_event_id, p_artist, p_title, p_venue, 'VIP-B', 'VIP B (Floor Soundboard)', p_vip_price);

    -- Section 3: LB-1 (Lower Box 1) -> Price: p_lower_price
    CALL `sp_generate_section_seats`(p_event_id, p_artist, p_title, p_venue, 'LB-1', 'Lower Box 1', p_lower_price);

    -- Section 4: LB-2 (Lower Box 2) -> Price: 85% of p_lower_price
    CALL `sp_generate_section_seats`(p_event_id, p_artist, p_title, p_venue, 'LB-2', 'Lower Box 2', ROUND(p_lower_price * 0.85, 2));

    -- Section 5: UB (Upper Box) -> Price: 150% of p_upper_price
    CALL `sp_generate_section_seats`(p_event_id, p_artist, p_title, p_venue, 'UB', 'Upper Box', ROUND(p_upper_price * 1.50, 2));

    -- Section 6: GA (Gen Ad) -> Price: p_upper_price
    CALL `sp_generate_section_seats`(p_event_id, p_artist, p_title, p_venue, 'GA', 'Gen Ad', p_upper_price);

    -- 3. Log admin audit action
    INSERT INTO `ep_admin_audit` (`audit_id`, `actor_user_id`, `action`, `details`)
    VALUES (
        CONCAT('aud_', SUBSTRING(MD5(RAND()), 1, 16)),
        p_admin_user_id,
        'CREATE_EVENT_WITH_INVENTORY',
        CONCAT('Created event ', p_event_id, ' (', p_artist, ' - ', p_title, ') with 60 seats')
    );

    COMMIT;
END//

-- Helper procedure to generate 12 seats (2 rows x 6 seats) for a given section
DROP PROCEDURE IF EXISTS `sp_generate_section_seats`//

CREATE PROCEDURE `sp_generate_section_seats`(
    IN p_event_id VARCHAR(50),
    IN p_artist VARCHAR(100),
    IN p_title VARCHAR(150),
    IN p_venue VARCHAR(150),
    IN p_section_id VARCHAR(30),
    IN p_section_name VARCHAR(80),
    IN p_price DECIMAL(10,2)
)
BEGIN
    DECLARE r INT DEFAULT 1;
    DECLARE s INT DEFAULT 1;
    DECLARE v_seat_id VARCHAR(100);
    DECLARE v_seat_label VARCHAR(150);

    WHILE r <= 2 DO
        SET s = 1;
        WHILE s <= 6 DO
            SET v_seat_id = CONCAT(p_event_id, '-', p_section_id, '-R', r, '-S', s);
            SET v_seat_label = CONCAT(p_section_name, ' - Row ', r, ', Seat ', s);

            INSERT IGNORE INTO `ep_seat_inventory` (
                `seat_id`, `event_id`, `event_artist`, `event_title`, `venue_name`,
                `section_id`, `section_name`, `seat_label`, `base_price`, `status`
            ) VALUES (
                v_seat_id, p_event_id, p_artist, p_title, p_venue,
                p_section_id, p_section_name, v_seat_label, p_price, 'AVAILABLE'
            );

            SET s = s + 1;
        END WHILE;
        SET r = r + 1;
    END WHILE;
END//

DELIMITER ;

-- =========================================================================
-- EXAMPLE USAGE TRANSACTION (Execute in phpMyAdmin / MySQL CLI)
-- =========================================================================
/*
CALL `sp_create_event_with_inventory'(
    'OLIVIA-GUTS',
    'Olivia Rodrigo',
    'GUTS World Tour (Live)',
    'Philippine Arena (Bulacan)',
    'International',
    48000.00,
    20000.00,
    6000.00,
    'USR-ADMIN-01'
);
*/

SET FOREIGN_KEY_CHECKS = 1;
SQL;

if (($_GET['download'] ?? '') === '1') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="add_event_feature_schema.txt"');
    echo $migrationSql;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EntryPulse — SQL Schema & Migration File</title>
    <style>
        body { font-family: monospace; background: #0b0f19; color: #f3f4f6; padding: 2rem; }
        pre { background: #111827; border: 1px solid #374151; padding: 1.5rem; border-radius: 8px; overflow-x: auto; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 6px; font-weight: bold; margin-bottom: 1rem; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <h1>EntryPulse — Add Event Feature SQL Schema & Migration File</h1>
    <p>Ready to run in phpMyAdmin / MariaDB / MySQL.</p>
    <a href="?download=1" class="btn">⬇ Download SQL Migration File (add_event_feature_schema.txt)</a>
    <pre><?php echo htmlspecialchars($migrationSql); ?></pre>
</body>
</html>
