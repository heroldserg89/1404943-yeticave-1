<?php

/**
 * @var array $config
 * @var array $categories
 * @var array $user
 * @var mysqli $con
 */
include_once __DIR__ . '/init.php';

if ($user ?? false) {
    showError('Доступ запрещен. Вы уже авторизованы', $categories, $user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['email', 'password', 'name', 'message'];
    $errors = [];

    $rules = [
        'email' => function ($value) {
            return validateEmail($value);
        },
        'password' => function ($value) {
            return validateTextLength($value, 8);
        },
        'name' => function ($value) {
            return validateTextLength($value, 5, 80);
        },
        'message' => function ($value) {
            return validateTextLength($value, 10);
        }
    ];
    $formInputs = filter_input_array(INPUT_POST,
        [
            'email' => FILTER_DEFAULT,
            'password' => FILTER_DEFAULT,
            'name' => FILTER_DEFAULT,
            'message' => FILTER_DEFAULT
        ]);

    $errors = getErrorsValidate($formInputs, $rules, $required);
    if (empty($errors)) {
        // Проверка существования пользователя с подготовленным выражением
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = dbGetPrepareStmt($con, $sql, [$formInputs['email']]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $errors['email'] = 'Пользователь с этим email уже зарегистрирован';
        } else {
            $password = password_hash($formInputs['password'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (email, password, name, contacts) VALUES (?, ?, ?, ?)";
            $stmt = dbGetPrepareStmt($con, $sql, [
                $formInputs['email'],
                $password,
                $formInputs['name'],
                $formInputs['message']
            ]);

            if (!mysqli_stmt_execute($stmt)) {
                $errors['general'] = 'Ошибка при регистрации. Попробуйте позже.';
            }
        }

        if (empty($errors)) {
            header("Location: /login.php");
            exit();
        }
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
