<?php
require_once 'functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['user_name'])) $errors[] = 'Name is required.';
    if (empty($_POST['email']))     $errors[] = 'Email is required.';
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        $errors[] = 'Invalid email format.';
    if (empty($_POST['email_confirm'])) $errors[] = 'Please confirm your email.';
    if (!empty($_POST['email']) && !empty($_POST['email_confirm']) && $_POST['email'] !== $_POST['email_confirm'])
        $errors[] = 'Emails do not match.';
    if (empty($_POST['password']))         $errors[] = 'Password is required.';
    if (empty($_POST['password_confirm'])) $errors[] = 'Please confirm your password.';
    if (!empty($_POST['password']) && !empty($_POST['password_confirm']) && $_POST['password'] !== $_POST['password_confirm'])
        $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        try {
            $db  = getDb();
            $stt = $db->prepare('SELECT email FROM users WHERE email = :email');
            $stt->bindValue(':email', $_POST['email']);
            $stt->execute();
            $row = $stt->fetch();

            if (!empty($row)) {
                $errors[] = 'This email is already registered.';
            } else {
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $ins  = $db->prepare('INSERT INTO users(user_name, email, password) VALUES(:name, :email, :pass)');
                $ins->bindValue(':name',  $_POST['user_name']);
                $ins->bindValue(':email', $_POST['email']);
                $ins->bindValue(':pass',  $hash);
                $ins->execute();

                // Auto-login after register
                $stt2 = $db->prepare('SELECT id FROM users WHERE email = :email');
                $stt2->bindValue(':email', $_POST['email']);
                $stt2->execute();
                $newUser = $stt2->fetch();
                session_start();
                $_SESSION['id'] = $newUser['id'];
                header('Location: member.php');
                exit;
            }
        } catch (PDOException $e) {
            throw new Exception("DB error: {$e->getMessage()}");
        }
    }
}

if (!empty($errors)) {
    session_start();
    $_SESSION['register_errors'] = $errors;
    header('Location: index.php#register');
    exit;
}

header('Location: index.php#register');
exit;
