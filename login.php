<?php

/**
 * @var array $config
 * @var array $user
 * @var array $categories
 * @var mysqli $con
 */
include_once __DIR__ . '/init.php';

if ($user ?? false) {
    showError('Доступ запрещен. Вы уже авторизованы', $categories, $user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['email', 'password'];
    $errors = [];

    $rules = [
        'email' => function ($value) {
            return validateEmail($value);
        },
        'password' => function ($value) {
            return validateTextLength($value, 8);
        }
    ];
    $formInputs = filter_input_array(INPUT_POST,
        [
            'email' => FILTER_DEFAULT,
            'password' => FILTER_DEFAULT
        ]);

    $errors = getErrorsValidate($formInputs, $rules, $required);

    if (empty($errors)) {
        $email = mysqli_real_escape_string($con, $formInputs['email']);
        $sql = "SELECT id, password, name, contacts FROM users WHERE email = '$email'";
        $res = mysqli_query($con, $sql);
        $user = $res ? mysqli_fetch_assoc($res) : null;
        if ($user) {
            if (password_verify($formInputs['password'], $user['password'])) {
                unset($user['password']);
                $_SESSION['user'] = $user;
            } else {
                $errors['password'] = 'Неверный пароль';
            }
        } else {
            $errors['email'] = 'Такой пользователь не найден';
        }
        if (empty($errors)) {
            header("Location: /index.php");
            exit();
        }
    }
} else {
    if (isset($_SESSION['user'])) {
        header("Location: /index.php");
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
