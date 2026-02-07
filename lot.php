<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';

$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 1;
;

$lot = getLotById($con, $lotId);

if (!$lot) {
    showError("", 404, $categories, $user);
}

$bets = getBetsByLotID($con, $lotId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(
        INPUT_POST,
        [
            'cost' => FILTER_DEFAULT
        ]
    );
    $errors = validateFormBets($formInputs, $lot);

    if (empty($errors)) {
        addBets($con, $lotId, $user['id'], $formInputs['cost']);
    }
}
mysqli_close($con);
$title = $lot['title'];
$content = includeTemplate(
    'lot.php',
    [
        'lot' => $lot,
        'bets' => $bets,
        'user' => $user,
        'errors' => $errors ?? [],
        'formInputs' => $formInputs ?? []
    ]
);
$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);

print includeTemplate('layout.php', [
    'titlePage' => $title,
    'user' => $user,
    'categories' => $categories,
    'menu' => $menu,
    'content' => $content
]);
