<?php
// 建立資料庫連線 (PDO)
function getDbConnection() {
    require_once 'config.php';
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

// 從 YouTube URL 中提取影片 ID
function getYouTubeEmbedUrl($url) {
    preg_match(
        '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/',
        $url,
        $matches
    );
    return $matches[1] ?? null;
}

// *** NEW: HSL to HEX Color Conversion Function ***
function hslToHex($hsl_string) {
    // Extract H, S, L values from string like "hsl(280, 90%, 55%)"
    if (!preg_match('/hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)/', $hsl_string, $matches)) {
        return '#000000'; // Return black on failure
    }
    list(, $h, $s, $l) = $matches;

    $h /= 360;
    $s /= 100;
    $l /= 100;

    if ($s == 0) {
        $r = $g = $b = $l;
    } else {
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $r = hue2rgb($p, $q, $h + 1/3);
        $g = hue2rgb($p, $q, $h);
        $b = hue2rgb($p, $q, $h - 1/3);
    }

    return sprintf("#%02x%02x%02x", $r * 255, $g * 255, $b * 255);
}

function hue2rgb($p, $q, $t) {
    if ($t < 0) $t += 1;
    if ($t > 1) $t -= 1;
    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
    if ($t < 1/2) return $q;
    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
    return $p;
}