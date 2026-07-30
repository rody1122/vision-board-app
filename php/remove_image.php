<?php
session_start();
require_once 'functions.php';

login_check();

if(!empty($_POST['image_id'])) {
    try {
        $db = getDb();

        $default_x = 0;
        $default_y = 0;
        $default_angle = 0;
        $default_z_index = 1;

        // 画像をボードから外し、配置情報を初期化
        
        $image_id = $_POST['image_id'];

        $stt = $db->prepare(
            'SELECT type 
            FROM board_images 
            WHERE id = :id
            AND user_id = :user_id'
            );
        $stt->bindValue(':id', $image_id, PDO::PARAM_INT);
        $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
        $stt->execute();

        $type = $stt->fetch();

        if($type !== false && ($type['type'] === 'image' || $type['type'] === 'stamp'))  {
            if($type['type'] === 'image') {
                $stt = $db->prepare(
                    'UPDATE board_images
                SET 
                    note_id = NULL, 
                    is_temp = 0,
                    x = :x, 
                    y = :y, 
                    angle = :angle,
                    z_index = :z_index
                WHERE id = :id
                AND user_id = :user_id'
                );

                $stt->bindValue(':x', $default_x);
                $stt->bindValue(':y', $default_y);
                $stt->bindValue(':angle', $default_angle);
                $stt->bindValue(':z_index', $default_z_index);
                $stt->bindValue(':id', $image_id, PDO::PARAM_INT);
                $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
                $stt->execute();
            } elseif ($type['type'] === 'stamp') {
                $stt = $db->prepare(
                    'DELETE FROM board_images
                    WHERE id = :id
                    AND user_id = :user_id'
                    );
                $stt->bindValue(':id', $image_id, PDO::PARAM_INT);
                $stt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
                $stt->execute();
            } 
            echo json_encode([
                'status' => 'success'
            ]);
            exit;
        } else {
                echo json_encode([
                    'status' => 'error'
                ]);
                exit;
        }
    } catch (PDOException $e) {
        throw new Exception("データベースエラー:{$e->getMessage()}");
    }
} else {
    echo json_encode([
        'status' => 'error'
    ]);
    exit;
}