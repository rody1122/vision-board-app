<?php
session_start();
require_once 'functions.php';

login_check();

// might delete it later or just add an option for guest? but might increase database area
$username = 'ゲスト';

try {
    $db = getDb();

    $default_x = 0;
    $default_y = 0;
    $default_width = 200;
    $default_height = 200;
    $default_angle = 0;
    $default_z_index = 1;
    // ログインユーザー名を取得
    $stt = $db->prepare('SELECT user_name FROM users WHERE id = :id');

    $stt->bindValue(':id', $_SESSION['id'], PDO::PARAM_INT);
    $stt->execute();

    $user_data = $stt->fetch();
    if($user_data) {
        $username = $user_data['user_name'];
    }

    // ボードIDを取得（GET: 表示、POST: 更新）
    $note_id = $_POST['id'] ?? $_GET['id'] ?? '';

    // 編集をキャンセルした画像をライブラリへ戻す
    if (!empty($_POST['cancel_edit']) || !empty($_POST['from_library'])) {
    $stt = $db->prepare(
        'UPDATE board_images
    SET
        note_id = NULL, 
        is_temp = 0,
        x = :x, 
        y = :y, 
        width = :width, 
        height = :height, 
        angle = :angle,
        z_index = :z_index
    WHERE note_id = :note_id
    AND user_id = :user_id
    AND is_temp = 1
    AND type = "image"'
    );

    $stt->bindValue(':x', $default_x);
    $stt->bindValue(':y', $default_y);
    $stt->bindValue(':width', $default_width);
    $stt->bindValue(':height', $default_height);
    $stt->bindValue(':angle', $default_angle);
    $stt->bindValue(':z_index', $default_z_index);
    $stt->bindValue(':note_id', $note_id, PDO::PARAM_INT);
    $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
    $stt->execute();

    $stt = $db->prepare(
        'DELETE FROM board_images
        WHERE note_id = :note_id
        AND user_id = :user_id
        AND type = "stamp"'
        );
    $stt->bindValue(':note_id', $note_id, PDO::PARAM_INT);
    $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
    $stt->execute();

    header('Location: list.php?id=' . $note_id);
    exit;
    }

    // ボード情報を取得
    $stt = $db->prepare('SELECT * FROM notes WHERE id = :id AND user_id = :user_id');

    $stt->bindValue(':id', $note_id, PDO::PARAM_INT);
    $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
    $stt->execute();
    $note = $stt->fetch();

    // 新規作成時はSELECT結果がないため、空のnoteデータを設定
    if(empty($note)) {
        $note = [
            'id' => '',
            'title' => '',
            'contents' => ''
        ];
    }
    // might need to change it later

    if(!empty($_POST['image_ids'])) {
        $image_ids = $_POST['image_ids'];

        $stt = $db->prepare(
            'UPDATE board_images 
                SET 
                    note_id = :note_id,
                    is_temp = 1,
                    x = :x,
                    y = :y,
                    width = :width,
                    height = :height,
                    angle = :angle,
                    z_index = :z_index
                WHERE id = :id 
                AND user_id = :user_id 
                AND note_id IS NULL'
        );

        $stt->bindValue(':note_id', $note_id, PDO::PARAM_INT);
        $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
        $stt->bindValue(':x', $default_x);
        $stt->bindValue(':y', $default_y);
        $stt->bindValue(':width', $default_width);
        $stt->bindValue(':height', $default_height);
        $stt->bindValue(':angle', $default_angle);
        $stt->bindValue(':z_index', $default_z_index);

        foreach($image_ids as $image_id) {
            $stt->bindValue(':id', $image_id);
            $stt->execute();
        }
    }

    $stt = $db->prepare(
        'SELECT
                board_images.id AS img_id,
                board_images.img_path,
                board_images.type,
                board_images.x,
                board_images.y,
                board_images.width,
                board_images.height,
                board_images.z_index,
                board_images.angle
            FROM board_images 
            WHERE user_id = :user_id AND note_id = :note_id'
    );
    $stt->bindValue(':user_id', $_SESSION["id"], PDO::PARAM_INT);
    $stt->bindValue(':note_id', $note_id, PDO::PARAM_INT);
    $stt->execute();

    $data = $stt->fetchAll();
} catch (PDOException $e) {
    throw new Exception("データベースエラー:{$e->getMessage()}");
}


// board bg column
$boardBg = $note['bg_style'] ?? '';

if ($boardBg === '' || $boardBg === null) {
    $boardBg = 'snow';
}

$userName = e($username);

?>

<!DOCTYPE html>
<html lang="ja" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ボード編集 | Edit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/member.css">
    <link rel="stylesheet" href="../css/edit.css">
</head>


<body>
    <aside class="sidebar">

        <a href="index.php" class="sidebar-logo">
            M<span><sup>3</sup></span>
        </a>

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

            <a href="library.php" class="nav-item">
                <span class="nav-icon">+</span>
                <span>ライブラリ</span>
            </a>
        </nav>

        <!-- needs to check it later, review here -->
        <div class="sidebar-divider"></div>

        <a href="logout.php" class="logout-btn">
            <span>
                <img src="../icons/logout.png" style="width: 35px;" alt="logout">
            </span>
            <span>ログアウト</span>
        </a>

        <div class="sidebar-divider"></div>

        <div class="sidebar-user">
            <div class="user-avatar">
                <?= mb_substr($userName, 0, 2) ?>
            </div>

            <div class="user-info">
                <div class="user-name"><?= $userName ?></div>
                <div class="user-role">ユーザ</div>
            </div>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <h1>ボード編集</h1>
                <p>写真を並べて、あなただけのボードをつくりましょう</p>
            </div>

            <div class="topbar-right">
                <img src="../images/tarot.png" width="50px" alt="switchmode">
                <span class="theme-toggle-label" id="theme-label"></span>
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
                <a href="#" class="avatar-btn">
                    <?= mb_substr($userName, 0, 2) ?>
                </a>
            </div>
        </div>

        <!-- board.js writes the save confirmation in here -->
        <p id="save-message" class="save-message"></p>

        <!-- title and memo fields, same names and ids board.js expects -->
        <form action="edit.php" method="post" class="edit-fields">
            <div class="edit-group">

                <label class="edit-label" for="noteTitle">タイトル</label>
                <input type="text" name="title" id="noteTitle" class="edit-input"
                    placeholder="ボードのタイトル"
                    value="<?= e($_POST['title'] ?? $note['title']) ?>">
            </div>

            <div class="edit-group">
                <label class="edit-label" for="noteContents">メモ</label>
                <input type="text" name="contents" id="noteContents" class="edit-input"
                    placeholder="このボードについて何か書きましょう..."
                    value="<?= e($_POST['contents'] ?? $note['contents']) ?>">
            </div>
        </form>


        <!-- board main function starts from here -->
        <div class="board-toolbar">

            <!-- row 1: save, focus, sizes, rotations -->
            <div class="toolbar-row">
                <div class="toolbar-group">
                    <input type="hidden" name="id" id="noteId" value="<?= e($note['id']) ?>">
                    <input type="button" value="保存する" id="save-btn" class="btn-neu btn-primary">
                    <input type="button" value="&#x26F6; 集中モード" id="focusToggle" class="btn-neu btn-tool">
                </div>

                <div class="toolbar-group">
                    <input type="button" value="＋大きく" id="plusSize" class="btn-neu btn-tool">
                    <input type="button" value="－小さく" id="minusSize" class="btn-neu btn-tool">
                    <input type="button" value="&#8635;" id="rotateRight" class="btn-neu btn-tool" title="時計回りに回転">
                    <input type="button" value="&#8634;" id="rotateLeft" class="btn-neu btn-tool" title="反時計回りに回転">
                </div>
            </div>

                        <div class="toolbar-row">
            <!-- background color palette area. might add more later on or change it for a color palette  -->
            <div class="toolbar-group bg-picker">
                <span class="toolbar-label">背景</span>

                <button type="button" class="bg-swatch" data-bg="#2d3142" style="background:#2d3142" title="ブラック">
                </button>

                <button type="button" class="bg-swatch" data-bg="#ffffff" style="background:#ffffff" title="ホワイト">
                </button>

                <button type="button" class="bg-swatch" data-bg="#fdf6e3" style="background:#fdf6e3" title="クリーム">
                </button>

                <button type="button" class="bg-swatch" data-bg="#fce4ec" style="background:#fce4ec" title="ピンク">
                </button>

                <button type="button" class="bg-swatch" data-bg="#c2e6ff" style="background:#c2e6ff" title="ブルー">
                </button>

                <button type="button" class="bg-swatch" data-bg="#83ee8c" style="background:#83ee8c" title="グリーン">
                </button>

                <button type="button" class="bg-swatch" data-bg="linear-gradient(135deg, #fce4ec, #e3f2fd)" style="background:linear-gradient(135deg, #fce4ec, #e3f2fd)" title="マシュマロ">
                </button>

                <button type="button" class="bg-swatch" data-bg="linear-gradient(135deg, #fdf6e3, #ffd166)" style="background:linear-gradient(135deg, #fdf6e3, #ffd166)" title="サンセット">
                </button>

                <button type="button" class="bg-swatch" data-bg="radial-gradient( #ffc43d, #e29d1b)" style="background:radial-gradient( #ffd166, #ffc02e, #ef082d)" title="ぷーさん">
                </button>

                <button type="button" class="bg-swatch" data-bg="linear-gradient(160deg, #667eea, #764ba2, #f865cc)" style="background:radial-gradient( #fa54c8, #f865cc, #667eea, #764ba2, #f865cc)" title="インスタ">
                </button>

                <button type="button" class="bg-swatch" data-bg="radial-gradient( #b9d9ff, #ffffff, #87b5ec)" style="background:linear-gradient(135deg, #87b5ec, #d3e6fd)" title="空">
                </button>
            </div>


            <!-- sticker picker. the panel sits hidden until the button opens it -->
            <div class="toolbar-group sticker-group">
                <button type="button" class="btn-neu btn-tool" id="stickerToggle">スタンプ</button>

                <div class="sticker-panel" id="stickerPanel" hidden>
                    <?php


                    $sticker_dir = '../stickers/';
                    $files = glob($sticker_dir . '*.{png,gif,webp}', GLOB_BRACE) ?: [];
                    foreach ($files as $f):
                        $name = basename($f);
                    ?>
                    
                        <img src="<?= e($sticker_dir . $name) ?>"
                            class="sticker-choice"
                            data-sticker="<?= e($name) ?>"
                            alt="sticker">
                    <?php endforeach; ?>

                    <?php if (empty($files)): ?>
                        <p class="sticker-empty">stickers/ フォルダに画像を入れてください</p>
                    <?php endif; ?>
                </div>
            </div>
            </div>

            <!-- row 3: add / remove images -->
            <div class="toolbar-row">
            <div class="toolbar-group">
                <!-- 画像追加フォーム -->
                <span class="toolbar-label">Add/Remove</span>
                <form action="edit.php" method="post" id="add-image-form" class="inline-form">
                    <input type="hidden" name="id" id="libraryNoteId" value="<?= e($note['id']) ?>">
                    <input type="hidden" name="title" id="sendTitle">
                    <input type="hidden" name="contents" id="sendContents">
                    <input type="hidden" name="from_library" value="1">

                    <button type="button" name="remove_image" id="add-image-btn" class="btn-neu btn-tool">画像を追加</button>
                </form>

                <!-- 画像を外すフォーム -->
                <form action="edit.php" method="post" class="inline-form" id="remove-image-form">
                    <input type="hidden" name="id" value="<?= e($note['id']) ?>">
                    <input type="hidden" name="image_id" id="image_id" value="">
                    <button type="submit" class="btn-neu btn-tool" style="color: #c70324;">画像を外す</button>
                </form>

                <!-- 保存前の追加画像を取り消す -->
                <form action="edit.php" method="post" class="inline-form">
                    <input type="hidden" name="id" value="<?= e($note['id']) ?>">
                    <input type="hidden" name="cancel_edit" value="1">
                    <button type="submit" class="btn-neu btn-danger-soft">キャンセル</button>
                </form>
            </div>
            </div>

        </div>


        <!-- the board itself. images are positioned inside this canvas,
         so nothing escapes over the rest of the page anymore -->
        <div class="board-frame">
            <div class="board-canvas" id="boardCanvas"
                data-bg="<?= e($boardBg) ?>"
                style="background: <?= e($boardBg) ?>;">

                <?php foreach ($data as $row): ?>

                    <img
                        src="<?= e($row['img_path']) ?>"
                        class="board-img"
                        draggable="false"
                        data-img-id="<?= e($row['img_id']) ?>"
                        data-type="<?= e($row['type']) ?>"
                        data-angle="<?= e($row['angle']) ?>"
                        style="
                            width:<?= e($row['width']) ?>px;
                            height:<?= e($row['height']) ?>px;
                            position:absolute;
                            left:<?= e($row['x']) ?>px;
                            top:<?= e($row['y']) ?>px;
                            z-index:<?= e($row['z_index']) ?>;
                            transform:rotate(<?= e($row['angle']) ?>deg);">
                
                <?php endforeach; ?>

            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-4.0.0.js"
        integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script src="../js/member.js"></script>
    <script src="../js/board.js"></script>
    <script src="../js/edit.js"></script>


</body>

</html>