<?php
session_start();

require_once 'functions.php';

login_check();

// Ajaxからpositionsデータが送られてきたら
if(!empty($_POST['saveBoard'])) {

    // JSON形式をPHPの連想配列に変換
    $positions = json_decode($_POST['positions'], true);

    // 取得したタイトル・メモのデータをnotesテーブルへ更新
    try {
        $db = getDb();

        $note_id = $_POST['noteId'];

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

        } else {
        // note_idがあればタイトル・メモを更新
            $stt = $db->prepare(
                'UPDATE notes
                SET
                    title = :title,
                    contents = :contents
                WHERE id = :id
                AND user_id = :user_id'
            );

            $stt->bindValue(':title', $_POST['noteTitle']);
            $stt->bindValue(':contents', $_POST['noteContents']);
            $stt->bindValue(':id', $note_id);
            $stt->bindValue(':user_id', $_SESSION['id']);
            $stt->execute();
        }


        // this lives in its own try/catch on purpose: if the bg_style
        // column hasnt been added yet (see sql/add_bg_style.sql),
        // the board still saves normally and only the background is skipped
        if (isset($_POST['bgStyle']) && $_POST['bgStyle'] !== '') {
            try {
                $bg_stt = $db->prepare(
                    'UPDATE notes
                    SET bg_style = :bg_style
                    WHERE id = :id
                    AND user_id = :user_id'
                );

                $bg_stt->bindValue(':bg_style', $_POST['bgStyle']);
                $bg_stt->bindValue(':id', $note_id);
                $bg_stt->bindValue(':user_id', $_SESSION['id']);
                $bg_stt->execute();
            } catch (PDOException $bg_e) {
             
            
            }
        }


        // 取得した画像の配置データをboard_imagesテーブルへ更新
        $stt = $db->prepare(
            'UPDATE board_images 
            SET
                note_id = :note_id,
                is_temp = 0, 
                x = :x, 
                y = :y, 
                width = :width, 
                height = :height, 
                z_index = :z_index, 
                angle = :angle 
            WHERE id = :id
            AND user_id = :user_id'
        );

        $stt->bindValue(':note_id', $note_id);
        $stt->bindValue(':user_id', $_SESSION['id']);

        foreach($positions as $position) {
            $x = $position['x'];
            $y = $position['y'];
            $width = $position['width'];
            $height = $position['height'];
            $z_index = $position['zIndex'];
            $angle = $position['angle'];
            $img_id = $position['imgId'];

            $stt->bindValue(':x', $x);
            $stt->bindValue(':y', $y);
            $stt->bindValue(':width', $width);
            $stt->bindValue(':height', $height);
            $stt->bindValue(':z_index', $z_index);
            $stt->bindValue(':angle', $angle);
            $stt->bindValue(':id', $img_id);
            $stt->execute();
        }

        echo json_encode([
            'status' => 'success',
            'note_id' => $note_id
        ]);

    } catch(PDOException $e) {
        throw new Exception("データベースエラー:{$e->getMessage()}");
    }
} else {
    echo 'no data';
}
