<?php
session_start();

require_once 'functions.php';

// セッション確認
login_check();

$err_msg = '';
$message = '';

// it validates each file twice (extension + real content) before saving
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // アップロードされた画像をDBへ保存
    try {
        $db = getDb();

        $stt = $db->prepare('INSERT INTO board_images(user_id, img_path, width, height, angle) VALUES(:user_id, :img_path, :width, :height, :angle)');

        // アップロードされた画像の数分１つずつ
        for ($i = 0; $i < count($_FILES['img_path']['name']); $i++) {

            // アップロード処理の成否を確認
            if ($_FILES['img_path']['error'][$i] !== UPLOAD_ERR_OK) {
                $msg = [
                    UPLOAD_ERR_INI_SIZE => 'php.iniのupload_max_filesize制限を越えています。',
                    UPLOAD_ERR_FORM_SIZE => 'HTMLのMAX_FILE_SIZE 制限を越えています。',
                    UPLOAD_ERR_PARTIAL => 'ファイルが一部しかアップロードされていません。',
                    UPLOAD_ERR_NO_FILE => 'ファイルはアップロードされませんでした。',
                    UPLOAD_ERR_NO_TMP_DIR => '一時保存フォルダーが存在しません。',
                    UPLOAD_ERR_CANT_WRITE => 'ディスクへの書き込みに失敗しました。',
                    UPLOAD_ERR_EXTENSION => '拡張モジュールによってアップロードが中断されました。'
                ];

                $err_msg = $msg[$_FILES['img_path']['error'][$i]];

                // 拡張子は許可されているものか確認
            } elseif (!in_array(
                strtolower(pathinfo($_FILES['img_path']['name'][$i], PATHINFO_EXTENSION)),
                ['gif', 'jpg', 'jpeg', 'png']
            )) {
                $err_msg = '画像以外のファイルはアップロードできません。';

                // ファイルの内容が画像か確認
            } elseif (!in_array(
                finfo_file(
                    finfo_open(FILEINFO_MIME_TYPE),
                    $_FILES['img_path']['tmp_name'][$i]
                ),
                ['image/gif', 'image/jpg', 'image/jpeg', 'image/png']
            )) {
                $err_msg = 'ファイルの内容が画像ではありません。';

                // エラー確認後、アップロード処理
            } else {
                $src = $_FILES['img_path']['tmp_name'][$i];
                $dest = uniqid() . '_' . $_FILES['img_path']['name'][$i];

                if (!move_uploaded_file($src, '../images/' . $dest)) {
                    $err_msg = 'アップロード処理に失敗しました。';
                } else {
                    $stt->bindValue(':user_id', $_SESSION['id']);

                    $stt->bindValue(':img_path', '../images/' . $dest);
                    $stt->bindValue(':width', 200);
                    $stt->bindValue(':height', 200);
                    $stt->bindValue(':angle', 0);
                    $stt->execute();

                    $message = '画像をアップロードしました';
                }
            }
        }

    } catch (PDOException $e) {
        throw new Exception("データベースエラー:{$e->getMessage()}");
    }
}

// grabbing the user name just for the sidebar and avatar
$db = getDb();
$stt = $db->prepare('SELECT user_name FROM users WHERE id = :id');
$stt->bindValue(':id', $_SESSION['id']);
$stt->execute();

$user = $stt->fetch(PDO::FETCH_ASSOC);
$userName = $user ? e($user['user_name']) : 'User';
?>
<!DOCTYPE html>
<html lang="ja" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>アップロード | Upload</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/favicon.png">

    <link rel="stylesheet" href="../css/member.css">
    <link rel="stylesheet" href="../css/upload.css">
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

        <a href="upload.php" class="nav-item active">
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
            <img src="../icons/logout.png" width="35px" alt="logout">
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


<!-- upload form area -->
<main class="main">

    <div class="topbar">
        <div class="topbar-left">
            <h1>写真をアップする</h1>
            <p>写真をドラッグして、倉庫に追加しましょう</p>
        </div>

        <div class="topbar-right">
            <img src="../images/tarot.png" style="width: 50px; border:0;" alt="switch mode">
            <span class="theme-toggle-label" id="theme-label"></span>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
            <a href="#" class="avatar-btn">
                <?= mb_substr($userName, 0, 2) ?>
            </a>
        </div>
    </div>


    <?php if (!empty($err_msg)): ?>
        <div class="alert alert-error"><?= e($err_msg) ?></div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <?= e($message) ?>
            <div class="alert-actions">
                <a href="list.php" class="alert-link">倉庫を見る →</a>
                <a href="edit.php" class="alert-link">ボードをつくる →</a>
                <a href="member.php" class="alert-link">マイページへ戻る</a>
            </div>
        </div>
    <?php endif; ?>


    <!-- posts to itself, exactly like original file -->
    <form class="upload-form" id="uploadForm" method="post" enctype="multipart/form-data">

        <!-- drop zone -->
        <div class="dropzone" id="dropzone">
            <input type="file"
                   name="img_path[]"
                   id="fileInput"
                   accept="image/*"
                   multiple
                   hidden>

            <div class="dropzone-icon">
                <img src="../icons/addpooh.gif" style="width: 45px;" alt="add">
            </div>
            <div class="dropzone-title">ここに写真をドラッグ</div>
            <p class="dropzone-sub">または</p>

            <button type="button" class="dropzone-btn" id="addFile">
                ファイルを選択する
            </button>
        </div>

        <!-- little previews show up here after choosing files -->
        <div class="preview-grid" id="fileArea"></div>

        <button type="submit" class="upload-submit">
            画像を保存する →
        </button>

    </form>

</main>


<script src="../js/member.js"></script>
<script src="../js/upload.js"></script>

</body>
</html>
