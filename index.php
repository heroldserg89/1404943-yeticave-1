<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include_once __DIR__ . '/helpers.php';
$con = mysqli_connect('mysql', 'root', 'root', 'yeticave');

if (!$con) {
    die("Ошибка подключения " . mysqli_connect_error());
}
mysqli_set_charset($con, 'utf8');
$sql = 'SELECT * FROM categories';
$result = mysqli_query($con, $sql);

if (!$result) {
    die("Ошибка запроса категорий: " . mysqli_error($con));
}
$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);

$sql = 'SELECT l.title,
       price_start,
       img_url,
       author_id,
       end_at,
       COALESCE(MAX(b.price), l.price_start) AS current_price,
       c.title                               AS category
FROM lots l
       JOIN categories c ON l.category_id = c.id
       LEFT JOIN bets b ON l.id = b.lot_id
WHERE l.end_at > NOW()
GROUP BY l.id, l.created_at
ORDER BY l.created_at DESC';

$result = mysqli_query($con, $sql);

if (!$result) {
    die("Ошибка запроса категорий: " . mysqli_error($con));
}
$lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

$isAuth = rand(0, 1);
$userName = 'Сергей';

$content = includeTemplate('main.php', [
    'categories' => $categories,
    'lots' => $lots,
]);

print includeTemplate('layout.php', [
    'title' => 'Главная',
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
    'content' => $content,
]);
