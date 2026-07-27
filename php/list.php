<?php
session_start();
require_once 'functions.php';
login_check();

$username = 'ゲスト';
$db = getDb();

// メッセージの取得と消去
$success_msg = $_SESSION['success_msg'] ?? '';
$err_msg = $_SESSION['err_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['err_msg']);

// URLパラメータから現在のノートIDを取得
$note_id = $_GET['id'] ?? '';

// URLパラメータから保存前のタイトル・メモを取得
$title = $_GET['title'] ?? '';
$contents = $_GET['contents'] ?? '';

$clean_note_id = $_GET['clean_id'] ?? '';

if (!empty($clean_note_id) && !empty($_SESSION['id'])) {
    try {
        // 1. 削除する前に、該当ボードに使われている画像の「ファイルパス（img_path）」を取得
        $select_img_sql = 'SELECT img_path 
                            FROM board_images 
                            WHERE note_id = ? 
                            AND user_id = ?';
        $select_img_stt = $db->prepare($select_img_sql);
        $select_img_stt->execute([$clean_note_id, $_SESSION['id']]);
        $used_images = $select_img_stt->fetchAll();

        if (!empty($used_images)) {
            // 2. サーバー（XAMPP内）の実ファイルを1つずつ物理削除
            foreach ($used_images as $img) {
                if (!empty($img['img_path']) && file_exists($img['img_path'])) {
                    unlink($img['img_path']);
                }
            }

            // 3. データベース（board_images）からレコードを完全削除
            $delete_img_sql = 'DELETE 
                                FROM board_images 
                                WHERE note_id = ? 
                                AND user_id = ?';
            $delete_img_stt = $db->prepare($delete_img_sql);
            $delete_img_stt->execute([$clean_note_id, $_SESSION['id']]);

            $success_msg = 'ボードに保存されていたパーツ画像をすべて完全削除しました。';
        }
    } catch (PDOException $e) {
        die('パーツ画像の一括完全削除に失敗しました: ' . $e->getMessage());
    }
}


try {
    $user_sql = 'SELECT user_name 
                FROM users 
                WHERE id = ?';
    $stt = $db->prepare($user_sql);
    $stt->execute([$_SESSION['id']]);
    $user_data = $stt->fetch();
    if ($user_data) { $username = $user_data['user_name']; }

    // 画像倉庫から「自分のパーツ」かつ「未使用（note_id IS NULL）」のものを取得
    $sql = 'SELECT * 
            FROM board_images 
            WHERE user_id = ? 
            AND note_id IS NULL 
            AND is_temp = 0 
            AND type = "image"
            ORDER BY id DESC';

    $stt = $db->prepare($sql);
    $stt->execute([$_SESSION['id']]);
    $parts = $stt->fetchAll();

} catch (PDOException $e) {
    die('データ取得エラー: ' . $e->getMessage());
}

// escaped name once, used in a few spots below
$userName = e($username);
?>


<!DOCTYPE html>
<html lang="ja" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>画像倉庫 | Warehouse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/favicon.png">

    <link rel="stylesheet" href="../css/member.css">
    <link rel="stylesheet" href="../css/list.css">
</head>

<body>

<!-- side option part, same as the other pages -->
<aside class="sidebar">
    <a href="index.php" class="sidebar-logo">M<span><sup>3</sup></span></a>

    <nav class="sidebar-nav">

        <a href="member.php" class="nav-item">
            <span class="nav-icon">+</span>
            <span>ダッシュボード</span>
        </a>

        <a href="edit.php" class="nav-item">
                <span class="nav-icon">+</span>
                <span>ボード作成・編集</span>
        </a>

        <a href="upload.php" class="nav-item">
            <span class="nav-icon">+</span>
            <span>写真をアップする</span>
        </a>

        <a href="list.php" class="nav-item active">
            <span class="nav-icon">+</span>
            <span>画像倉庫</span>
        </a>

        <a href="library.php" class="nav-item">
            <span class="nav-icon">+</span>
            <span>ライブラリ</span>
        </a>
    </nav>

    <div class="sidebar-divider"></div>

    <a href="./logout.php" class="logout-btn">
        <span>
            <img src="../icons/logout.png" style="width: 35px;" alt="logout">
        </span>
        <span>ログアウト</span>
    </a>

    <div class="sidebar-divider"></div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= mb_substr($userName, 0, 2) ?></div>

        <div class="user-info">
            <div class="user-name"><?= $userName ?></div>
            <div class="user-role">ユーザ</div>
        </div>
    </div>
</aside>


<main class="main">

    <div class="topbar">
        <div class="topbar-left">
            <h1>画像倉庫</h1>
            <p>アップロード済みのパーツを選んで、ボードをつくりましょう</p>
        </div>

        <div class="topbar-right">
            <img src="../images/tarot.png" style="width: 50px; border:0;" alt="">
            <span class="theme-toggle-label" id="theme-label"></span>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
            <a href="#" class="avatar-btn">
                <?= mb_substr($userName, 0, 2) ?>
            </a>
        </div>
    </div>

    <!-- the girls' session messages, in our alert style -->
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?= e($success_msg) ?></div>
    <?php endif; ?>
    <?php if (!empty($err_msg)): ?>
        <div class="alert alert-error"><?= e($err_msg) ?></div>
    <?php endif; ?>


    <!-- 送信メソッドを POST に統一 -->
    <form id="parts-form" action="edit.php" method="POST">

        <!-- ノートIDとモードを次の画面（edit.php）へ確実にPOST引き渡し -->
        <input type="hidden" name="id" value="<?= e($note_id) ?>">
        <input type="hidden" name="mode" value="new">

        <!-- 保存前のタイトル・メモをedit.phpへ引き渡し -->
        <input type="hidden" name="title" value="<?= e($title) ?>">
        <input type="hidden" name="contents" value="<?= e($contents) ?>">

        <!-- action bar, sticks around while you scroll the parts -->
        <div class="list-actions">
            <button type="button" class="btn-neu btn-primary" onclick="goToEditPage()">
                選択したパーツで編集を始める →
            </button>

            <button type="button" class="btn-neu btn-danger" onclick="deleteSelectedParts()">
                選択したパーツを削除する
            </button>
        </div>


        <?php if (empty($parts)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <img src="../images/sad.png" style="width: 220px;" alt="sad">
                </div>
                <div class="empty-state-text">倉庫が空っぽです</div>
                <div class="empty-state-sub"><a href="upload.php" class="section-link">写真をアップして</a>パーツを追加しましょう</div>
            </div>
        <?php else: ?>

            <div class="parts-grid">
                <?php $rotations = [-2, 1.5, -1, 2, -1.5, 0.5]; ?>
                <?php foreach ($parts as $i => $part): ?>

                    <!-- the whole polaroid is the label, so tapping anywhere selects it -->
                    <label class="part-card" style="--rot: <?= $rotations[$i % count($rotations)] ?>deg">

                        <input type="checkbox"
                               name="image_ids[]"
                               value="<?= e($part['id']) ?>"
                               class="part-check">
                        <span class="part-check-mark">&#10003;</span>

                        <?php if (!empty($part['img_path'])): ?>
                            <img src="<?= e($part['img_path']) ?>" alt="パーツ画像" class="part-img">
                        <?php endif; ?>

                        <div class="part-caption">
                            <?php
                            // the girls strip the uniqid prefix so the real filename shows
                            $clean_name = preg_replace('/^[a-zA-Z0-9]+_/', '', basename($part['img_path']));
                            echo e($clean_name);
                            ?>
                        </div>

                        <div class="part-date">
                            <?= (!empty($part['created_at']) && $part['created_at'] !== '0000-00-00 00:00:00') ? e(date('Y-m-d H:i', strtotime($part['created_at']))) : '不明'; ?>
                        </div>

                    </label>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </form>

</main>

<script src="../js/member.js"></script>
<script src="../js/list.js"></script>

</body>
</html>
