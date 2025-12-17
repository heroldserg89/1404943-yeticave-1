<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include_once __DIR__ . '/helpers.php';

$isAuth = rand(0, 1);

$userName = 'Сергей';
$categories = [
    [
        'name' => 'Доски и лыжи',
        'class_name' => 'boards',
    ],
    [
        'name' => 'Крепления',
        'class_name' => 'attachment',
    ],
    [
        'name' => 'Ботинки',
        'class_name' => 'boots',
    ],
    [
        'name' => 'Одежда',
        'class_name' => 'clothing',
    ],
    [
        'name' => 'Инструменты',
        'class_name' => 'tools',
    ],
    [
        'name' => 'Разное',
        'class_name' => 'other',
    ],
];

$lots = [
    [
        'name' => '2014 Rossignol District Snowboard',
        'category' => $categories[0]['name'],
        'price' => 10999,
        'img_url' => 'img/lot-1.jpg',
        'date_end' => date('Y-m-d', strtotime('+1 day')),
    ],
    [
        'name' => 'DC Ply Mens 2016/2017 Snowboard',
        'category' => $categories[0]['name'],
        'price' => 159999,
        'img_url' => 'img/lot-2.jpg',
        'date_end' => date('Y-m-d', strtotime('+1 day')),
    ],
    [
        'name' => 'Крепления Union Contact Pro 2015 года размер L/XL',
        'category' => $categories[1]['name'],
        'price' => 8000,
        'img_url' => 'img/lot-3.jpg',
        'date_end' => date('Y-m-d', strtotime('+2 day')),
    ],
    [
        'name' => 'Ботинки для сноуборда DC Mutiny Charcoal',
        'category' => $categories[2]['name'],
        'price' => 10999,
        'img_url' => 'img/lot-4.jpg',
        'date_end' => date('Y-m-d', strtotime('+3 day')),
    ],
    [
        'name' => 'Куртка для сноуборда DC Mutiny Charcoal',
        'category' => $categories[3]['name'],
        'price' => 7500,
        'img_url' => 'img/lot-5.jpg',
        'date_end' => date('Y-m-d', strtotime('+4 day')),
    ],
    [
        'name' => 'Маска Oakley Canopy',
        'category' => $categories[5]['name'],
        'price' => 5400,
        'img_url' => 'img/lot-6.jpg',
        'date_end' => date('Y-m-d', strtotime('+5 day')),
    ],
];

$content = includeTemplate('main.php', [
    'categories' => $categories,
    'lots' => $lots,
]);

print includeTemplate('layout.php', [
    'title' => 'Главная',
    'isAuth' => $isAuth,
    'userName' => $userName,
    'categories' => $categories,
    'content' => $content,
]);
