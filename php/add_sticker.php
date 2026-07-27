<?php

session_start();
require_once 'functions.php';
login_check();

$db = getDb();


$sticker = $_POST['sticker'] ?? '';

// stickers and stamps only from here and nothing else
// ここから先はステッカーとスタンプ専用
$safe = basename($sticker);
$img_path = '../stickers/' . $safe;

if (empty($safe) || !file_exists($img_path)) {
    echo json_encode(['status' => 'error']);
    exit;
}

try {
    $note_id = $_POST['note_id'] ?? '';

    if(empty($note_id)) {
        // note_idがなければ新規ボードを作成
        $title = empty($_POST['noteTitle']) ? '無題' : $_POST['noteTitle'];
        $contents = empty($_POST['noteContents']) ? '' : $_POST['noteContents'];

        $stt = $db->prepare(
            'INSERT INTO 
            notes(title, contents, user_id) 
            VALUES(:title, :contents, :user_id)'
            );

        $stt->bindValue(':title', $title);
        $stt->bindValue(':contents', $contents);
        $stt->bindValue(':user_id', $_SESSION['id']);
        $stt->execute();

        $note_id = $db->lastInsertId();
    }

    $stt = $db->prepare(
        'INSERT INTO board_images(
        user_id,
        note_id,
        is_temp,
        img_path,
        type,
        x, y,
        width,
        height,
        angle,
        z_index
        )
        VALUES(:user_id, :note_id, 1, :img_path, "stamp", 50, 50, 150, 150, 0, 1)'
    );

    $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
    $stt->bindValue(':note_id', $note_id);
    $stt->bindValue(':img_path', $img_path);
    $stt->execute();


    echo json_encode([
    'status' => 'success',
    'img_id' => $db->lastInsertId(),
    'img_path' => $img_path,
    'note_id' => $note_id
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error']);
}




