<?php
require_once 'config.php';
require_once 'functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = session_id();
}

// 根據 URL 參數決定顯示哪個頁面
$page = $_GET['page'] ?? 'dashboard';

// 'admin' 頁面由 admin.php 獨立處理
$allowed_pages = ['dashboard', 'form', 'preview', 'export']; 
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

$page_title = [
    'dashboard' => '系統中控台',
    'form' => '歌曲推薦系統',
    'preview' => '我的推薦清單',
    'export' => '所有玩家推薦腳本'
];

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title[$page] ?> - Dark-Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;700&family=Noto+Sans+TC:wght@400;700&family=Space+Grotesk:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">

    <?php
    // --- 載入並應用主題風格設定 ---
    if (file_exists(THEME_SETTINGS_FILE)) {
        $theme_json = file_get_contents(THEME_SETTINGS_FILE);
        $current_theme = json_decode($theme_json, true);
        if ($current_theme && is_array($current_theme)) {
            echo "<style>:root {\n";
            foreach ($current_theme as $key => $value) {
                if (str_starts_with($key, 'color-') || $key === 'glow-color') {
                    echo "\t--" . htmlspecialchars($key) . ": " . htmlspecialchars($value) . ";\n";
                }
            }
            echo "}\n</style>\n";
        }
    }
    
    // --- 載入並應用表單卡片樣式設定 ---
    if (file_exists(FORM_SETTINGS_FILE)) {
        $f_settings_json = file_get_contents(FORM_SETTINGS_FILE);
        $f_settings = json_decode($f_settings_json, true);
        if ($f_settings && is_array($f_settings)) {
            echo "<style>\n";
            // 選擇器 .song-entry 和 .preview-item 以提高樣式優先級
            echo ".song-entry, .preview-item {\n";
            if (!empty($f_settings['card_width'])) {
                echo "\t--card-min-width: " . htmlspecialchars($f_settings['card_width']) . "px;\n";
                echo "\t--card-max-width: " . htmlspecialchars($f_settings['card_width']) . "px;\n";
            }
            if (!empty($f_settings['card_height'])) {
                echo "\t--card-min-height: " . htmlspecialchars($f_settings['card_height']) . "px;\n";
            }
            if (!empty($f_settings['card_color'])) {
                // admin.php 的 color picker 回傳的是 HEX 格式，hslToHex 函式可以兼容處理
                $color_value = hslToHex($f_settings['card_color']);
                echo "\t--card-bg-color: " . htmlspecialchars($color_value) . ";\n";
            }
            echo "}\n";
            echo "</style>\n";
        }
    }
    ?>
</head>
<body data-particle-effect="true">
    <div class="main-container">
        <?php include "views/{$page}.php"; ?>
    </div>
    
    <script src="<?= BASE_URL ?>assets/main.js"></script>
    
    <?php if ($page === 'preview'): ?>
    <script src="https://www.youtube.com/iframe_api"></script>
    <?php endif; ?>
</body>
</html>