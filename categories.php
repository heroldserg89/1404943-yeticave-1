<?php

/**
 * @var mysqli $con
 * @var array $categories
 * @var array $user
 * @var array $config
 */
include_once __DIR__ . '/init.php';


$categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 1;
$curPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;

$pageItems = $config['pagination']['items_per_page'];

$category = getCategoryById($con, $categoryId);
if (!$category) {
    showError('', 404, $categories, $user);
}

if ($curPage > 0) {
    $pagination = getLotsByCategoryPagination($con, $categoryId, $curPage, $pageItems);
    $lots = getLotsByCategory($con, $categoryId, $pageItems, $pagination['offset']);
}

mysqli_close($con);

$menu = includeTemplate('menu.php', [
    'categories' => $categories,
]);

$lotsBlock = includeTemplate('lots-list.php', [
    'lots' => $lots ?? [],
]);
$paginationBlock = includeTemplate('pagination.php', [
    'pages' => $pagination['pages'] ?? [],
    'curPage' => $curPage,
]);

$content = includeTemplate('categories.php', [
    'title' => $category['title'],
    'lotsBlock' => $lotsBlock,
    'pagination' => $paginationBlock,
    'lots' => $lots ?? []
]);

print includeTemplate('layout.php', [
    'titlePage' => 'Главная',
    'user' => $user,
    'menu' => $menu,
    'categories' => $categories,
    'content' => $content,
]);
