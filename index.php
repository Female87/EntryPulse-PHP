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

$scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'og files' . DIRECTORY_SEPARATOR . 'script.js';
$scriptVersion = is_file($scriptPath) ? (string)filemtime($scriptPath) : (string)time();
$frontendHtml = str_replace(
	'<script src="script.js"></script>',
	'<script src="../index.php?asset=app-script&amp;v=' . $scriptVersion . '"></script>',
	$frontendHtml
);

$headPosition += strlen('<head>');
$headInsert = "\n  <base href=\"og%20files/\">\n"
	. "  <style>\n"
	. "    body.admin-view.admin-user-view #sqlInspectorNavBtn,\n"
	. "    body.admin-view.admin-user-view #view-inspector,\n"
	. "    body.admin-view.admin-user-view button[onclick*=\"inspector\"] {\n"
	. "      display: none !important;\n"
	. "    }\n"
	. "  </style>\n";
$frontendHtml = substr_replace($frontendHtml, $headInsert, $headPosition, 0);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
echo $frontendHtml;