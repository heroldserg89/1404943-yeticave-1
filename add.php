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
        $required = ['lot-name', 'category', 'message', 'lot-rate', 'lot-step', 'lot-date'];
        $errors = [];

        $rules = [
            'lot-name' => function ($value) {
                return validateTextLength($value, 5, 80);
            },
            'category' => function ($value) use ($catIds) {
                return validateCategory($value, $catIds);
            },
            'message' => function ($value) {
                return validateTextLength($value, 10, 0);
            },
            'lot-rate' => function ($value) {
                return validateNumber($value);
            },
            'lot-step' => function ($value) {
                return validateNumber($value);
            },
            'lot-date' => function ($value) {
                return validateDateFormat($value);
            }
        ];
        $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        foreach ($formInputs as $key => $value) {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                $errors[$key] = $rule($value);
            }
        }

        $errors = array_filter($errors);
        if (!empty($_FILES['lot-img']['name'])) {
            $allowedFileTypes = [
                'image/jpeg' => '.jpg',
                'image/png' => '.png'
            ];
            $tmp_name = $_FILES['lot-img']['tmp_name'];
            $path = $_FILES['lot-img']['name'];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $tmp_name);

            if (!isset($allowedFileTypes[$file_type])) {
                $errors['lot-img'] = 'Загрузите картинку в формате JPEG и PNG';
            } else {
                $filename = uniqid() . $allowedFileTypes[$file_type];
                move_uploaded_file($tmp_name, __DIR__ . "/uploads/$filename");
                $formInputs['lot-img'] = $filename;
            }

        } else {
            $errors['lot-img'] = 'Вы не загрузили файл';
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
$content = includeTemplate('form-add-lot.php', [
    'categories' => $categories,
    'errors' => $errors ?? [],
    'formInputs' => $formInputs ?? []
]);
print includeTemplate('layout.php', [
    'titlePage' => 'Добавить лот',
    'isAuth' => $isAuth,
    'userName' => $userName,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
    'isCalendar' => true
]);
