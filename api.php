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
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE ep_seat_inventory SET status = 'AVAILABLE', locked_by_user_id = NULL, lock_expires_at = NULL WHERE status = 'HELD' AND lock_expires_at < NOW()")->execute();
        $insert = $pdo->prepare("INSERT IGNORE INTO ep_seat_inventory (seat_id, event_id, event_artist, event_title, venue_name, section_id, section_name, seat_label, base_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($seats as $seat) {
            if (!is_array($seat) || empty($seat['seat_id'])) {
                continue;
            }
            $status = ($seat['status'] ?? '') === 'SOLD' ? 'SOLD' : 'AVAILABLE';
            $insert->execute([
                (string)$seat['seat_id'],
                (string)$event['id'],
                (string)($event['artist'] ?? ''),
                (string)($event['event'] ?? ''),
                (string)($event['venue'] ?? ''),
                (string)($seat['section_id'] ?? ''),
                (string)($seat['section_name'] ?? ''),
                (string)($seat['seat_label'] ?? ''),
                max(0, (float)($seat['base_price'] ?? 0)),
                $status,
            ]);
        }
        $query = $pdo->prepare('SELECT seat_id, status, locked_by_user_id, lock_expires_at FROM ep_seat_inventory WHERE event_id = ?');
        $query->execute([(string)$event['id']]);
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
        $price = filter_var($seat['base_price'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($seatId === '' || $eventId === '' || $price === false || $price < 0) {
            jsonResponse(['error' => 'Invalid seat details.'], 422);
        }

        $pdo->beginTransaction();
        $releasePrevious = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'AVAILABLE', locked_by_user_id = NULL, lock_expires_at = NULL WHERE status = 'HELD' AND locked_by_user_id = ? AND seat_id <> ?");
        $releasePrevious->execute([$user['user_id'], $seatId]);
        $expire = $pdo->prepare('UPDATE ep_seat_inventory SET status = \'AVAILABLE\', locked_by_user_id = NULL, lock_expires_at = NULL WHERE status = \'HELD\' AND lock_expires_at < NOW()');
        $expire->execute();
        $find = $pdo->prepare('SELECT * FROM ep_seat_inventory WHERE seat_id = ? FOR UPDATE');
        $find->execute([$seatId]);
        $existing = $find->fetch();
        if ($existing && $existing['status'] === 'SOLD') {
            $pdo->rollBack();
            jsonResponse(['error' => 'That seat has already been sold.'], 409);
        }
        if ($existing && $existing['status'] === 'HELD' && $existing['locked_by_user_id'] !== $user['user_id']) {
            $pdo->rollBack();
            jsonResponse(['error' => 'That seat is currently held by another buyer.'], 409);
        }

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE ep_seat_inventory SET status = 'HELD', locked_by_user_id = ?, lock_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), event_artist = ?, event_title = ?, venue_name = ?, section_id = ?, section_name = ?, seat_label = ?, base_price = ? WHERE seat_id = ?");
            $stmt->execute([$user['user_id'], (string)($event['artist'] ?? ''), (string)($event['event'] ?? ''), (string)($event['venue'] ?? ''), (string)($seat['section_id'] ?? ''), (string)($seat['section_name'] ?? ''), (string)($seat['seat_label'] ?? ''), $price, $seatId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO ep_seat_inventory (seat_id, event_id, event_artist, event_title, venue_name, section_id, section_name, seat_label, base_price, status, locked_by_user_id, lock_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'HELD', ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
            $stmt->execute([$seatId, $eventId, (string)($event['artist'] ?? ''), (string)($event['event'] ?? ''), (string)($event['venue'] ?? ''), (string)($seat['section_id'] ?? ''), (string)($seat['section_name'] ?? ''), (string)($seat['seat_label'] ?? ''), $price, $user['user_id']]);
        }
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
            'ticket_serial' => $ticket,
            'status' => 'ACTIVE',
            'purchased_at' => date(DATE_ATOM),
        ]]);
    }

    if ($action === 'orders') {
        $stmt = $pdo->prepare("SELECT order_id, event_id, event_artist AS artist, event_title AS event, venue_name AS venue, seat_id, seat_label, total_amount AS base_price, ticket_serial, order_status AS status, purchased_at FROM ep_orders WHERE user_id = ? ORDER BY purchased_at DESC");
        $stmt->execute([$user['user_id']]);
        jsonResponse(['orders' => $stmt->fetchAll()]);
    }

    jsonResponse(['error' => 'Unknown API action.'], 404);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log((string)$error);
    jsonResponse(['error' => 'The server could not complete the request. Check the PHP error log and database connection.'], 500);
}