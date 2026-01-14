<?php

/**
 * @var number $isAuth
 * @var string $userName
 * @var array $config
 */
include_once __DIR__ . '/init.php';

try {
    $con = connectDB($config['db']);

    $categories = getCategories($con);

    $lots = getlots($con);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "Внутренняя ошибка сервера";
    die();
}
mysqli_close($con);

$menu = includeTemplate('promo.php', [
    'categories' => $categories,
]);
$content = includeTemplate('lots.php', [
    'lots' => $lots,
    'title' => 'Открытые лоты'
]);

print includeTemplate('layout.php', [
    'titlePage' => 'Главная',
    'isAuth' => $isAuth,
    'userName' => $userName,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
    'isMain' => true
]);
