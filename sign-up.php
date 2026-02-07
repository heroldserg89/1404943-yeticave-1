<?php

/**
 * @var array $config
 * @var array $categories
 * @var array $user
 * @var mysqli $con
 */
include_once __DIR__ . '/init.php';

if ($user ?? false) {
    showError('Доступ запрещен. Вы уже авторизованы', 403, $categories, $user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(
        INPUT_POST,
        [
            'email' => FILTER_DEFAULT,
            'password' => FILTER_DEFAULT,
            'name' => FILTER_DEFAULT,
            'message' => FILTER_DEFAULT
        ]
    );
    $errors = validateFormRegUser($con, $formInputs);

    if (empty($errors)) {
        registerUser($con, $formInputs);
    }
}
mysqli_close($con);

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);
$content = includeTemplate('registration.php', [
    'errors' => $errors ?? [],
    'formInputs' => $formInputs ?? []
]);
print includeTemplate('layout.php', [
    'titlePage' => 'Регистрация пользователя',
    'user' => $user,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content
]);
