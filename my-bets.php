<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';

if ($user === false) {
    showError403('Доступ запрещен. Авторизуйтесь', $categories, $user);
}
$sql = "SELECT b.id,
                   b.price,
                   b.created_at,
                   l.id AS lot_id,
                   l.title,
                   l.img_url,
                   l.end_at,
                   l.price_step,
                   l.winner_id,
                   c.title AS category,
                   COALESCE(MAX(b2.price), l.price_start) AS current_price
            FROM bets b
            JOIN lots l ON b.lot_id = l.id
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bets b2 ON l.id = b2.lot_id
            WHERE b.user_id = {$user['id']}
            GROUP BY b.id, b.created_at, l.id, l.title, l.img_url, l.end_at,
                     l.price_step, l.winner_id, c.title, l.price_start
            ORDER BY b.created_at DESC";
$result = mysqli_query($con, $sql);
if (!$result) {
    error_log(mysqli_error($con));
    die("Внутренняя ошибка сервера");
}
$bets = mysqli_fetch_all($result, MYSQLI_ASSOC);
echo '<pre>';
var_dump($bets);
echo '</pre>';
$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);
$content = includeTemplate('my-bets.php', [
    'categories' => $categories,
]);
print includeTemplate('layout.php', [
    'titlePage' => 'Мои ставки',
    'user' => $user,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
]);
