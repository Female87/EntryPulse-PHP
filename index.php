<?php
declare(strict_types=1);

if (($_GET['asset'] ?? '') === 'app-script') {
	$scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'og files' . DIRECTORY_SEPARATOR . 'script.js';
	$script = file_get_contents($scriptPath);
	if ($script === false) {
		http_response_code(500);
		exit('Unable to load the EntryPulse application script.');
	}

	$script = str_replace(
		['Admin: Demo inventory reset to seed state', '-- ADMIN DEMO RESET:', 'Demo verification code: '],
		['Admin: Inventory reset to seed state', '-- ADMIN RESET:', 'Verification code: '],
		$script
	);
	$script = str_replace(
		"document.body.classList.add('admin-view');",
		"document.body.classList.add('admin-view');\n        if (!new URLSearchParams(window.location.search).has('adminLayout')) { window.location.replace(window.location.pathname + '?adminLayout=1'); return; }",
		$script
	);
	$script = str_replace(
		"apiRequest('logout').catch(() => {});",
		"apiRequest('logout').finally(function () { if (new URLSearchParams(window.location.search).has('adminLayout')) window.location.replace(window.location.pathname); });",
		$script
	);
	header('Content-Type: application/javascript; charset=utf-8');
	echo $script;
	exit;
}

session_set_cookie_params([
	'httponly' => true,
	'samesite' => 'Lax',
	'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
$isAdminPage = false;
if (!empty($_SESSION['user_id'])) {
	try {
		require_once __DIR__ . '/config.php';
		$roleQuery = db()->prepare('SELECT role FROM users WHERE user_id = ?');
		$roleQuery->execute([$_SESSION['user_id']]);
		$isAdminPage = strtoupper((string)$roleQuery->fetchColumn()) === 'ADMIN';
	} catch (Throwable $error) {
		$isAdminPage = false;
	}
}

$frontendPath = __DIR__ . DIRECTORY_SEPARATOR . 'og files' . DIRECTORY_SEPARATOR . 'index.html';
$frontendHtml = file_get_contents($frontendPath);
$headPosition = stripos($frontendHtml ?: '', '<head>');

if ($frontendHtml === false || $headPosition === false) {
	http_response_code(500);
	exit('Unable to load the EntryPulse interface.');
}

$frontendHtml = preg_replace(
	'/\x{2605}\s*MAIN STAGE\s*\[=+\]\s*\x{2605}/u',
	'<i class="fa-solid fa-music" aria-hidden="true"></i> MAIN STAGE',
	$frontendHtml
);

$frontendHtml = str_replace(
	['Local XAMPP demo.', 'Developer Controls (Demo)', 'Reset Demo Sales', 'Event Catalog'],
	['Local XAMPP app.', 'Developer Controls', 'Reset Sales', 'Events'],
	$frontendHtml
);
$frontendHtml = str_replace(
	'<script src="script.js"></script>',
	'<script src="../index.php?asset=app-script"></script>',
	$frontendHtml
);

$catalogFilter = '          <button class="filter-btn" onclick="filterCategory(\'P-Pop & OPM\')">P-Pop & OPM (7)</button>';
$developerButton = '          <button class="filter-btn" type="button" onclick="switchView(\'inspector\')"><i class="fa-solid fa-terminal" aria-hidden="true"></i> Developer Console</button>';
$frontendHtml = str_replace($catalogFilter, $catalogFilter . "\n" . $developerButton, $frontendHtml);

$queueResetButton = '              <button class="queue-admin-btn" onclick="queueReset()">' . "\n"
	. '                <i class="fa-solid fa-rotate-left"></i> Reset Queue' . "\n"
	. '              </button>';
$queueDeveloperButton = '              <button class="queue-admin-btn" type="button" onclick="switchView(\'inspector\')">' . "\n"
	. '                <i class="fa-solid fa-terminal" aria-hidden="true"></i> Developer Console' . "\n"
	. '              </button>';
$frontendHtml = preg_replace(
	'/(<button class="queue-admin-btn" onclick="queueReset\(\)">\s*<i class="fa-solid fa-rotate-left"><\/i> Reset Queue\s*<\/button>)/',
	'$1' . "\n" . $queueDeveloperButton,
	$frontendHtml,
	1
);

if ($isAdminPage) {
	$frontendHtml = preg_replace(
		'~<section id="view-events" class="view-section active">.*?</section>~s',
		'<div id="eventsGrid" hidden></div>',
		$frontendHtml,
		1
	);
	$frontendHtml = preg_replace(
		'~<button class="nav-btn active" onclick="switchView\(\'events\'\)">.*?</button>~s',
		'',
		$frontendHtml,
		1
	);
}

$headPosition += strlen('<head>');
$headInsert = "\n  <base href=\"og%20files/\">\n"
	. "  <style>\n"
	. "    body.admin-view:not(.admin-user-view) #navTabsContainer > button[onclick=\"switchView('events')\"],\n"
	. "    body.admin-view:not(.admin-user-view) #view-events,\n"
	. "    body.admin-view #adminViewToggleBtn { display: none !important; }\n"
	. "  </style>";
$frontendHtml = substr_replace($frontendHtml, $headInsert, $headPosition, 0);

header('Content-Type: text/html; charset=utf-8');
echo $frontendHtml;