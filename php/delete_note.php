<?php
session_start();
require_once 'functions.php';

login_check(); 

$db = getDb();

// まとめて一括削除（配列での送信）が来た場合
if (!empty($_POST['note_ids']) && is_array($_POST['note_ids'])) {
    $note_ids = $_POST['note_ids'];
}

// 従来の1件削除（単一のID送信）が来た場合、配列に包んで同じループで処理できるようにする
elseif (!empty($_POST['id'])) {
    $note_ids = [$_POST['id']];
} 
 
else {
    header('Location: library.php'); 
    exit;
}


try {

    $db->beginTransaction();

    $update_sql = 
        'UPDATE board_images 
        SET 
            note_id = NULL,
            is_temp = 0
        WHERE note_id = ? 
        AND user_id = ? 
        AND type = "image"';

    $update_stt = $db->prepare($update_sql);

    $stamp_delete_sql =
        'DELETE FROM board_images
        WHERE note_id = ?
        AND user_id = ?
        AND type = "stamp"';

    $stamp_delete_stt = $db->prepare($stamp_delete_sql);

    $delete_sql = 
        'DELETE FROM notes 
        WHERE id = ? 
        AND user_id = ?';

    $delete_stt = $db->prepare($delete_sql);

    // 削除対象のIDをループで1つずつ処理
    foreach ($note_ids as $note_id) {
        $note_id = (int)$note_id;

        // 1. 先に対象ノートの画像を倉庫（NULL）へ戻す
        $update_stt->execute([$note_id, $_SESSION['id']]);

        // スタンプを削除
        $stamp_delete_stt->execute([$note_id, $_SESSION['id']]);

        // 2. ノートを削除する
        $delete_stt->execute([$note_id, $_SESSION['id']]);
    }

    // すべて成功したらDBを確定
    $db->commit();

    $_SESSION['success_msg'] = '選択されたボードを削除しました。配置されていた画像は倉庫に戻りました。';
    header('Location: library.php'); 
    exit;

    // error section
} catch (PDOException $e) {
    $db->rollBack();
    die('ボードの削除に失敗しました: ' . $e->getMessage());
}