<?php
// 頁面數據查詢邏輯
$pdo = getDbConnection();

// 只查詢當前用戶的推薦數
$stmt_user_recs = $pdo->prepare("SELECT COUNT(*) FROM user_recommendations WHERE user_id = ?");
$stmt_user_recs->execute([$_SESSION['user_id']]);
$user_recommend_count = $stmt_user_recs->fetchColumn();
?>

<div class="glass-card dashboard">
    <div class="card-header">
        <h1>系統介紹</h1>
        <p>系統會幫您檢查本期是否已經有人推薦該歌曲以及幫您檢查歌曲是否已經存在遊戲歌單上,所以務必確實填寫必要資料
            <br>日本或韓國歌手 , 必須把歌手,歌名,翻譯成大家熟知的中文或英文。
            <br>動漫歌曲部分，歌手請填入【該動漫名稱】以便在遊戲歌單上尋找。</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $user_recommend_count ?> / <?= MAX_RECOMMENDATIONS ?></h3>
            <p>我的已推薦數</p>
        </div>
    </div>

    <div class="nav-grid">
        <a href="index.php?page=form" class="nav-card">
            <div class="nav-icon">🎵</div>
            <h2>推薦歌曲</h2>
            <p>本次推歌最後收單時間為12月5日。</p>
        </a>
        <a href="index.php?page=preview" class="nav-card">
            <div class="nav-icon">🎬</div>
            <h2>推薦清單</h2>
            <p>查看、編輯或預覽您已推薦的歌曲。</p>
        </a>
        <a href="index.php?page=export" class="nav-card">
            <div class="nav-icon">📋</div>
            <h2>匯出腳本</h2>
            <p>產生所有玩家的推薦腳本以供使用。</p>
        </a>
    </div>
</div>