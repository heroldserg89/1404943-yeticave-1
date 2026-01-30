<?php

/**
 * @var array $config
 * @var array $categories
 * @var array $user
 * @var mysqli $con
 */
include_once __DIR__ . '/init.php';

if ($user ?? false) {
    showError403('Доступ запрещен. Вы уже авторизованы', $categories, $user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['email', 'password', 'name', 'message'];
    $errors = [];
    $formInputs = filter_input_array(INPUT_POST,
        [
            'email' => FILTER_DEFAULT,
            'password' => FILTER_DEFAULT,
            'name' => FILTER_DEFAULT,
            'message' => FILTER_DEFAULT
        ]);

    $rules = [
        'email' => function ($value) use ($con) {
            $errorEmail = validateEmail($value);
            if ($errorEmail !== null) {
                return $errorEmail;
            }
            if (getUsersByEmail($con, $value)) {
                return 'Пользователь с этим email уже зарегистрирован';
            }
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


    $errors = getErrorsValidate($formInputs, $rules, $required);

    if (empty($errors)) {

        $password = password_hash($formInputs['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (email, password, name, contacts) VALUES (?, ?, ?, ?)";
        $stmt = dbGetPrepareStmt($con, $sql, [
            $formInputs['email'],
            $password,
            $formInputs['name'],
            $formInputs['message']
        ]);

        if (!mysqli_stmt_execute($stmt)) {
            error_log(mysqli_error($con));
            http_response_code(500);
            die("Внутренняя ошибка сервера");
        }
        header("Location: /login.php");
        exit();

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
