<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';
include_once __DIR__ . '/getwinner.php';

$lots = getLots($con);

mysqli_close($con);

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);
$promo = includeTemplate('promo.php', [
    'categories' => $categories,
]);
$content = includeTemplate('lots-list.php', [
    'lots' => $lots,
    'title' => 'Открытые лоты'
]);

print includeTemplate('layout.php', [
    'titlePage' => 'Главная',
    'user' => $user,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
    'promo' => $promo,
    'isMain' => true
]);
