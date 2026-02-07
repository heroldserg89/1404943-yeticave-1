<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';

if ($user === false) {
    showError('Доступ запрещен. Авторизуйтесь', 403, $categories, $user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(
        INPUT_POST,
        [
            'lot-name' => FILTER_DEFAULT,
            'category' => FILTER_DEFAULT,
            'message' => FILTER_DEFAULT,
            'lot-rate' => FILTER_DEFAULT,
            'lot-step' => FILTER_DEFAULT,
            'lot-date' => FILTER_DEFAULT,
        ]
    );


    $errors = validateFormAddLot($formInputs, $categories);

    $upload = uploadImage($_FILES['lot-img'], __DIR__ . '/uploads');
    if ($upload['error']) {
        $errors['lot-img'] = $upload['error'];
    } else {
        $formInputs['lot-img'] = $upload['path'];
    }
    if (empty($errors)) {
        addLot($con, $formInputs, $user);
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
