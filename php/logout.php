<?php
session_start();

require_once 'functions.php';

$_SESSION = [];

session_destroy();

header('Location: index.php');
exit;