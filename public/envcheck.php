<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
header('Content-Type: text/plain');
$k = 'AI_API_KEY';
echo "Diagnostic for $k (values hidden)\n\n";
$g = getenv($k);
echo "getenv:   " . ($g !== false && $g !== '' ? 'SET (len ' . strlen((string)$g) . ')' : 'not set') . "\n";
echo "\$_SERVER: " . (isset($_SERVER[$k]) && $_SERVER[$k] !== '' ? 'SET (len ' . strlen((string)$_SERVER[$k]) . ')' : 'not set') . "\n";
echo "\$_ENV:    " . (isset($_ENV[$k]) && $_ENV[$k] !== '' ? 'SET (len ' . strlen((string)$_ENV[$k]) . ')' : 'not set') . "\n";
echo "\ncounts: \$_ENV=" . count($_ENV) . "  \$_SERVER=" . count($_SERVER) . "\n";
echo "\nAI/API/KEY-ish variable NAMES visible to PHP (values hidden):\n";
$seen = [];
foreach ([$_SERVER, $_ENV] as $arr) {
    foreach ($arr as $kk => $vv) {
        if (preg_match('/AI|API|KEY|MODEL|BASE|DEEPSEEK|OPENAI|TOKEN|SECRET/i', (string)$kk)) $seen[$kk] = true;
    }
}
foreach (array_keys($seen) as $kk) echo "  [" . $kk . "]\n";
if (!$seen) echo "  (none found — the host is not exposing these env vars to PHP)\n";
