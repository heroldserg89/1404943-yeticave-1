<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';

$lotId = intval($_GET['id'] ?? 0);

$lot = getLotById($con, $lotId);

try {
    if (!$lot) {
        throw new Exception('Такая страница не найдена', 404);
    }
    $bets = getBetsByLotID($con, $lotId);

    $title = $lot['title'];
    $content = includeTemplate('lot.php',
        [
            'lot' => $lot,
            'bets' => $bets
        ]);
} catch (Exception $e) {
    $content = includeTemplate('404.php');
    $title = '404 Страница не найдена';
    http_response_code(404);
}

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);

print includeTemplate('layout.php', [
    'titlePage' => $title,
    'user' => $user,
    'categories' => $categories,
    'menu' => $menu,
    'content' => $content
]);

mysqli_close($con);
