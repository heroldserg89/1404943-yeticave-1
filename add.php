<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';

if ($user === false) {
    showError403('Доступ запрещен. Авторизуйтесь', $categories, $user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['lot-name', 'category', 'message', 'lot-rate', 'lot-step', 'lot-date'];

    $catIds = array_column($categories, 'id');

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

    $errors = getErrorsValidate($formInputs, $rules, $required);

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
        $formInputs['author_id'] = $user['id'];
        var_dump($formInputs);
        $sql = "INSERT INTO lots (title, category_id, description, price_start, price_step, end_at, img_url, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = dbGetPrepareStmt($con, $sql, $formInputs);
        $res = mysqli_stmt_execute($stmt);
        if ($res) {
            $lotId = mysqli_insert_id($con);
            header('location: lot.php?id=' . $lotId);
            exit;
        }
    }
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
    'user' => $user,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
    'isCalendar' => true
]);
