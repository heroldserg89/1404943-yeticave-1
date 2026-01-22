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

    $lotId = intval($_GET['id'] ?? 0);

    $lot = getLotById($con, $lotId);

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
    if ($e->getCode() == 404) {
        $content = includeTemplate('404.php');
        $title = '404 Страница не найдена';
        http_response_code(404);
    } else {
        error_log($e->getMessage());
        http_response_code(500);
        echo "Внутренняя ошибка сервера";
        die();
    }
}
$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);

print includeTemplate('layout.php', [
    'titlePage' => $title,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
    'menu' => $menu,
    'content' => $content
]);

mysqli_close($con);
