<?php
// admin.php - 獨立的後台主題控制器

require_once 'config.php';
require_once 'functions.php';

// --- 儲存主題設定的後端邏輯 ---
$current_settings = [];
if (file_exists(THEME_SETTINGS_FILE)) {
    $current_settings = json_decode(file_get_contents(THEME_SETTINGS_FILE), true);
}

$defaults = [
    'glow-color' => '#00ffff',
    'color-purple' => '#8a2be2',
    'color-hot-pink' => '#ff69b4',
    'color-gold' => '#ffd700',
    'color-electric-blue' => '#7df9ff',
];
$settings = array_merge($defaults, (is_array($current_settings) ? $current_settings : []));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_theme') {
    $new_settings = [
        'glow-color' => $_POST['glow-color'] ?? $defaults['glow-color'],
        'color-purple' => $_POST['color-purple'] ?? $defaults['color-purple'],
        'color-hot-pink' => $_POST['color-hot-pink'] ?? $defaults['color-hot-pink'],
        'color-gold' => $_POST['color-gold'] ?? $defaults['color-gold'],
        'color-electric-blue' => $_POST['color-electric-blue'] ?? $defaults['color-electric-blue'],
    ];
    
    file_put_contents(THEME_SETTINGS_FILE, json_encode($new_settings, JSON_PRETTY_PRINT));
    
    $settings = $new_settings;
    $success_message = "風格設定已儲存！請到前台頁面查看更新。";
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理後台 - 主題風格控制器</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;700&family=Noto+Sans+TC:wght@400;700&family=Space+Grotesk:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">

    <?php
    if (file_exists(THEME_SETTINGS_FILE)) {
        $settings_json = file_get_contents(THEME_SETTINGS_FILE);
        $current_theme = json_decode($settings_json, true);
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
    ?>
</head>
<body data-particle-effect="true">
    <div class="main-container">
        <div class="glass-card theme-controller">
            <div class="card-header">
                <h1>主題風格控制器</h1>
                <p>即時調整網站的燈光與輝光效果</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="status-message success" style="display: block;"><?= $success_message ?></div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="save_theme">
                <div class="control-grid">
                    <div class="control-group">
                        <label for="glow-color">主要輝光 (科技青)</label>
                        <input type="color" id="glow-color" name="glow-color" value="<?= htmlspecialchars($settings['glow-color']) ?>">
                    </div>
                    <div class="control-group">
                        <label for="color-purple">舞台燈光 (紫色)</label>
                        <input type="color" id="color-purple" name="color-purple" value="<?= htmlspecialchars($settings['color-purple']) ?>">
                    </div>
                    <div class="control-group">
                        <label for="color-hot-pink">舞台燈光 (粉色)</label>
                        <input type="color" id="color-hot-pink" name="color-hot-pink" value="<?= htmlspecialchars($settings['color-hot-pink']) ?>">
                    </div>
                    <div class="control-group">
                        <label for="color-gold">舞台燈光 (金色)</label>
                        <input type="color" id="color-gold" name="color-gold" value="<?= htmlspecialchars($settings['color-gold']) ?>">
                    </div>
                    <div class="control-group">
                        <label for="color-electric-blue">舞台燈光 (藍色)</label>
                        <input type="color" id="color-electric-blue" name="color-electric-blue" value="<?= htmlspecialchars($settings['color-electric-blue']) ?>">
                    </div>
                </div>
                <div class="button-group">
                    <a href="<?= BASE_URL ?>" class="btn-secondary">返回前台</a>
                    <button type="submit" class="btn-glow">儲存風格設定</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>