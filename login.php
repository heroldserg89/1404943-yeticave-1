<?php

/**
 * @var array $config
 * @var array $user
 * @var array $categories
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
            'password' => FILTER_DEFAULT
        ]
    );

    $errors = validateFormUserLogin($formInputs);

    if (empty($errors)) {
        $user = authenticateUser($con, $formInputs['email'], $formInputs['password']);

        if ($user) {
            $_SESSION['user'] = $user;
            header("Location: /");
            exit();
        } else {
            $errors['email'] = 'Неверный логин или пароль';
            $errors['password'] = 'Неверный логин или пароль';
        }
    }
} else {
    if (isset($_SESSION['user'])) {
        header("Location: /");
        exit();
    }
}

mysqli_close($con);

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);
$content = includeTemplate('login.php', [
    'errors' => $errors ?? [],
    'formInputs' => $formInputs ?? []
]);
print includeTemplate('layout.php', [
    'titlePage' => 'Регистрация пользователя',
    'user' => $user,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
]);
