<?php
// 檢查推薦是否開放
if (!isRecommendationPeriodActive()) {
    // --- 如果不在開放期間，顯示關閉訊息 ---
    $settings = json_decode(file_get_contents(FORM_SETTINGS_FILE), true);
    $start_date = $settings['start_date'] ?? '未設定';
    $end_date = $settings['end_date'] ?? '未設定';
?>
    <div class="glass-card closed-message-card">
        <div class="card-header">
            <h2>推薦系統目前關閉</h2>
            <p>感謝您的參與，請於開放時間內再次前來推薦。</p>
        </div>
        <div class="stats-grid">
             <div class="stat-card">
                <h3><?= htmlspecialchars($start_date) ?></h3>
                <p>開始日期</p>
            </div>
            <div class="stat-card">
                <h3><?= htmlspecialchars($end_date) ?></h3>
                <p>結束日期</p>
            </div>
        </div>
        <div class="button-group">
            <a href="index.php?page=dashboard" class="btn-glow">返回首頁</a>
        </div>
    </div>
<?php
} else {
    // --- 如果在開放期間，顯示原有的表單內容 ---
    $pdo = getDbConnection();
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM user_recommendations WHERE user_id = ?");
    $stmt_count->execute([$_SESSION['user_id']]);
    $existing_count = $stmt_count->fetchColumn();
?>
<div class="glass-card">
    <div class="card-header">
        <h1>歌曲推薦系統</h1>
        <p>您已推薦 <?= $existing_count ?> 首，還可推薦 <?= MAX_RECOMMENDATIONS - $existing_count ?> 首</p>
    </div>
    <form id="recommendForm" method="post" action="api.php">
        <input type="hidden" name="action" value="recommend">

        <div class="add-button-container">
            <button type="button" id="add-song-btn" class="btn-glow">新增一首推薦</button>
        </div>

        <div id="song-entries" class="card-grid-container" data-existing-count="<?= $existing_count ?>">
            <div class="song-entry">
                <div class="form-group">
                    <label class="form-label">歌手名稱</label>
                    <input type="text" name="singer[]" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">歌曲名稱</label>
                    <input type="text" name="song_title[]" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">YouTube 歌曲網址</label>
                    <input type="url" name="song_url[]" class="form-input" required>
                </div>
            </div>
        </div>

        <div class="main-actions">
            <button type="button" id="open-search-btn" class="btn-glow">查詢現有歌曲</button>
            <button type="submit" class="btn-glow">送出檢查</button>
            <a href="index.php?page=dashboard" class="btn-glow">返回首頁</a>
        </div>
    </form>
    <div id="general-status" class="status-message"></div>
</div>

<div id="search-modal-backdrop" class="modal-backdrop">
    <div id="search-modal-content" class="glass-card modal-content">
        <div class="card-header">
            <h2>查詢現有歌曲</h2>
        </div>
        <div class="form-group">
            <input type="text" id="search-input" class="form-input" placeholder="輸入歌手或歌名進行模糊搜索...">
        </div>
        <div id="search-results" class="search-results-container">
            <p class="search-placeholder">請輸入關鍵字開始查詢</p>
        </div>
        <div class="button-group modal-footer">
            <button type="button" id="close-search-btn" class="btn-secondary">關閉</button>
        </div>
    </div>
</div>
<?php
} // 結束 else 區塊
?>