<?php

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
