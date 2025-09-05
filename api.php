<?php
// 隱藏非致命錯誤，確保 API 輸出乾淨
error_reporting(0);

header('Content-Type: application/json');
require_once 'config.php';
require_once 'functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = session_id();
}

// --- 輔助函式 ---

// 簡繁轉換函式
function convertToTraditional($text) {
    // 使用 shell_exec 呼叫系統已安裝的 opencc
    // s2t.json 是 "Simplified to Traditional" 的標準設定檔
    return trim(shell_exec('echo ' . escapeshellarg($text) . ' | opencc -c s2t.json'));
}

// YouTube 網址驗證函式
function isValidYouTubeUrl($url) {
    $url_parts = parse_url($url);
    if (empty($url_parts['host'])) {
        return false;
    }
    $host = strtolower($url_parts['host']);
    // str_ends_with handles www.youtube.com, m.youtube.com, music.youtube.com etc.
    return str_ends_with($host, 'youtube.com') || $host === 'youtu.be';
}


$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => '無效的操作', 'field' => 'general'];


// --- 推薦新歌曲的邏輯 ---
if ($action === 'recommend') {
    try {
        $pdo = getDbConnection();
        $singers = $_POST['singer'] ?? [];
        $song_titles = $_POST['song_title'] ?? [];
        $song_urls = $_POST['song_url'] ?? [];

        if (empty($singers)) throw new Exception('請至少推薦一首歌曲。');
        
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM user_recommendations WHERE user_id = ?");
        $stmt_count->execute([$user_id]);
        if ($stmt_count->fetchColumn() + count($singers) > MAX_RECOMMENDATIONS) {
            throw new Exception('提交後將超過 ' . MAX_RECOMMENDATIONS . ' 首歌曲的推薦上限！');
        }

        $pdo->beginTransaction();
        
        $unsupported_scripts_regex = '/[\p{Hiragana}\p{Katakana}\p{Hangul}\p{Cyrillic}\x{0100}-\x{024F}\x{1E00}-\x{1EFF}]/u';
        $stmt_symbol_check = '/[\'\"\;--]/';
        $stmt_check_game = $pdo->prepare("SELECT id FROM songs WHERE singer = ? AND song_title = ?");
        $stmt_check_recs = $pdo->prepare("SELECT id FROM user_recommendations WHERE (singer = ? AND song_title = ?) OR song_url = ?");
        $stmt_insert = $pdo->prepare("INSERT INTO user_recommendations (user_id, singer, song_title, song_url) VALUES (?, ?, ?, ?)");

        foreach ($singers as $index => $singer_raw) {
            $song_title_raw = trim($song_titles[$index] ?? '');
            $song_url = trim($song_urls[$index] ?? '');
            $singer_raw = trim($singer_raw);

            if (empty($singer_raw) || empty($song_title_raw) || empty($song_url)) continue;

            if (!isValidYouTubeUrl($song_url)) {
                 throw new Exception("第 ".($index+1)." 首歌請輸入有效的 YouTube 或 YouTube Music 網址。");
            }

            $singer = convertToTraditional($singer_raw);
            $song_title = convertToTraditional($song_title_raw);

            $error_message = "第 ".($index+1)." 首歌的輸入只接受繁體中文或英文。";

            if (preg_match($stmt_symbol_check, $singer) || preg_match($stmt_symbol_check, $song_title)) throw new Exception("第 ".($index+1)." 首歌的輸入內容包含不允許的符號。");
            if (preg_match($unsupported_scripts_regex, $singer)) throw new Exception($error_message);
            if (preg_match($unsupported_scripts_regex, $song_title)) throw new Exception($error_message);

            $stmt_check_game->execute([$singer, $song_title]);
            if ($stmt_check_game->fetch()) throw new Exception("歌曲 “{$singer} - {$song_title}” 已經在遊戲歌單上了。");
            $stmt_check_recs->execute([$singer, $song_title, $song_url]);
            if ($stmt_check_recs->fetch()) throw new Exception("歌曲 “{$singer} - {$song_title}” 已有其他玩家推薦了。");
            
            $stmt_insert->execute([$user_id, $singer, $song_title, $song_url]);
        }
        
        $pdo->commit();
        $response = ['status' => 'success', 'message' => '所有歌曲推薦成功！'];
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        $response['message'] = $e->getMessage();
    }
}

// --- 編輯推薦歌曲的邏輯 ---
if ($action === 'edit') {
    try {
        $pdo = getDbConnection();
        
        $id = $_POST['id'] ?? 0;
        $singer_raw = trim($_POST['singer'] ?? '');
        $song_title_raw = trim($_POST['song_title'] ?? '');
        $song_url = trim($_POST['song_url'] ?? '');

        if (empty($id) || empty($singer_raw) || empty($song_title_raw) || empty($song_url)) {
            throw new Exception('所有欄位皆為必填。');
        }
        
        if (!isValidYouTubeUrl($song_url)) {
             throw new Exception("請輸入有效的 YouTube 或 YouTube Music 網址。");
        }

        $stmt_owner = $pdo->prepare("SELECT user_id FROM user_recommendations WHERE id = ?");
        $stmt_owner->execute([$id]);
        if ($stmt_owner->fetchColumn() !== $user_id) {
            throw new Exception('權限不足，您無法編輯此項目。');
        }

        $singer = convertToTraditional($singer_raw);
        $song_title = convertToTraditional($song_title_raw);

        $unsupported_scripts_regex = '/[\p{Hiragana}\p{Katakana}\p{Hangul}\p{Cyrillic}\x{0100}-\x{024F}\x{1E00}-\x{1EFF}]/u';
        $error_message = "推薦歌曲只接受繁體中文或英文。";

        if (preg_match('/[\'\"\;--]/', $singer) || preg_match('/[\'\"\;--]/', $song_title)) {
            throw new Exception('輸入內容包含不允許的符號。');
        }
        if (preg_match($unsupported_scripts_regex, $singer)) throw new Exception($error_message);
        if (preg_match($unsupported_scripts_regex, $song_title)) throw new Exception($error_message);

        $stmt_check_game = $pdo->prepare("SELECT id FROM songs WHERE singer = ? AND song_title = ?");
        $stmt_check_game->execute([$singer, $song_title]);
        if ($stmt_check_game->fetch()) {
            throw new Exception('該歌曲已經在遊戲歌單上了。');
        }
        
        $stmt_check_recs = $pdo->prepare("SELECT id FROM user_recommendations WHERE ((singer = ? AND song_title = ?) OR song_url = ?) AND id != ?");
        $stmt_check_recs->execute([$singer, $song_title, $song_url, $id]);
        if ($stmt_check_recs->fetch()) {
            throw new Exception('此歌曲資訊與其他已推薦的項目重複。');
        }
        
        $stmt_update = $pdo->prepare("UPDATE user_recommendations SET singer = ?, song_title = ?, song_url = ? WHERE id = ? AND user_id = ?");
        $stmt_update->execute([$singer, $song_title, $song_url, $id, $user_id]);

        $response = ['status' => 'success', 'message' => '更新成功！'];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

// --- 模糊搜索歌曲的邏輯 ---
if ($action === 'search') {
    try {
        $pdo = getDbConnection();
        $query = trim($_POST['query'] ?? '');

        if (strlen($query) < 1) {
            throw new Exception('查詢關鍵字過短。');
        }

        // 使用 LIKE 進行模糊搜索
        $search_term = '%' . $query . '%';
        $stmt = $pdo->prepare(
            "SELECT singer, song_title FROM songs 
             WHERE singer LIKE ? OR song_title LIKE ? 
             ORDER BY singer, song_title
             LIMIT 50"
        );
        $stmt->execute([$search_term, $search_term]);
        $results = $stmt->fetchAll();

        $response = ['status' => 'success', 'results' => $results];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}


echo json_encode($response);