<?php
session_start();

require_once 'functions.php';

login_check();

// Ajaxからpositionsデータが送られてきたら
if(!empty($_POST['saveBoard']) || !empty($_POST['tempSave'])) {

    // JSON形式をPHPの連想配列に変換
    $positions = json_decode($_POST['positions'], true);

    // 取得したタイトル・メモのデータをnotesテーブルへ更新
    try {
        $db = getDb();

        $note_id = $_POST['noteId'] ?? $_POST['id'] ?? null;

        if(empty($note_id)) {
            // note_idがなければ編集中ボードを探す
            $stt = $db->prepare(
                'SELECT id
                FROM notes
                WHERE user_id = :user_id
                AND is_saved = 0
                ORDER BY updated_at DESC
                LIMIT 1
            ');
            $stt->bindValue(':user_id', $_SESSION['id']);
            $stt->execute();
            $draft_note = $stt->fetch();
            // 見つかれば編集中ボードのidをnote_idに入れる
            if($draft_note) {
                $note_id = $draft_note['id'];
            }
        }

        if(empty($note_id)) {
            // note_idがなければ新規ボードを作成
            $title = empty($_POST['title']) ? '無題' : $_POST['title'];
            $contents = empty($_POST['contents']) ? '' : $_POST['contents'];

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

        if (!empty($_POST['saveBoard'])) {
            $stt_draft = $db->prepare(
                'UPDATE notes
                SET 
                    is_draft = 0,
                    is_saved = 1
                WHERE id = :id
                AND user_id = :user_id'
            );
            $stt_draft->bindValue(':id', $note_id);
            $stt_draft->bindValue(':user_id', $_SESSION['id']);
            $stt_draft->execute();
        }

        // this lives in its own try/catch on purpose: if the bg_style
        // column hasnt been added yet (see sql/add_bg_style.sql),
        // the board still saves normally and only the background is skipped
        // 意図的に独立した try/catch に配置しています。
        // もし bg_style カラムがまだ追加されていなくても（sql/add_bg_style.sql を参照）、
        // ボード自体は正常に保存され、背景の処理だけがスキップされます。
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
        $position_stt = $db->prepare(
            'UPDATE board_images 
            SET
                note_id = :note_id, 
                x = :x, 
                y = :y, 
                width = :width, 
                height = :height, 
                z_index = :z_index, 
                angle = :angle 
            WHERE id = :id
            AND user_id = :user_id'
        );

        $position_stt->bindValue(':note_id', $note_id);
        $position_stt->bindValue(':user_id', $_SESSION['id']);

        foreach($positions as $position) {
            $x = $position['x'];
            $y = $position['y'];
            $width = $position['width'];
            $height = $position['height'];
            $z_index = $position['zIndex'];
            $angle = $position['angle'];
            $img_id = $position['imgId'];

            $position_stt->bindValue(':x', $x);
            $position_stt->bindValue(':y', $y);
            $position_stt->bindValue(':width', $width);
            $position_stt->bindValue(':height', $height);
            $position_stt->bindValue(':z_index', $z_index);
            $position_stt->bindValue(':angle', $angle);
            $position_stt->bindValue(':id', $img_id);
            $position_stt->execute();
        }

        // 正式保存時は編集中フラグを解除
        if(!empty($_POST['saveBoard'])) {
            $save_stt = $db->prepare(
                'UPDATE board_images 
                SET is_temp = 0 
                WHERE note_id = :note_id
                AND user_id = :user_id'
            );

            $save_stt->bindValue(':note_id', $note_id);
            $save_stt->bindValue(':user_id', $_SESSION['id']);
            $save_stt->execute();
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
