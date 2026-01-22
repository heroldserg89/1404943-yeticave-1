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
        $errors = [];

        $rules = [
            'lot-name' => function ($value) {
                return validateTextLength($value, 5, 80);
            },
            'category' => function ($value) use ($catIds) {
                return validateCategory($value, $catIds);
            },
            'message' => function ($value) {
                return validateTextLength($value, 10);
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
        $formInputs = filter_input_array(INPUT_POST,
            [
                'lot-name' => FILTER_DEFAULT,
                'category' => FILTER_DEFAULT,
                'message' => FILTER_DEFAULT,
                'lot-rate' => FILTER_DEFAULT,
                'lot-step' => FILTER_DEFAULT,
                'lot-date' => FILTER_DEFAULT,
            ]);

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
            $tmpName = $_FILES['lot-img']['tmp_name'];
            $path = $_FILES['lot-img']['name'];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $tmpName);

            if (!isset($allowedFileTypes[$file_type])) {
                $errors['lot-img'] = 'Загрузите картинку в формате JPEG и PNG';
            } else {
                $filename = uniqid() . $allowedFileTypes[$file_type];
                $uploadPath = __DIR__ . "/uploads/$filename";

                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $formInputs['lot-img'] = "/uploads/$filename";
                } else {
                    $errors['lot-img'] = 'Ошибка при загрузке файла';
                }
            }

        } else {
            $errors['lot-img'] = 'Вы не загрузили файл';
        }
        if (empty($errors)) {
            $sql = "INSERT INTO lots (title, category_id, description, price_start,mg_url) VALUES (?, ?, ?, ?, ?, ?, 1,  ?)";
            $stmt = dbGetPrepareStmt($con, $sql, $formInputs);
            $res = mysqli_stmt_execute($stmt);
            if ($res) {
                $lotId = mysqli_insert_id($con);
                header('location: lot.php?id=' . $lotId);
                exit;
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
$content = includeTemplate('add-lot.php', [
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
