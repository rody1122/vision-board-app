<?php
session_start();
require_once 'functions.php';
login_check();

$db = getDb();
$note = null;
$board_images = [];
$err_msg = '';

// 一覧画面等から送られてくるノートIDを取得
$note_id = $_POST['id'] ?? $_GET['id'] ?? '';

if (empty($note_id)) {
    $err_msg = 'ボードIDが指定されていません。';
}

try {
    if (!empty($note_id)) {
        // 1. ボード（ノート）のタイトルやメモなどの文字情報を取得
        $note_sql = 'SELECT * FROM notes WHERE id = ? AND user_id = ?';
        $note_stmt = $db->prepare($note_sql);
        $note_stmt->execute([$note_id, $_SESSION['id']]);
        $note = $note_stmt->fetch();

        if (!$note) {
            $err_msg = '指定されたボードが見つかりません。';
        } else {
            
            $sql = 'SELECT * FROM board_images WHERE note_id = ? AND user_id = ? ORDER BY z_index ASC, id ASC';
            $stmt = $db->prepare($sql);
            $stmt->execute([$note_id, $_SESSION['id']]);
            $board_images = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    die('データ取得エラー: ' . $e->getMessage());
}

// user name for the sidebar
$username = 'ゲスト';
$user_stt = $db->prepare('SELECT user_name FROM users WHERE id = ?');
$user_stt->execute([$_SESSION['id']]);
$user_data = $user_stt->fetch();
if ($user_data) { $username = $user_data['user_name']; }
$userName = e($username);

// saved background, white when nothing was picked or column doesnt exist
$boardBg = $note['bg_style'] ?? '';
if ($boardBg === '' || $boardBg === null) { $boardBg = '#ffffff'; }
?>


<!DOCTYPE html>
<html lang="ja" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ボード確認 | View</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/favicon.png">

    <link rel="stylesheet" href="../css/member.css">
    <link rel="stylesheet" href="../css/view.css">
</head>

<body>
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

        <a href="list.php" class="nav-item">
            <span class="nav-icon">+</span>
            <span>画像倉庫</span>
        </a>

        <a href="library.php" class="nav-item active">
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
            <h1>ボード確認</h1>
            <p>完成したボードをそのまま眺めるページです</p>
        </div>

        <div class="topbar-right">
            <img src="../images/tarot.png" style="width: 50px;" alt="switchbutton">
            <span class="theme-toggle-label" id="theme-label">+</span>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
            <a href="#" class="avatar-btn">
                <?= mb_substr($userName, 0, 2) ?>
            </a>
        </div>
    </div>


    <?php if (!empty($err_msg)): ?>

        <div class="alert alert-error"><?php echo e($err_msg); ?></div>

    <?php elseif ($note): ?>

        <!-- title and memo part -->
        <div class="view-info">
            <h2 class="view-title"><?php echo e($note['title'] ?: '無題のボード'); ?></h2>
            <p class="view-date">更新日: <?php echo e($note['updated_at']); ?></p>
            <?php if (!empty($note['contents'])): ?>
                <div class="view-memo"><?php echo e($note['contents']); ?></div>
            <?php endif; ?>
        </div>


        <div class="view-frame" id="viewFrame">
            <div class="view-canvas" id="viewCanvas" style="background: <?php echo e($boardBg); ?>;">

                <?php foreach ($board_images as $img): ?>
                    <?php if (!empty($img['img_path'])): ?>
                        <img src="<?php echo e($img['img_path']); ?>" 
                             alt="ボードパーツ" 
                             style="
                                position: absolute;
                                left: <?php echo e((float)$img['x']); ?>px;
                                top: <?php echo e((float)$img['y']); ?>px;
                                width: <?php echo e((float)($img['width'] ?: 200)); ?>px;
                                height: <?php echo e((float)($img['height'] ?: 200)); ?>px;
                                transform: rotate(<?php echo e((float)$img['angle'] ?? 0); ?>deg);
                                z-index: <?php echo e((int)($img['z_index'] ?? 1)); ?>;
                                object-fit: fill;
                                pointer-events: none;
                             ">
                    <?php endif; ?>
                <?php endforeach; ?>

            </div>
        </div>


        <div class="view-links">
            <button type="button" id="downloadBtn" class="btn-neu btn-primary">画像として保存</button>
            <a href="edit.php?id=<?php echo e($note_id); ?>" class="btn-neu btn-tool">このボードを編集する</a>
            <a href="library.php" class="btn-neu btn-tool">ライブラリへ戻る</a>
            <a href="member.php" class="btn-neu btn-tool">マイページトップへ</a>
        </div>

    <?php endif; ?>

</main>

<script src="../js/member.js"></script>
<!-- self-hosted (no CDN) so the save-as-image button keeps working on lolipop
     even if outside scripts are blocked -->
<script src="../js/html2canvas.min.js"></script>
<script src="../js/view.js"></script>

</body>
</html>
