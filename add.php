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
    $catIds = array_column($categories, 'id');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $required = ['lot-name', 'category', 'lot-rate', 'lot-step', 'lot-date'];
        $errors = [];

        $rules = [
            'category' => function ($value) use ($categories) {
                return validateCategory($value, $categories);
            }
        ];
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo "Внутренняя ошибка сервера";
    die();
}
mysqli_close($con);

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);
$content = includeTemplate('form-add-lot.php', [
    'categories' => $categories
]);

print includeTemplate('layout.php', [
    'titlePage' => 'Главная',
    'isAuth' => $isAuth,
    'userName' => $userName,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
    'isCalendar' => true
]);
