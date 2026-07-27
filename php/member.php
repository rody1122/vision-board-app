<?php

session_start();

require_once 'functions.php';

login_check();

$db = getDb();
$stt = $db->prepare('SELECT user_name FROM users WHERE id = :id');
$stt->bindValue(':id', $_SESSION['id']);
$stt->execute();

$user = $stt->fetch(PDO::FETCH_ASSOC);
$userName = $user ? e($user['user_name']) : 'User';

// total boards this user made
$stt2 = $db->prepare('SELECT COUNT(*) as total FROM notes WHERE user_id = :uid');
$stt2->bindValue(':uid', $_SESSION['id']);
$stt2->execute();
$stats = $stt2->fetch(PDO::FETCH_ASSOC);
$totalNotes = $stats['total'] ?? 0;

// parts sitting in the warehouse, not on any board yet
$stt3 = $db->prepare('SELECT COUNT(*) as parts FROM board_images WHERE user_id = :uid AND note_id IS NULL AND is_temp = 0');
$stt3->bindValue(':uid', $_SESSION['id']);
$stt3->execute();
$parts_row = $stt3->fetch(PDO::FETCH_ASSOC);
$totalParts = $parts_row['parts'] ?? 0;

// greeting depending on tokyo time, set in functions.php
$h = (int)date('H');

if ($h >= 5 && $h < 12) {
    $greeting = 'おはようございます';
} elseif ($h >= 12 && $h < 18) {
    $greeting = 'こんにちは';
} else {
    $greeting = 'こんばんは';
}
?>
<!DOCTYPE html>
<html lang="ja" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ダッシュボード | Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/favicon.png">
    
    <a href="https://lordicon.com/"></a>
    <link rel="stylesheet" href="../css/member.css">
    <!-- reuse the library mini-board preview styles for the recent boards -->
    <link rel="stylesheet" href="../css/library.css">
</head>

<body>
  <!-- side option part -->
<aside class="sidebar">
    <a href="index.php" class="sidebar-logo">M<span><sup>3</sup></span></a>

     <nav class="sidebar-nav">

        <a href="member.php" class="nav-item active">
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

        <a href="library.php" class="nav-item">
            <span class="nav-icon">+</span>
            <span>ライブラリ</span>
        </a>
    </nav>


     <div class="sidebar-divider"></div>

    <a href="logout.php" class="logout-btn">
        <span>
            <img src="../icons/logout.png" style="width: 35px;" alt="logout">
        </span>
             <span>ログアウト</span>
    </a>

    <div class="sidebar-divider"></div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= mb_substr($userName, 0, 2) ?>
    </div>

    <div class="user-info">
            <div class="user-name"><?= $userName ?></div>
            <div class="user-role">ユーザ</div>
        </div>
    </div>
</aside>




<!-- from here starts the main part -->
<main class="main">

<!-- 
top section from here, specially the menu and user part (pics and user name) -->
     <div class="topbar">
 
     <div class="topbar-left">
            <h1><?= $greeting ?>, <?= $userName ?>!</h1>

            <p>「鏡は自惚れの醸造器である如く、同時に自慢の消毒器である。」
                <br>
                - 夏目・漱石 -</p>
        </div>


        <div class="topbar-right">
            <img src="../images/tarot.png" style="width: 50px; border:0;" alt="sun">
            <span class="theme-toggle-label" id="theme-label"></span>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
            <a href="#" class="avatar-btn">
                <?= mb_substr($userName, 0, 2) ?>
            </a>
        </div>
    </div>


    <!-- counting and general information about the user acc -->
    <div class="stats-row">
        <div class="stat-card">
            
            <div class="stat-label">ボードの数</div>
            <div class="stat-value">
                <?= $totalNotes ?><span>枚</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">最後に保存した日</div>

            <div class="stat-value" style="font-size:22px;" >

                 <?php
                $stt4 = $db->prepare('SELECT created_at FROM notes WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1');

                $stt4->bindValue(':uid', $_SESSION['id']);

                  $stt4->execute();

                $last = $stt4->fetch(PDO::FETCH_ASSOC);
                echo $last ? date('m/d', strtotime($last['created_at'])) : '-';
                ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">倉庫のパーツ</div>
            <div class="stat-value">
                <?= $totalParts ?>
                <span>枚</span>
            </div>
        </div>
    </div>

    <!-- actions starts from here  -->
    <div class="actions-row">
        <a href="upload.php" class="action-card">
            <div class="action-icon">
                <img src="../icons/add.gif" style="width:45px;" alt="upload">
            </div>
            <div class="action-title">写真をアップする</div>

            <p class="action-desc">新しい写真を倉庫に追加する</p>
            <div class="action-arrow">→</div>
        </a>

        <a href="list.php" class="action-card">
            <div class="action-icon">
                <img src="../icons/edit.gif" style="width: 45px;" alt="edit">
            </div>
            <div class="action-title">画像倉庫</div>
            <p class="action-desc">パーツを選んでボードをつくりましょう</p>
            <div class="action-arrow">→</div>
        </a>

        <a href="library.php" class="action-card">
            <div class="action-icon">
                <img src="../icons/library.gif" style="width: 45px;" alt="library">
            </div>
            <div class="action-title">ライブラリ</div>
            <p class="action-desc">完成したボードの一覧を見る</p>
            <div class="action-arrow">→</div>
        </a>
    </div>


    <!-- newest boards that were saved recently -->
    <div class="section-header">
        <div class="section-title">最近つくったボード</div>
    </div>


    <div class="cards-grid">

    <!-- to start a new board button -->
    <a href="list.php" class="add-card">
            <div class="add-card-icon">
                <img src="../icons/addpooh.gif" style="width: 45px;" alt="add">
            </div>
            <div class="add-card-text">ボードをつくる</div>
        </a>


        <?php

// most recent boards, plus how many parts each one has
        $stt5 = $db->prepare(
            'SELECT n.*,
                (SELECT COUNT(*) FROM board_images bi2
                 WHERE bi2.note_id = n.id) AS parts_count
                FROM notes n
                WHERE n.user_id = :uid
                ORDER BY n.created_at DESC
                LIMIT 7'
        );
        $stt5->bindValue(':uid', $_SESSION['id']);
        $stt5->execute();

        $notes = $stt5->fetchAll(PDO::FETCH_ASSOC);

        // grab every placed image for these boards so each card can show the
        // full mini-board preview (same as library.php), not just one thumb
        $images_by_note = [];
        if (!empty($notes)) {
            $note_ids = array_column($notes, 'id');
            $in_clause = implode(',', array_fill(0, count($note_ids), '?'));
            $img_stmt = $db->prepare(
                "SELECT note_id, img_path, x, y, width, height, angle, z_index
                 FROM board_images
                 WHERE note_id IN ($in_clause)
                 ORDER BY z_index ASC"
            );
            $img_stmt->execute($note_ids);
            foreach ($img_stmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
                $images_by_note[$img['note_id']][] = $img;
            }
        }


        // from here just showing empty baord with emoji in it
        if (empty($notes)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <img src="../images/sad.png" style="width: 100px;" alt="sad">
                </div>
                <div class="empty-state-text">ボードが空っぽです</div>
                <div class="empty-state-sub">新しいボードをつくりましょう</div>
            </div>

 <!-- each recent board: full mini preview like the library, whole card
             links to edit, no action buttons -->
        <?php else:
              foreach ($notes as $note):
                 $imgs = $images_by_note[$note['id']] ?? [];

                 $card_bg = $note['bg_style'] ?? '';
                 if ($card_bg === '' || $card_bg === null) { $card_bg = '#ffffff'; }
        ?>

             <a href="edit.php?id=<?= $note['id'] ?>" class="recent-card">

                <?php if (!empty($imgs)): ?>
                    <div class="board-thumb-wrapper">
                        <div class="mini-board-canvas" style="background: <?= e($card_bg) ?>;">
                            <?php foreach ($imgs as $img): ?>
                                <?php if (!empty($img['img_path'])): ?>
                                    <img src="<?= e($img['img_path']) ?>"
                                         alt="パーツ"
                                         style="
                                            
                                            position: absolute;
                                            left: <?= e((float)$img['x']) ?>px;
                                            top: <?= e((float)$img['y']) ?>px;
                                            width: <?= e((float)($img['width'] ?: 200)) ?>px;
                                            height: <?= e((float)($img['height'] ?: 200)) ?>px;
                                            transform: rotate(<?= e((float)($img['angle'] ?? 0)) ?>deg);
                                            z-index: <?= e((int)($img['z_index'] ?? 1)) ?>;
                                            
                                        object-fit: fill;

                                         ">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-image">※配置された画像がありません</div>
                <?php endif; ?>

                <div class="polaroid-caption">
                    <?= e($note['title'] ?: 'Untitled') ?>
                </div>
                <!-- <div style="text-align:center">
                    <span class="category-tag"><?= (int)$note['parts_count'] ?> パーツ</span>
                </div> -->
            </a>

        <?php endforeach; endif; ?>

    </div>
</main>


<script src="../js/member.js"></script>
<script src="../js/library.js"></script>
</body>
</html>




