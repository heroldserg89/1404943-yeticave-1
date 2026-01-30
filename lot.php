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

if (!$lot) {
    showError404($categories, $user);
}
$bets = getBetsByLotID($con, $lotId);

$title = $lot['title'];
$content = includeTemplate('lot.php',
    [
        'lot' => $lot,
        'bets' => $bets
    ]);

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
