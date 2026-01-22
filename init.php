<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

include_once __DIR__ . '/functions/db.php';
include_once __DIR__ . '/functions/template.php';
include_once __DIR__ . '/functions/validate.php';

$config = require_once __DIR__ . '/config.php';
$con = connectDB($config['db']);

$categories = getCategories($con);

$isAuth = rand(0, 1);
$userName = 'Сергей';
