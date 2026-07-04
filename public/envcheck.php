<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
header('Content-Type: text/plain');
$k = 'AI_API_KEY';
echo "Diagnostic (values hidden)\n\n";
echo "ENV VAR $k:\n";
$g = getenv($k);
echo "  getenv:   " . ($g !== false && $g !== '' ? 'SET (len ' . strlen((string)$g) . ')' : 'not set') . "\n";
echo "  \$_SERVER: " . (isset($_SERVER[$k]) && $_SERVER[$k] !== '' ? 'SET' : 'not set') . "\n";
echo "  \$_ENV:    " . (isset($_ENV[$k]) && $_ENV[$k] !== '' ? 'SET' : 'not set') . "\n";

echo "\nSECRET FILES (/etc/secrets):\n";
$sd = '/etc/secrets';
if (is_dir($sd)) {
    $any = false;
    foreach (scandir($sd) as $f) {
        if ($f === '.' || $f === '..') continue;
        $p = $sd . '/' . $f;
        $len = is_file($p) ? strlen(trim((string)@file_get_contents($p))) : 0;
        echo "  [$f]  (content len $len)\n";
        $any = true;
    }
    if (!$any) echo "  (directory exists but is empty)\n";
} else {
    echo "  (no /etc/secrets directory — no secret files configured)\n";
}

echo "\nApp-root fallback (" . dirname(__DIR__) . "/$k): " . (is_file(dirname(__DIR__) . "/$k") ? 'present' : 'absent') . "\n";
