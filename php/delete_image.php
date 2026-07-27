<?php
session_start();
require_once 'functions.php';

login_check();

$db = getDb();

// チェックされた複数のIDを配列として受け取る
$image_ids = $_POST['image_ids'] ?? [];

if (!empty($image_ids) && is_array($image_ids)) {
    try {
        // 安全のため、削除対象がログインユーザー本人のもの（user_id）であるかも条件に加える
        $sql = 'DELETE FROM board_images 
        WHERE id = :id 
        AND user_id = :user_id';

        $stt = $db->prepare($sql);
        

        $delete_count = 0;
        foreach ($image_ids as $id) {
            $stt->bindValue(':id', $id, PDO::PARAM_INT);
            $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
            $stt->execute();
            $delete_count++;
        }
        
        $_SESSION['success_msg'] = $delete_count . '件のパーツを倉庫から削除しました。';
        
    } catch (PDOException $e) {
        $_SESSION['err_msg'] = 'パーツの削除に失敗しました: ' . $e->getMessage();
    }
} else {
    $_SESSION['err_msg'] = '削除するパーツが選択されていません。';
}

// 処理が終わったら画像倉庫一覧（list.php）に自動で戻す
header('Location: list.php');

exit;