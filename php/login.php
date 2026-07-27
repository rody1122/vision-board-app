<?php

session_start();
require_once 'functions.php';


$errors = [];

// leave the error message in english or japanese? needs to ask them
// error language not decided yet!

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['email']))    $errors[] = 'Email is required.';

    if (empty($_POST['password'])) $errors[] = 'Password is required.';

    if (empty($errors)) {
        try {
            $db  = getDb();
            $stt = $db->prepare('SELECT id, password FROM users WHERE email = :email');
            $stt->bindValue(':email', $_POST['email']);
            $stt->execute();
            $row = $stt->fetch();

            if (!empty($row) && password_verify($_POST['password'], $row['password'])) {
                $_SESSION['id'] = $row['id'];
                header('Location: member.php');
                exit;

            } else {
                $errors[] = 'Email or password is incorrect.';
            }

        } catch (PDOException $e) {
            throw new Exception("DB error: {$e->getMessage()}");
        }
    }
}




// if login is not working, section
if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    header('Location: index.php#login');
    exit;
}

// pathway to go to index page
header('Location: index.php#login');
exit;
