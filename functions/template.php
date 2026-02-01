<?php

use JetBrains\PhpStorm\NoReturn;

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function includeTemplate(string $name, array $data = []): string
{
    $name = "templates/$name";
    $result = 'Ошибка загрузки шаблона';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    return ob_get_clean();
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function getNounPluralForm(int $number, string $one, string $two, string $many): string
{
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    return match (true) {
        $mod100 >= 11 && $mod100 <= 20 => $many,
        $mod10 > 5 => $many,
        $mod10 === 1 => $one,
        $mod10 >= 2 && $mod10 <= 4 => $two,
        default => $many,
    };
}

/**
 * Форматирует цену
 * @param float $price Цена
 * @return string Форматированая цена
 */

function formatPrice(float $price): string
{
    $price = ceil($price);
    if ($price > 999) {
        $price = number_format($price, 0, '', ' ');
    }
    return "$price<b class='rub'>р</b>";
}

/**
 * Вычисляет оставшееся время до указанной будущей даты и возвращает количество целых часов и минут.
 * @param string $date дату в формате ГГГГ-ММ-ДД
 * @return array Количество часов и минут до указаной даты
 */

function getTimeRemaining(string $date): array
{
    $curDate = date_create();
    try {
        $endDate = date_create($date);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return ['00', '00'];
    }

    if ($endDate <= $curDate) {
        return ['00', '00'];
    }
    $diff = date_diff($curDate, $endDate);
    $totalHours = ($diff->days * 24) + $diff->h;
    $totalHours = str_pad($totalHours, 2, '0', STR_PAD_LEFT);
    $minutes = str_pad($diff->i, 2, '0', STR_PAD_LEFT);
    return [$totalHours, $minutes];
}

/**
 * Возвращает CSS-класс для невалидного поля
 *
 * @param array $errors Массив ошибок
 * @param string $field Название поля
 * @param string $class CSS-класс для невалидного поля
 * @return string CSS-класс или пустая строка
 */
function getErrorClass(array $errors, string $field, string $class = 'form__item--invalid'): string
{
    return isset($errors[$field]) ? $class : '';
}

function showError403(string $message, array $categories, false|array $user): void
{
    $content = includeTemplate('403.php', [
        'message' => $message,
        'user' => $user
    ]);
    $titlePage = '403 Нет доступа';

    $menu = includeTemplate('menu.php', [
        'categories' => $categories,
    ]);
    print includeTemplate('layout.php', [
        'titlePage' => $titlePage,
        'user' => $user,
        'menu' => $menu,
        'categories' => $categories,
        'content' => $content,
    ]);
    http_response_code(403);
    exit();
}

function showError404(array $categories, $user): void
{
    $message = 'Данная страница не существует';
    $content = includeTemplate('404.php', [
        'message' => $message
    ]);

    $menu = includeTemplate('menu.php', [
        'categories' => $categories,
    ]);
    print includeTemplate('layout.php', [
        'titlePage' => $message,
        'menu' => $menu,
        'user' => $user,
        'categories' => $categories,
        'content' => $content,
    ]);
    http_response_code(404);
    exit();
}

function buildPaginationLink(int $page): string
{
    // Копируем текущие GET-параметры
    $params = $_GET;

    // Добавляем/обновляем параметр page
    $params['page'] = $page;

    // Формируем query string
    return '?' . http_build_query($params);
}

/**
 * Форматирует прошедшее время в читаемый вид.
 * Если прошло больше часа - возвращает дату
 *
 * @param string $datetime Дата в формате 'Y-m-d H:i:s'
 * @return string Отформатированная строка времени
 */
function formatElapsedTime(string $datetime): string
{
    $now = new DateTime();
    $past = new DateTime($datetime);
    $interval = $now->diff($past);

    // Вычисляем общее количество часов
    $totalHours = ($interval->days * 24) + $interval->h;
    if ($totalHours > 0) {
        return $past->format('d.m.Y в H:i');
    }

    $minutes = $interval->i;
    $word = getNounPluralForm(
        $minutes,
        'минуту',
        'минуты',
        'минут'
    );
    return $minutes . ' ' . $word . ' назад';
}

/**
 * Проверяет, может ли пользователь сделать ставку на лот
 *
 * @param false|array $user Текущий пользователь (или false если не залогинен)
 * @param array $lot Данные лота
 * @param array $bets Массив ставок на лот
 * @return bool true если пользователь может сделать ставку
 */
function canUserPlaceBet(false|array $user, array $lot, array $bets): bool
{
    // Не залогинен
    if (!$user) {
        return false;
    }

    // Автор лота не может ставить на свой лот
    if ((int)$user['id'] === (int)$lot['author_id']) {
        return false;
    }

    // Если уже есть ставки, текущий пользователь не может перебить свою же ставку
    if (!empty($bets) && (int)$bets[0]['user_id'] === (int)$user['id']) {
        return false;
    }

    return true;
}

?>
