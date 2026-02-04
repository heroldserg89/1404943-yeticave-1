<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';

if ($user === false) {
    showError('Доступ запрещен. Авторизуйтесь', 403, $categories, $user);
}
$bets = getMyBets($con, $user['id']);

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);
$content = includeTemplate('my-bets.php', [
    'categories' => $categories,
    'bets' => $bets,
    'user' => $user
]);
print includeTemplate('layout.php', [
    'titlePage' => 'Мои ставки',
    'user' => $user,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
]);
