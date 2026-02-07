<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');
session_start();

include_once __DIR__ . '/functions/db.php';
include_once __DIR__ . '/functions/template.php';
include_once __DIR__ . '/functions/validate.php';
include_once __DIR__ . '/functions/auth.php';
include_once __DIR__ . '/functions/files.php';
include_once __DIR__ . '/functions/url.php';

if (!file_exists(__DIR__ . '/config.php')) {
    die('Файл конфигурации отсутствует');
}
$config = require_once __DIR__ . '/config.php';
$con = connectDB($config['db']);

$categories = getCategories($con);
$user = isLoggedIn();
