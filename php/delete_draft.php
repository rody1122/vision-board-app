<?php
session_start();
require_once 'functions.php';

login_check();

$note_id = $_POST['noteId'] ?? NULL;

if (empty($note_id)) {
    echo json_encode([
        'status' => 'error'
    ]);
    exit;
}

try {
    $db =getDb();

    // 例外処理を有効化
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // トランザクションを開始
    $db->beginTransaction();

    // 破棄する場合(image)
    $stt_parts_return = $db->prepare(
        'UPDATE board_images
        SET note_id = NULL,
            is_temp = 0
        WHERE note_id = :note_id
        AND user_id = :user_id
        AND type = "image"'
    );
    $stt_parts_return->bindValue(':note_id', $note_id);
    $stt_parts_return->bindValue(':user_id', $_SESSION['id']);
    $stt_parts_return->execute();

    // 破棄する場合(stamp)
    $stt_parts_delete = $db->prepare(
        'DELETE FROM board_images
        WHERE note_id = :note_id
        AND user_id = :user_id
        AND type = "stamp"'
    );
    $stt_parts_delete->bindValue(':note_id', $note_id);
    $stt_parts_delete->bindValue(':user_id', $_SESSION['id']);
    $stt_parts_delete->execute();

    // notesも削除
    $stt_board_delete = $db->prepare(
        'DELETE FROM notes
        WHERE id = :id
        AND user_id = :user_id'
    );
    $stt_board_delete->bindValue(':id', $note_id);
    $stt_board_delete->bindValue(':user_id', $_SESSION['id']);

    $stt_board_delete->execute();

    // すべての処理が成功したら、トランザクションをコミット
    $db->commit();

    // 処理結果をJSに返す
    echo json_encode([
        'status' => 'success'
    ]);
} catch(PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo json_encode([
        'status' => 'error'
    ]);
}
