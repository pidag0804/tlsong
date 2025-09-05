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

// *** MODIFIED: 'admin' pages are handled by admin.php, not here ***
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
</head>
<body data-particle-effect="true"> <div class="main-container">
        <?php include "views/{$page}.php"; ?>
    </div>
    
    <script src="<?= BASE_URL ?>assets/main.js"></script>
    
    <?php if ($page === 'preview'): ?>
    <script src="https://www.youtube.com/iframe_api"></script>
    <?php endif; ?>
</body>
</html>