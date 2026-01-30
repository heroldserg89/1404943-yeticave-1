<?php

/**
 * @var array $config
 * @var array $user
 * @var array $categories
 * @var mysqli $con
 */
include_once __DIR__ . '/init.php';

if ($user ?? false) {
    showError403('Доступ запрещен. Вы уже авторизованы', $categories, $user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['email', 'password'];
    $errors = [];
    $formInputs = filter_input_array(INPUT_POST,
        [
            'email' => FILTER_DEFAULT,
            'password' => FILTER_DEFAULT
        ]);
    $rules = [
        'email' => function ($value) {
            return validateEmail($value);
        },
        'password' => function ($value) {
            return validateTextLength($value, 8);
        }
    ];


    $errors = getErrorsValidate($formInputs, $rules, $required);

    if (empty($errors)) {
        $user = getUsersByEmail($con, $formInputs['email']);

        if ($user) {
            if (password_verify($formInputs['password'], $user['password'])) {
                unset($user['password']);
                $_SESSION['user'] = $user;
            } else {
                $errors['password'] = 'Неверный логин/пароль';
                $errors['email'] = 'Неверный логин/пароль';
            }
        } else {
            $errors['password'] = 'Неверный логин/пароль';
            $errors['email'] = 'Неверный логин/пароль';
        }
        if (empty($errors)) {
            header("Location: /");
            exit();
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
