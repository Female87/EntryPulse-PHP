<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
require __DIR__ . '/config.php';

try {
    $pdo = db();
    $data = requestData();
    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'me') {
        if (empty($_SESSION['user_id'])) {
            jsonResponse(['user' => null]);
        }
        $user = requireUser($pdo);
        jsonResponse(['user' => $user]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'orders')) {
        jsonResponse(['error' => 'Unsupported request method.'], 405);
    }

    if ($action === 'register') {
        $username = trim((string)($data['username'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            jsonResponse(['error' => 'Username must be 3 to 50 letters, numbers, dots, underscores, or hyphens.'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            jsonResponse(['error' => 'Enter a valid email address.'], 422);
        }
        if (strlen($password) < 6) {
            jsonResponse(['error' => 'Password must be at least 6 characters.'], 422);
        }
        $userId = newId('usr');
        try {
            $stmt = $pdo->prepare('INSERT INTO users (user_id, username, email, password_hash) VALUES (?, ?, ?, ?)');
            $stmt->execute([$userId, $username, $email, password_hash($password, PASSWORD_DEFAULT)]);
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                jsonResponse(['error' => 'That username or email is already registered.'], 409);
            }
            throw $error;
        }
        jsonResponse(['message' => 'Account created. You can now log in.']);
    }

    if ($action === 'login') {
        $identifier = trim((string)($data['identifier'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $stmt = $pdo->prepare('SELECT user_id, username, email, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$identifier, strtolower($identifier)]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            jsonResponse(['error' => 'Incorrect username or password.'], 401);
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        unset($user['password_hash']);
        jsonResponse(['user' => $user]);
    }

    if ($action === 'logout') {
        if (!empty($_SESSION['user_id'])) {
            $release = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'AVAILABLE', locked_by_user_id = NULL, lock_expires_at = NULL WHERE status = 'HELD' AND locked_by_user_id = ?");
            $release->execute([$_SESSION['user_id']]);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        jsonResponse(['message' => 'Logged out.']);
    }

    if ($action === 'queue-duration') {
        $eventId = trim((string)($data['event_id'] ?? ''));
        if ($eventId === '') {
            jsonResponse(['error' => 'An event ID is required.'], 422);
        }
        $durations = [180, 240, 300];
        jsonResponse([
            'event_id' => $eventId,
            'duration_seconds' => $durations[random_int(0, count($durations) - 1)],
            'initial_position' => random_int(12, 180),
        ]);
    }

    $user = requireUser($pdo);

    if ($action === 'inventory') {
        $event = $data['event'] ?? null;
        $seats = $data['seats'] ?? null;
        if (!is_array($event) || !is_array($seats) || empty($event['id'])) {
            jsonResponse(['error' => 'Event inventory details are required.'], 422);
        }
        $eventId = trim((string)$event['id']);
        $catalogQuery = $pdo->prepare('SELECT event_id, event_artist, event_title, venue_name, vip_price, lower_price, upper_price FROM ep_event_catalog WHERE event_id = ? AND is_active = 1');
        $catalogQuery->execute([$eventId]);
        $catalogEvent = $catalogQuery->fetch();
        if (!$catalogEvent) {
            jsonResponse(['error' => 'That event is not in the approved catalog.'], 422);
        }

        $sections = [
            'VIP-A' => ['name' => 'VIP A', 'price' => (float)$catalogEvent['vip_price']],
            'VIP-B' => ['name' => 'VIP B', 'price' => (float)$catalogEvent['vip_price']],
            'LB-1' => ['name' => 'Lower Box 1', 'price' => (float)$catalogEvent['lower_price']],
            'LB-2' => ['name' => 'Lower Box 2', 'price' => round((float)$catalogEvent['lower_price'] * 0.85)],
            'UB' => ['name' => 'Upper Box', 'price' => round((float)$catalogEvent['upper_price'] * 1.5)],
            'GA' => ['name' => 'Gen Ad', 'price' => (float)$catalogEvent['upper_price']],
        ];
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE ep_seat_inventory SET status = 'AVAILABLE', locked_by_user_id = NULL, lock_expires_at = NULL WHERE status = 'HELD' AND lock_expires_at < NOW()")->execute();
        $insert = $pdo->prepare("INSERT IGNORE INTO ep_seat_inventory (seat_id, event_id, event_artist, event_title, venue_name, section_id, section_name, seat_label, base_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'AVAILABLE')");
        foreach ($seats as $seat) {
            if (!is_array($seat) || empty($seat['seat_id'])) {
                $pdo->rollBack();
                jsonResponse(['error' => 'Every inventory seat must have a seat ID.'], 422);
            }
            $seatId = (string)$seat['seat_id'];
            $prefix = $eventId . '-';
            if (strpos($seatId, $prefix) !== 0 || !preg_match('/\A([A-Z0-9-]+)-R([1-2])-S([1-6])\z/', substr($seatId, strlen($prefix)), $seatParts)) {
                $pdo->rollBack();
                jsonResponse(['error' => 'An inventory seat ID is outside the approved seat layout.'], 422);
            }
            $sectionId = $seatParts[1];
            if (!isset($sections[$sectionId])) {
                $pdo->rollBack();
                jsonResponse(['error' => 'An inventory seat uses an unknown section.'], 422);
            }
            $section = $sections[$sectionId];
            $row = (int)$seatParts[2];
            $seatNumber = (int)$seatParts[3];
            $insert->execute([
                $seatId,
                $eventId,
                $catalogEvent['event_artist'],
                $catalogEvent['event_title'],
                $catalogEvent['venue_name'],
                $sectionId,
                $section['name'],
                $section['name'] . ' - Row ' . $row . ', Seat ' . $seatNumber,
                $section['price'],
            ]);
            $verifySeat = $pdo->prepare('SELECT event_id, section_id FROM ep_seat_inventory WHERE seat_id = ?');
            $verifySeat->execute([$seatId]);
            $storedSeat = $verifySeat->fetch();
            if (!$storedSeat || $storedSeat['event_id'] !== $eventId || $storedSeat['section_id'] !== $sectionId) {
                $pdo->rollBack();
                jsonResponse(['error' => 'A seat ID already exists with conflicting event or section data.'], 409);
            }
            $normalize = $pdo->prepare('UPDATE ep_seat_inventory SET event_artist = ?, event_title = ?, venue_name = ?, section_name = ?, seat_label = ?, base_price = ? WHERE seat_id = ?');
            $normalize->execute([
                $catalogEvent['event_artist'],
                $catalogEvent['event_title'],
                $catalogEvent['venue_name'],
                $section['name'],
                $section['name'] . ' - Row ' . $row . ', Seat ' . $seatNumber,
                $section['price'],
                $seatId,
            ]);
        }
        $query = $pdo->prepare('SELECT seat_id, status, locked_by_user_id, lock_expires_at, section_id, section_name, seat_label, base_price FROM ep_seat_inventory WHERE event_id = ?');
        $query->execute([$eventId]);
        $pdo->commit();
        jsonResponse(['seats' => $query->fetchAll()]);
    }

    if ($action === 'hold') {
        $required = ['seat', 'event'];
        foreach ($required as $key) {
            if (!isset($data[$key]) || !is_array($data[$key])) {
                jsonResponse(['error' => 'Seat and event details are required.'], 422);
            }
        }
        $seat = $data['seat'];
        $event = $data['event'];
        $seatId = (string)($seat['seat_id'] ?? '');
        $eventId = (string)($event['id'] ?? '');
        if ($seatId === '' || $eventId === '') {
            jsonResponse(['error' => 'Invalid seat details.'], 422);
        }

        $catalogQuery = $pdo->prepare('SELECT event_id, event_artist, event_title, venue_name, vip_price, lower_price, upper_price FROM ep_event_catalog WHERE event_id = ? AND is_active = 1');
        $catalogQuery->execute([$eventId]);
        $catalogEvent = $catalogQuery->fetch();
        if (!$catalogEvent || strpos($seatId, $eventId . '-') !== 0) {
            jsonResponse(['error' => 'The requested event or seat is not approved.'], 422);
        }

        $pdo->beginTransaction();
        $releasePrevious = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'AVAILABLE', locked_by_user_id = NULL, lock_expires_at = NULL WHERE status = 'HELD' AND locked_by_user_id = ? AND seat_id <> ?");
        $releasePrevious->execute([$user['user_id'], $seatId]);
        $expire = $pdo->prepare('UPDATE ep_seat_inventory SET status = \'AVAILABLE\', locked_by_user_id = NULL, lock_expires_at = NULL WHERE status = \'HELD\' AND lock_expires_at < NOW()');
        $expire->execute();
        $find = $pdo->prepare('SELECT * FROM ep_seat_inventory WHERE seat_id = ? FOR UPDATE');
        $find->execute([$seatId]);
        $existing = $find->fetch();
        if (!$existing || $existing['event_id'] !== $eventId) {
            $pdo->rollBack();
            jsonResponse(['error' => 'That seat is not available in the selected event inventory.'], 404);
        }
        if ($existing && $existing['status'] === 'SOLD') {
            $pdo->rollBack();
            jsonResponse(['error' => 'That seat has already been sold.'], 409);
        }
        if ($existing && $existing['status'] === 'HELD' && $existing['locked_by_user_id'] !== $user['user_id']) {
            $pdo->rollBack();
            jsonResponse(['error' => 'That seat is currently held by another buyer.'], 409);
        }

        $sectionPrices = [
            'VIP-A' => (float)$catalogEvent['vip_price'],
            'VIP-B' => (float)$catalogEvent['vip_price'],
            'LB-1' => (float)$catalogEvent['lower_price'],
            'LB-2' => round((float)$catalogEvent['lower_price'] * 0.85),
            'UB' => round((float)$catalogEvent['upper_price'] * 1.5),
            'GA' => (float)$catalogEvent['upper_price'],
        ];
        if (!array_key_exists($existing['section_id'], $sectionPrices)) {
            $pdo->rollBack();
            jsonResponse(['error' => 'That seat has an invalid ticket section.'], 422);
        }
        $stmt = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'HELD', locked_by_user_id = ?, lock_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), event_artist = ?, event_title = ?, venue_name = ?, base_price = ? WHERE seat_id = ?");
        $stmt->execute([$user['user_id'], $catalogEvent['event_artist'], $catalogEvent['event_title'], $catalogEvent['venue_name'], $sectionPrices[$existing['section_id']], $seatId]);
        $pdo->commit();
        jsonResponse(['message' => 'Seat held for 10 minutes.', 'expires_at' => date(DATE_ATOM, time() + 600)]);
    }

    if ($action === 'release') {
        $stmt = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'AVAILABLE', locked_by_user_id = NULL, lock_expires_at = NULL WHERE seat_id = ? AND status = 'HELD' AND locked_by_user_id = ?");
        $stmt->execute([(string)($data['seat_id'] ?? ''), $user['user_id']]);
        jsonResponse(['released' => $stmt->rowCount() > 0]);
    }

    if ($action === 'checkout') {
        $seatId = (string)($data['seat_id'] ?? '');
        $method = strtoupper((string)($data['payment_method'] ?? ''));
        if (!in_array($method, ['GCASH', 'MAYA', 'CARD'], true)) {
            jsonResponse(['error' => 'Choose a valid payment method.'], 422);
        }
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM ep_seat_inventory WHERE seat_id = ? AND status = 'HELD' AND locked_by_user_id = ? AND lock_expires_at > NOW() FOR UPDATE");
        $stmt->execute([$seatId, $user['user_id']]);
        $seat = $stmt->fetch();
        if (!$seat) {
            $pdo->rollBack();
            jsonResponse(['error' => 'Your seat hold expired. Please select the seat again.'], 409);
        }
        $orderId = newId('ord');
        $ticket = 'EP-' . strtoupper(bin2hex(random_bytes(8)));
        $insert = $pdo->prepare('INSERT INTO ep_orders (order_id, user_id, event_id, event_artist, event_title, venue_name, seat_id, seat_label, total_amount, payment_method, ticket_serial) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->execute([$orderId, $user['user_id'], $seat['event_id'], $seat['event_artist'], $seat['event_title'], $seat['venue_name'], $seat['seat_id'], $seat['seat_label'], $seat['base_price'], $method, $ticket]);
        $update = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'SOLD', locked_by_user_id = NULL, lock_expires_at = NULL, ticket_serial = ? WHERE seat_id = ?");
        $update->execute([$ticket, $seatId]);
        $pdo->commit();
        jsonResponse(['order' => [
            'order_id' => $orderId,
            'event_id' => $seat['event_id'],
            'artist' => $seat['event_artist'],
            'event' => $seat['event_title'],
            'venue' => $seat['venue_name'],
            'seat_id' => $seat['seat_id'],
            'seat_label' => $seat['seat_label'],
            'base_price' => (float)$seat['base_price'],
            'payment_method' => $method,
            'ticket_serial' => $ticket,
            'status' => 'ACTIVE',
            'purchased_at' => date(DATE_ATOM),
        ]]);
    }

    if ($action === 'orders') {
        if ($user['role'] === 'ADMIN') {
            $stmt = $pdo->query("SELECT o.order_id, o.user_id, u.username, o.event_id, o.event_artist AS artist, o.event_title AS event, o.venue_name AS venue, o.seat_id, o.seat_label, o.total_amount AS base_price, o.payment_method, o.ticket_serial, o.order_status AS status, o.purchased_at FROM ep_orders o JOIN users u ON u.user_id = o.user_id ORDER BY o.purchased_at DESC");
            $refunds = $pdo->query("SELECT r.refund_id, r.order_id, r.user_id AS session_id, u.username, o.event_id, o.event_artist AS artist, o.event_title AS event, o.seat_id, o.seat_label, r.amount, r.reason, r.status, r.created_at AS processed_at FROM ep_refunds r JOIN users u ON u.user_id = r.user_id JOIN ep_orders o ON o.order_id = r.order_id ORDER BY r.created_at DESC")->fetchAll();
        } else {
            $stmt = $pdo->prepare("SELECT o.order_id, o.user_id, u.username, o.event_id, o.event_artist AS artist, o.event_title AS event, o.venue_name AS venue, o.seat_id, o.seat_label, o.total_amount AS base_price, o.payment_method, o.ticket_serial, o.order_status AS status, o.purchased_at FROM ep_orders o JOIN users u ON u.user_id = o.user_id WHERE o.user_id = ? ORDER BY o.purchased_at DESC");
            $stmt->execute([$user['user_id']]);
            $refundStmt = $pdo->prepare("SELECT r.refund_id, r.order_id, r.user_id AS session_id, u.username, o.event_id, o.event_artist AS artist, o.event_title AS event, o.seat_id, o.seat_label, r.amount, r.reason, r.status, r.created_at AS processed_at FROM ep_refunds r JOIN users u ON u.user_id = r.user_id JOIN ep_orders o ON o.order_id = r.order_id WHERE r.user_id = ? ORDER BY r.created_at DESC");
            $refundStmt->execute([$user['user_id']]);
            $refunds = $refundStmt->fetchAll();
        }
        jsonResponse(['orders' => $stmt->fetchAll(), 'refunds' => $refunds]);
    }

    if ($action === 'refund') {
        $orderId = trim((string)($data['order_id'] ?? ''));
        if ($orderId === '') {
            jsonResponse(['error' => 'An order ID is required.'], 422);
        }
        $pdo->beginTransaction();
        $find = $pdo->prepare("SELECT o.order_id, o.user_id, o.seat_id, o.total_amount, o.order_status, s.status AS seat_status, s.ticket_serial FROM ep_orders o JOIN ep_seat_inventory s ON s.seat_id = o.seat_id WHERE o.order_id = ? AND o.user_id = ? AND NOT EXISTS (SELECT 1 FROM ep_gate_scans g WHERE g.ticket_serial = o.ticket_serial) FOR UPDATE");
        $find->execute([$orderId, $user['user_id']]);
        $order = $find->fetch();
        if (!$order || $order['order_status'] !== 'ACTIVE' || $order['seat_status'] !== 'SOLD') {
            $pdo->rollBack();
            jsonResponse(['error' => 'This order is no longer eligible for cancellation.'], 409);
        }
        $refundId = newId('ref');
        $pdo->prepare("UPDATE ep_orders SET order_status = 'REFUNDED' WHERE order_id = ? AND order_status = 'ACTIVE'")->execute([$orderId]);
        $release = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'AVAILABLE', ticket_serial = NULL, locked_by_user_id = NULL, lock_expires_at = NULL WHERE seat_id = ? AND status = 'SOLD' AND ticket_serial = ?");
        $release->execute([$order['seat_id'], $order['ticket_serial']]);
        if ($release->rowCount() !== 1) {
            $pdo->rollBack();
            jsonResponse(['error' => 'The ticket inventory no longer matches this order. No refund was recorded.'], 409);
        }
        $insert = $pdo->prepare("INSERT INTO ep_refunds (refund_id, order_id, user_id, amount, reason, status) VALUES (?, ?, ?, ?, ?, 'PROCESSED')");
        $insert->execute([$refundId, $orderId, $user['user_id'], $order['total_amount'], 'Consumer-initiated cancellation']);
        $pdo->commit();
        jsonResponse(['refund' => [
            'refund_id' => $refundId,
            'order_id' => $orderId,
            'status' => 'PROCESSED',
            'amount' => (float)$order['total_amount'],
            'created_at' => date(DATE_ATOM),
        ]]);
    }

    if ($action === 'scan-ticket') {
        $ticketSerial = strtoupper(trim((string)($data['ticket_serial'] ?? '')));
        if ($ticketSerial === '') {
            jsonResponse(['error' => 'A ticket serial is required.'], 422);
        }
        $pdo->beginTransaction();
        $priorScan = $pdo->prepare('SELECT scanned_at FROM ep_gate_scans WHERE ticket_serial = ?');
        $priorScan->execute([$ticketSerial]);
        $scannedAt = $priorScan->fetchColumn();
        if ($scannedAt !== false) {
            $pdo->rollBack();
            jsonResponse(['error' => 'This ticket has already been scanned at ' . $scannedAt . '.'], 409);
        }
        $find = $pdo->prepare("SELECT o.order_id, o.user_id, o.event_id, o.event_artist AS artist, o.event_title AS event, o.venue_name AS venue, o.seat_id, o.seat_label, o.total_amount AS base_price, o.payment_method, o.ticket_serial, o.order_status AS status, o.purchased_at FROM ep_orders o JOIN ep_seat_inventory s ON s.seat_id = o.seat_id AND s.ticket_serial = o.ticket_serial AND s.status = 'SOLD' WHERE o.ticket_serial = ? AND o.order_status = 'ACTIVE' FOR UPDATE");
        $find->execute([$ticketSerial]);
        $order = $find->fetch();
        if (!$order) {
            $pdo->rollBack();
            $priorScan->execute([$ticketSerial]);
            if ($priorScan->fetchColumn() !== false) {
                jsonResponse(['error' => 'This ticket has already been scanned.'], 409);
            }
            jsonResponse(['error' => 'No active ticket matches that barcode.'], 404);
        }
        try {
            $insert = $pdo->prepare('INSERT INTO ep_gate_scans (scan_id, ticket_serial, user_id, gate_id) VALUES (?, ?, ?, ?)');
            $insert->execute([newId('scan'), $ticketSerial, $user['user_id'], 'Gate 04']);
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                $pdo->rollBack();
                jsonResponse(['error' => 'This ticket has already been scanned.'], 409);
            }
            throw $error;
        }
        $pdo->prepare("UPDATE ep_orders SET order_status = 'USED' WHERE order_id = ? AND order_status = 'ACTIVE'")->execute([$order['order_id']]);
        $pdo->commit();
        $order['base_price'] = (float)$order['base_price'];
        $order['status'] = 'USED';
        jsonResponse(['order' => $order, 'gate' => 'Gate 04']);
    }

    if ($action === 'admin-release-holds') {
        if ($user['role'] !== 'ADMIN') {
            jsonResponse(['error' => 'Admin privileges are required for this action.'], 403);
        }
        $pdo->beginTransaction();
        $release = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'AVAILABLE', locked_by_user_id = NULL, lock_expires_at = NULL WHERE status = 'HELD'");
        $release->execute();
        $releasedCount = $release->rowCount();
        $audit = $pdo->prepare('INSERT INTO ep_admin_audit (audit_id, actor_user_id, action, details) VALUES (?, ?, ?, ?)');
        $audit->execute([newId('audit'), $user['user_id'], 'RELEASE_ALL_HOLDS', "Released {$releasedCount} held seats"]);
        $pdo->commit();
        jsonResponse(['released' => $releasedCount]);
    }

    jsonResponse(['error' => 'Unknown API action.'], 404);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log((string)$error);
    jsonResponse(['error' => 'The server could not complete the request. Check the PHP error log and database connection.'], 500);
}