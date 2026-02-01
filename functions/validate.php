<?php

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * isDateValid('2019-01-01'); // true
 * isDateValid('2016-02-29'); // true
 * isDateValid('2019-04-31'); // false
 * isDateValid('10.10.2010'); // false
 * isDateValid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function isDateValid(string $date): bool
{
    $formatToCheck = 'Y-m-d';
    $dateTimeObj = date_create_from_format($formatToCheck, $date);

    return $dateTimeObj !== false && !date_get_last_errors();
}

function validateCategory(string $id, array $categories): ?string
{
    if (!in_array($id, $categories)) {
        return "Выберите категорию из списка";
    }

    return null;
}

function validateNumber(string $value): ?string
{
    $result = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if (!$result) {
        return 'Поле должно быть числом больше нуля.';
    }

    return null;
}

function validateTextLength(string $value, int $min = 0, int $max = 0): ?string
{
    $length = mb_strlen($value);

    if ($max === 0 && $length < $min) {
        return "Минимальная длина — $min символов.";
    }

    if ($max > 0 && ($length < $min || $length > $max)) {
        return "Длина должна быть от $min до $max символов.";
    }

    return null;
}

function validateDateFormat(string $date): ?string
{
    if (!isDateValid($date)) {
        return 'Введите дату в формате "ГГГГ-ММ-ДД"';
    }
    $endDate = date_create($date);
    $currentDate = date_create();
    if ($endDate <= $currentDate) {
        return 'Дата должна быть не меньше завтрашнего дня';
    }
    return null;
}

function validateEmail(string $value): ?string
{
    $result = filter_var($value, FILTER_VALIDATE_EMAIL);
    $errorTextLength = validateTextLength($value, 5, 128);

    if ($errorTextLength !== null) {
        return $errorTextLength;
    }
    if ($result === false) {
        return 'Введите корректный email';
    }

    return null;
}

function getErrorsValidate(array $inputs, array $rules, array $required): array
{
    $errors = [];
    foreach ($inputs as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        if (in_array($key, $required) && empty($value)) {
            $errors[$key] = "Поле обязательно к заполнению";
        }
    }
    return array_filter($errors);
}

function validateBet(string $value, int $minBid): ?string
{
    $numberError = validateNumber($value);
    if ($numberError !== null) {
        return $numberError;
    }

    $bet = (int)$value;
    if ($bet < $minBid) {
        return "Ставка должна быть не меньше {$minBid}";
    }

    return null;
}
