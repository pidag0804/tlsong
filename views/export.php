<?php
$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT singer, song_title, song_url FROM user_recommendations ORDER BY user_id, created_at");
$stmt->execute();
$all_recs = $stmt->fetchAll();
?>
<div class="glass-card">
    <div class="card-header">
        <h1>所有玩家推薦腳本</h1>
    </div>
    
    <div class="reminder-card">
        <p>【請將腳本內容複製傳送到Line客服 : @820mdvjx】</p>
    </div>

    <div class="export-box" id="export-content">
<?php
$export_text = "";
foreach ($all_recs as $rec) {
    $export_text .= "歌手: " . htmlspecialchars($rec['singer']) . "\n";
    $export_text .= "歌名: " . htmlspecialchars($rec['song_title']) . "\n";
    $export_text .= "網址: " . htmlspecialchars($rec['song_url']) . "\n\n";
}
echo trim($export_text);
?>
    </div>
    
    <div class="button-group">
         <button id="copy-btn" onclick="copyExportScript()" class="btn-glow">複製內容</button>
         <a href="index.php?page=form" class="btn-glow">返回推薦</a>
         <a href="index.php?page=preview" class="btn-glow">返回清單</a>
         <a href="index.php?page=dashboard" class="btn-glow">返回首頁</a>
    </div>
</div>