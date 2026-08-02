<?php
session_start();

require_once 'functions.php';

login_check();

$username = 'ゲスト';
$db = getDb();
$err_msg = '';

// メッセージの取得と消去
$success_msg = $_SESSION['success_msg'] ?? '';
$session_err_msg = $_SESSION['err_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['err_msg']);

// ユーザー名取得
try {
    $user_sql = 'SELECT user_name FROM users WHERE id = ?';
    $user_stt = $db->prepare($user_sql);
    $user_stt->execute([$_SESSION['id']]);
    $user_data = $user_stt->fetch();
    if ($user_data) {
        $username = $user_data['user_name'];
    }
} catch (PDOException $e) {
    die('データ取得エラー: ' . $e->getMessage());
}

// ボード一覧の取得
try {
    $sql = 'SELECT n.*, 
                   GREATEST(n.updated_at, COALESCE(MAX(bi.updated_at), n.updated_at)) AS last_touched
            FROM notes n
            LEFT JOIN board_images bi ON n.id = bi.note_id
            WHERE n.user_id = ? 
            GROUP BY n.id
            ORDER BY last_touched DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute([$_SESSION['id']]);
    $completed_boards = $stmt->fetchAll();

    $images_by_note = [];
    if (!empty($completed_boards)) {
        // 現在一覧にあるボードのIDだけを集める
        $note_ids = array_column($completed_boards, 'id');
        // SQLの IN (?, ?, ...) のプレースホルダーを作成
        $in_clause = implode(',', array_fill(0, count($note_ids), '?'));

        $img_sql = "SELECT id, note_id, img_path, x, y, width, height, angle, z_index 
                    FROM board_images 
                    WHERE note_id IN ($in_clause)
                    ORDER BY z_index ASC";
        $img_stmt = $db->prepare($img_sql);
        $img_stmt->execute($note_ids);
        $all_images = $img_stmt->fetchAll();

        // ループ内で扱いやすいように [ボードID => [画像パーツの配列]] の形に仕分ける
        foreach ($all_images as $img) {
            $images_by_note[$img['note_id']][] = $img;
        }
    }
} catch (PDOException $e) {
    die('データ取得エラー: ' . $e->getMessage());
}

$userName = e($username);
?>


<!DOCTYPE html>
<html lang="ja" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ライブラリ | Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/favicon.png">

    <link rel="stylesheet" href="../css/member.css">
    <link rel="stylesheet" href="../css/library.css">
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
    <!-- side bar ends heree -->

    <main class="main">

        <div class="topbar">
            <div class="topbar-left">
                <h1>ライブラリ</h1>
                <p>これまでに作成した完成品ボードの一覧です</p>
            </div>

            <div class="topbar-right">
                <img src="../images/tarot.png" style="width: 50px; border:0; " alt="">
                <span class="theme-toggle-label" id="theme-label"></span>
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
                <a href="#" class="avatar-btn">
                    <?= mb_substr($userName, 0, 2) ?>
                </a>
            </div>
        </div>

        <!-- メッセージ表示エリア -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?= e($success_msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($session_err_msg)): ?>
            <div class="alert alert-error"><?= e($session_err_msg) ?></div>
        <?php endif; ?>


        <?php if (empty($completed_boards)): ?>

            <div class="empty-state">
                <div class="empty-state-icon">
                    <img src="../images/sad.png" style="width: 220px;" alt="sad">
                </div>
                <div class="empty-state-text">まだ保存されたボードはありません</div>
                <div class="empty-state-sub"><a href="list.php" class="section-link">画像倉庫</a>から新しいボードを作成してみましょう！</div>
            </div>

        <?php else: ?>

            <label class="select-actions">
                <input type="checkbox" id="select-all-boards">
                <span class="select-all-mark">✓</span>
                <span class="select-all-text">すべて選択</span>
            </label>

            <form id="delete-bulk-form" action="delete_note.php" method="POST">
                <div class="list-actions">
                    <button type="button" class="btn-neu btn-danger" onclick="deleteSelectedBoards()">
                        選択したボードをまとめて削除する
                    </button>
                </div>
            </form>

            <div class="library-grid">
                <?php foreach ($completed_boards as $board): ?>
                    <div class="board-card">

                        <label class="delete-checkbox-label">
                            <input type="checkbox" name="note_ids[]" value="<?php echo e($board['id']); ?>" class="board-delete-checkbox">
                            <span class="board-check-mark">✓</span>
                        </label>

                        <?php
                        // このボードに紐づく画像たちを取得。無ければ空配列
                        $board_images = $images_by_note[$board['id']] ?? [];



                        // saved background, plain white when nothing was picked, considering to changefor snow
                        $card_bg = $board['bg_style'] ?? '';
                        if ($card_bg === '' || $card_bg === null) {
                            $card_bg = '#ffffff';
                        }
                        ?>

                        <?php if (!empty($board_images)): ?>
                            <div class="board-thumb-wrapper">

                                <div class="mini-board-canvas" style="background: <?php echo e($card_bg); ?>;">
                                    <?php foreach ($board_images as $img): ?>
                                        <?php if (!empty($img['img_path'])): ?>
                                            <img src="<?php echo e($img['img_path']); ?>"
                                                alt="パーツ"
                                                style="
                                                position: absolute;
                                                left: <?php echo e((float)$img['x']); ?>px;
                                                top: <?php echo e((float)$img['y']); ?>px;
                                                width: <?php echo e((float)($img['width'] ?: 200)); ?>px;
                                                height: <?php echo e((float)($img['height'] ?: 200)); ?>px;
                                                transform: rotate(<?php echo e((float)$img['angle'] ?? 0); ?>deg);
                                                z-index: <?php echo e((int)($img['z_index'] ?? 1)); ?>;
                                                object-fit: fill;
                                             ">
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="no-image">※配置された画像がありません</div>
                        <?php endif; ?>

                        <h3 class="board-title"><?php echo e($board['title']); ?></h3>
                        <p class="board-date">
                            更新日: <?php echo e(date('Y-m-d H:i', strtotime($board['last_touched']))); ?>
                        </p>
                        <p class="board-memo"><?php echo e($board['contents']); ?></p>

                        <div class="board-card-actions">
                            <form action="edit.php" method="POST" class="inline-form">
                                <input type="hidden" name="id" value="<?php echo e($board['id']); ?>">
                                <button type="submit" class="btn-neu btn-tool">再編集</button>
                            </form>

                            <form action="view.php" method="POST" class="inline-form">
                                <input type="hidden" name="id" value="<?php echo e($board['id']); ?>">
                                <button type="submit" class="btn-neu btn-primary">大きく見る</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <script src="../js/member.js"></script>
    <script src="../js/library.js"></script>

</body>

</html>