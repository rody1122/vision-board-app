<?php

require_once 'config.php';

// timezone so the greeting on the dashboard shows the right time of day
date_default_timezone_set('Asia/Tokyo');

// エスケープ処理
function e(string $str, string $charset = 'utf-8'): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, $charset, false);
}

// @return PDO DB情報
function getDb() : PDO {

    global $dsn, $db_user, $db_passwd;

    try {
        $db = new PDO($dsn, $db_user, $db_passwd);
        return $db;
    } catch (PDOException $e) {
        throw new Exception("接続エラー:{$e->getMessage()}");
    }
}

// セッション確認
function login_check() {
    if(empty($_SESSION['id'])) {
        header('Location: login.php');
        exit;
    }
}
