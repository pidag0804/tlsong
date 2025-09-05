<?php
$pdo = getDbConnection();
// 查詢時需包含 id 以便編輯功能使用
$stmt = $pdo->prepare("SELECT id, singer, song_title, song_url FROM user_recommendations WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_recs = $stmt->fetchAll();
?>
<div class="glass-card">
    <div class="card-header">
        <h1>我的推薦清單 (<?= count($user_recs) ?>/<?= MAX_RECOMMENDATIONS ?>)</h1>
    </div>
    <?php if (empty($user_recs)): ?>
        <p style="text-align:center;">您目前還沒有推薦任何歌曲。</p>
    <?php else: ?>
        <ul class="preview-list card-grid-container">
            <?php foreach ($user_recs as $index => $rec): ?>
                <li class="preview-item" 
                    data-id="<?= $rec['id'] ?>" 
                    data-singer="<?= htmlspecialchars($rec['singer']) ?>" 
                    data-song-title="<?= htmlspecialchars($rec['song_title']) ?>" 
                    data-song-url="<?= htmlspecialchars($rec['song_url']) ?>">
                    
                    <div class="song-info">
                        <p><strong>歌手:</strong> <span class="singer-text"><?= htmlspecialchars($rec['singer']) ?></span></p>
                        <p><strong>歌名:</strong> <span class="song-title-text"><?= htmlspecialchars($rec['song_title']) ?></span></p>
                    </div>
                    <?php 
                        $embedId = getYouTubeEmbedUrl($rec['song_url']);
                        if ($embedId):
                    ?>
                    <div class="youtube-embed" data-url="<?= htmlspecialchars($rec['song_url']) ?>">
                        <div id="player-<?= $index ?>" data-videoid="<?= $embedId ?>"></div>
                        <div id="player-error-container-<?= $index ?>" class="player-error-container"></div>
                    </div>
                    <?php else: ?>
                    <p style="color: var(--glow-color-error);">無效的 YouTube 網址</p>
                    <?php endif; ?>
                    
                    <div class="card-actions">
                        <button class="btn-edit">編輯</button>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <div class="button-group">
        <a href="index.php?page=form" class="btn-glow">返回推薦</a>

        <a href="index.php?page=export" class="btn-glow">匯出腳本</a>
                <a href="index.php?page=dashboard" class="btn-glow">返回首頁</a>
        <?php if (!empty($user_recs)): ?>
        <?php endif; ?>
    </div>
</div>

<div id="edit-modal-backdrop" class="modal-backdrop">
    <div id="edit-modal-content" class="glass-card modal-content">
        <div class="card-header">
            <h2>編輯推薦歌曲</h2>
        </div>
        <form id="editForm" action="api.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit-song-id" name="id">
            <div class="form-group">
                <label for="edit-singer" class="form-label">歌手名稱</label>
                <input type="text" id="edit-singer" name="singer" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit-song-title" class="form-label">歌曲名稱</label>
                <input type="text" id="edit-song-title" name="song_title" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="edit-song-url" class="form-label">YouTube 歌曲網址</label>
                <input type="url" id="edit-song-url" name="song_url" class="form-input" required>
            </div>
            <div id="edit-status" class="status-message"></div>
            <div class="button-group modal-footer">
                <button type="button" id="cancel-edit-btn" class="btn-secondary">取消</button>
                <button type="submit" class="btn-glow">儲存變更</button>
            </div>
        </form>
    </div>
</div>