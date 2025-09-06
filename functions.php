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

// HSL 轉 HEX 顏色格式的工具函式
function hslToHex($hsl_string) {
    // 如果傳入的已經是 HEX 格式，直接回傳
    if (str_starts_with($hsl_string, '#')) {
        return $hsl_string;
    }
    if (!preg_match('/hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)/', $hsl_string, $matches)) {
        return '#000000'; // 轉換失敗則回傳黑色
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

/**
 * 檢查目前是否在推薦開放期間內
 * @return bool
 */
function isRecommendationPeriodActive() {
    // 檢查設定檔是否存在
    if (!file_exists(FORM_SETTINGS_FILE)) {
        return true; // 如果設定檔不存在，預設為開放
    }
    
    $settings_json = file_get_contents(FORM_SETTINGS_FILE);
    $settings = json_decode($settings_json, true);
    
    $start_date_str = $settings['start_date'] ?? '';
    $end_date_str = $settings['end_date'] ?? '';

    // 如果日期未設定，預設為開放
    if (empty($start_date_str) || empty($end_date_str)) {
        return true;
    }

    try {
        // 設定時區為臺北時間
        $timezone = new DateTimeZone('Asia/Taipei');
        
        // 建立開始日期物件 (從當天 00:00:00 開始)
        $start_date = new DateTime($start_date_str . ' 00:00:00', $timezone);
        
        // 建立結束日期物件 (到當天 23:59:59 結束)
        $end_date = new DateTime($end_date_str . ' 23:59:59', $timezone);
        
        // 取得目前時間
        $now = new DateTime('now', $timezone);
        
        // 判斷目前時間是否在區間內
        return $now >= $start_date && $now <= $end_date;
        
    } catch (Exception $e) {
        // 如果日期格式錯誤，預設為開放以避免系統完全鎖定
        return true;
    }
}