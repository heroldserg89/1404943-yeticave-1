<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';

$lotId = intval($_GET['id'] ?? 0);

$lot = getLotById($con, $lotId);

if (!$lot) {
    showError404($categories, $user);
}
$bets = getBetsByLotID($con, $lotId);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['cost'];
    $errors = [];

    $formInputs = filter_input_array(INPUT_POST,
        [
            'cost' => FILTER_DEFAULT
        ]);

    $rules = [
        'cost' => function ($value) use ($lot) {
            return validateBet($value, $lot['min_bid']);
        }
    ];

    $errors = getErrorsValidate($formInputs, $rules, $required);

    if (empty($errors)) {
        $sql = 'INSERT INTO bets (price, user_id, lot_id) VALUES (?, ?, ?);';
        $stmt = dbGetPrepareStmt($con, $sql, [$formInputs['cost'], $user['id'], $lotId]);
        mysqli_stmt_execute($stmt);
        header('location: lot.php?id=' . $lotId);
    }
}
mysqli_close($con);
$title = $lot['title'];
$content = includeTemplate('lot.php',
    [
        'lot' => $lot,
        'bets' => $bets,
        'user' => $user,
        'errors' => $errors ?? [],
        'formInputs' => $formInputs ?? []
    ]);
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


