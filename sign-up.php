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
        $required = ['email', 'password', 'name', 'message'];;
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

        foreach ($formInputs as $key => $value) {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                $errors[$key] = $rule($value);
            }
            if (in_array($key, $required) && empty($value)) {
                $errors[$key] = "Поле обязательно к заполнению";
            }
        }

        $errors = array_filter($errors);

        if (empty($errors)) {
            $email = mysqli_real_escape_string($con, $formInputs['email']);
            $sql = "SELECT id FROM users WHERE email = '$email'";
            $res = mysqli_query($con, $sql);
            if (mysqli_num_rows($res) > 0) {
                $errors['email'] = 'Пользователь с этим email уже зарегистрирован';
            } else {
                $password = password_hash($formInputs['password'], PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (email, password, name, contacts) VALUES (?, ?, ?, ?)";
                $stmt = dbGetPrepareStmt($con, $sql, [$email, $password, $formInputs['name'], $formInputs['message']]);
                $res = mysqli_stmt_execute($stmt);
            }
            if (empty($errors)) {
                header("Location: /login.php");
                exit();
            }
        }
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
$content = includeTemplate('registration.php', [
    'errors' => $errors ?? [],
    'formInputs' => $formInputs ?? []
]);
print includeTemplate('layout.php', [
    'titlePage' => 'Регистрация пользователя',
    'isAuth' => $isAuth,
    'userName' => $userName,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content
]);
