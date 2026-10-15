<?php
declare(strict_types=1);

if (($_GET['asset'] ?? '') === 'app-script') {
	$scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'og files' . DIRECTORY_SEPARATOR . 'script.js';
	$script = file_get_contents($scriptPath);
	if ($script === false) {
		http_response_code(500);
		exit('Unable to load the EntryPulse application script.');
	}
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	header('Expires: 0');

	header('Content-Type: application/javascript; charset=utf-8');
	echo $script;
	exit;
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
$scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'og files' . DIRECTORY_SEPARATOR . 'script.js';
$scriptVersion = is_file($scriptPath) ? (string)filemtime($scriptPath) : (string)time();
$frontendHtml = str_replace(
	'<script src="script.js"></script>',
	'<script src="../index.php?asset=app-script&amp;v=' . $scriptVersion . '"></script>',
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

$headPosition += strlen('<head>');
$headInsert = "\n  <base href=\"og%20files/\">\n";
$frontendHtml = substr_replace($frontendHtml, $headInsert, $headPosition, 0);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
echo $frontendHtml;