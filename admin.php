<?php
// admin.php - 獨立的後台主題與表單控制器

require_once 'config.php';
require_once 'functions.php';

// --- 處理主題風格設定 ---
$theme_settings = [];
if (file_exists(THEME_SETTINGS_FILE)) {
    $theme_settings = json_decode(file_get_contents(THEME_SETTINGS_FILE), true);
}
$theme_defaults = [
    'glow-color' => '#00ffff', 'color-purple' => '#8a2be2', 'color-hot-pink' => '#ff69b4',
    'color-gold' => '#ffd700', 'color-electric-blue' => '#7df9ff',
];
$theme_settings = array_merge($theme_defaults, (is_array($theme_settings) ? $theme_settings : []));

// --- 處理表單控制設定 ---
$form_settings = [];
if (file_exists(FORM_SETTINGS_FILE)) {
    $form_settings = json_decode(file_get_contents(FORM_SETTINGS_FILE), true);
}
$form_defaults = [
    'start_date' => '', 'end_date' => '', 'card_width' => '280',
    'card_height' => '340', 'card_color' => 'rgba(0, 0, 0, 0.3)',
];
$form_settings = array_merge($form_defaults, (is_array($form_settings) ? $form_settings : []));


// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_theme') {
        $new_theme_settings = [
            'glow-color' => $_POST['glow-color'] ?? $theme_defaults['glow-color'],
            'color-purple' => $_POST['color-purple'] ?? $theme_defaults['color-purple'],
            'color-hot-pink' => $_POST['color-hot-pink'] ?? $theme_defaults['color-hot-pink'],
            'color-gold' => $_POST['color-gold'] ?? $theme_defaults['color-gold'],
            'color-electric-blue' => $_POST['color-electric-blue'] ?? $theme_defaults['color-electric-blue'],
        ];
        file_put_contents(THEME_SETTINGS_FILE, json_encode($new_theme_settings, JSON_PRETTY_PRINT));
        $theme_settings = $new_theme_settings;
        $success_message = "風格設定已儲存！";
    }

    if ($_POST['action'] === 'save_form_control') {
        $new_form_settings = [
            'start_date' => $_POST['start_date'] ?? $form_defaults['start_date'],
            'end_date' => $_POST['end_date'] ?? $form_defaults['end_date'],
            'card_width' => $_POST['card_width'] ?? $form_defaults['card_width'],
            'card_height' => $_POST['card_height'] ?? $form_defaults['card_height'],
            'card_color' => $_POST['card_color'] ?? $form_defaults['card_color'],
        ];
        file_put_contents(FORM_SETTINGS_FILE, json_encode($new_form_settings, JSON_PRETTY_PRINT));
        $form_settings = $new_form_settings;
        $success_message = "表單控制設定已儲存！";
    }
}

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理後台</title>
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
            
            <?php if (isset($success_message) && $_POST['action'] === 'save_theme'): ?>
                <div class="status-message success" style="display: block;"><?= $success_message ?></div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="save_theme">
                <div class="control-grid">
                    <div class="control-group">
                        <label for="glow-color">主要輝光 (科技青)</label>
                        <input type="color" id="glow-color" name="glow-color" value="<?= htmlspecialchars($theme_settings['glow-color']) ?>">
                    </div>
                    <div class="control-group">
                        <label for="color-purple">舞台燈光 (紫色)</label>
                        <input type="color" id="color-purple" name="color-purple" value="<?= htmlspecialchars($theme_settings['color-purple']) ?>">
                    </div>
                    <div class="control-group">
                        <label for="color-hot-pink">舞台燈光 (粉色)</label>
                        <input type="color" id="color-hot-pink" name="color-hot-pink" value="<?= htmlspecialchars($theme_settings['color-hot-pink']) ?>">
                    </div>
                    <div class="control-group">
                        <label for="color-gold">舞台燈光 (金色)</label>
                        <input type="color" id="color-gold" name="color-gold" value="<?= htmlspecialchars($theme_settings['color-gold']) ?>">
                    </div>
                    <div class="control-group">
                        <label for="color-electric-blue">舞台燈光 (藍色)</label>
                        <input type="color" id="color-electric-blue" name="color-electric-blue" value="<?= htmlspecialchars($theme_settings['color-electric-blue']) ?>">
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn-glow">儲存風格設定</button>
                </div>
            </form>
        </div>

        <div class="glass-card theme-controller">
            <div class="card-header">
                <h1>推薦表單控制器</h1>
                <p>設定推薦功能的開放時間與卡片樣式</p>
            </div>
            
            <?php if (isset($success_message) && $_POST['action'] === 'save_form_control'): ?>
                <div class="status-message success" style="display: block;"><?= $success_message ?></div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="save_form_control">
                
                <div class="control-section">
                    <h3>開放時間設定 (僅需選擇日期)</h3>
                    <div class="control-grid">
                        <div class="control-group">
                            <label for="start_date">開始日期</label>
                            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($form_settings['start_date']) ?>" class="form-input">
                        </div>
                        <div class="control-group">
                            <label for="end_date">結束日期</label>
                            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($form_settings['end_date']) ?>" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="control-section">
                    <h3>推薦卡片樣式</h3>
                    <div class="control-grid">
                        <div class="control-group">
                            <label for="card_width">卡片寬度 (px)</label>
                            <input type="number" id="card_width" name="card_width" value="<?= htmlspecialchars($form_settings['card_width']) ?>" class="form-input">
                        </div>
                        <div class="control-group">
                            <label for="card_height">卡片最小高度 (px)</label>
                            <input type="number" id="card_height" name="card_height" value="<?= htmlspecialchars($form_settings['card_height']) ?>" class="form-input">
                        </div>
                        <div class="control-group">
                            <label for="card_color">卡片背景顏色</label>
                            <input type="color" id="card_color" name="card_color" value="<?= htmlspecialchars($form_settings['card_color']) ?>">
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <a href="<?= BASE_URL ?>" class="btn-secondary">返回前台</a>
                    <button type="submit" class="btn-glow">儲存表單設定</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>