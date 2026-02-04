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

/**
 * Валидирует числовое значение на соответствие целому положительному числу
 *
 * Функция проверяет, что переданное значение является целым числом больше нуля.
 * Использует встроенный фильтр PHP для валидации целых чисел с заданным диапазоном.
 *
 * @param string $value Строковое значение для валидации
 *
 * @return string|null Возвращает строку с сообщением об ошибке, если валидация не пройдена,
 *                    или null, если значение валидно
 */
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

/**
 * Валидирует длину текстового поля с использованием многобайтовых строк
 *
 * Функция проверяет длину строки в символах (с учетом многобайтовых кодировок)
 * и возвращает сообщение об ошибке, если длина не соответствует заданным ограничениям.
 *
 * @param string $value Текстовая строка для проверки длины
 * @param int $min Минимальная допустимая длина строки в символах
 *                (по умолчанию: 0 - без минимального ограничения)
 * @param int $max Максимальная допустимая длина строки в символах
 *                (по умолчанию: 0 - без максимального ограничения,
 *                 если установлено значение > 0, то проверяется и максимальная длина)
 *
 * @return string|null Возвращает строку с сообщением об ошибке валидации,
 *                    если длина строки не соответствует заданным ограничениям.
 *                    Возвращает null, если валидация пройдена успешно.
 */
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

/**
 * Валидирует дату на соответствие формату и проверяет, что она находится в будущем
 *
 * Функция выполняет две проверки:
 * 1. Корректность формата даты (использует вспомогательную функцию isDateValid)
 * 2. Что указанная дата находится в будущем относительно текущей даты
 *
 * @param string $date Дата в строковом формате для валидации
 *
 * @return string|null Возвращает строку с сообщением об ошибке валидации,
 *                    если дата не соответствует требованиям.
 *                    Возвращает null, если дата валидна.
 */
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

/**
 * Валидирует email-адрес на корректность формата и длину
 *
 * Функция выполняет две проверки:
 * 1. Проверяет длину email-адреса (от 5 до 128 символов)
 * 2. Проверяет корректность формата email с помощью FILTER_VALIDATE_EMAIL
 *
 * @param string $value Строка с email-адресом для валидации
 *
 * @return string|null Возвращает строку с сообщением об ошибке валидации,
 *                    если email не соответствует требованиям.
 *                    Возвращает null, если email валиден.
 */
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

/**
 * Валидирует массив входных данных по заданным правилам и обязательным полям
 *
 * Функция проходит по всем входным данным и применяет к ним соответствующие
 * правила валидации, а также проверяет обязательность заполнения полей.
 *
 * @param array $inputs Ассоциативный массив входных данных для валидации
 * @param array $rules Ассоциативный массив правил валидации,
 *                    где ключ - имя поля, значение - callback-функция валидации
 * @param array $required Массив с именами обязательных для заполнения полей
 *
 * @return array Ассоциативный массив ошибок валидации,
 *              где ключ - имя поля, значение - текст ошибки.
 *              Пустые значения ошибок фильтруются из результата.
 */
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

/**
 * Валидирует ставку на соответствие числовому формату и минимальному значению
 *
 * Функция выполняет две проверки:
 * 1. Проверяет, что значение является целым числом больше нуля
 * 2. Проверяет, что ставка не меньше установленного минимального значения
 *
 * @param string $value Строковое значение ставки для валидации
 * @param int $minBid Минимально допустимое значение ставки
 *
 * @return string|null Возвращает строку с сообщением об ошибке валидации,
 *                    если ставка не соответствует требованиям.
 *                    Возвращает null, если ставка валидна.
 */
function validateBet(string $value, int $minBid): ?string
{
    $numberError = validateNumber($value);
    if ($numberError !== null) {
        return $numberError;
    }

    $bet = (int)$value;
    if ($bet < $minBid) {
        return "Ставка должна быть не меньше $minBid";
    }

    return null;
}

/**
 * Валидирует данные формы регистрации пользователя
 *
 * Функция проверяет данные регистрационной формы по заданным правилам:
 * - email: корректность формата и уникальность в системе
 * - password: минимальная длина 8 символов
 * - name: длина от 5 до 80 символов
 * - message: минимальная длина 10 символов
 *
 * @param mysqli $con Объект соединения с базой данных
 * @param array $formInputs Ассоциативный массив данных формы регистрации
 *
 * @return array Массив ошибок валидации или пустой массив, если ошибок нет
 */
function validateFormRegUser(mysqli $con, array $formInputs): array
{
    $required = ['email', 'password', 'name', 'message'];

    $rules = [
        'email' => function ($value) use ($con) {
            $errorEmail = validateEmail($value);
            if ($errorEmail !== null) {
                return $errorEmail;
            }
            if (getUsersByEmail($con, $value)) {
                return 'Пользователь с этим email уже зарегистрирован';
            }
            return null;
        },
        'password' => function ($value) {
            return validateTextLength($value, 8);
        },
        'name' => function ($value) {
            return validateTextLength($value, 5, 80);
        },
        'message' => function ($value) {
            return validateTextLength($value, 10);
        }
    ];
    return getErrorsValidate($formInputs, $rules, $required) ?? [];
}

/**
 * Валидирует данные формы входа пользователя
 *
 * Функция проверяет данные формы входа по заданным правилам:
 * - email: корректность формата
 * - password: минимальная длина 8 символов
 *
 * @param array $formInputs Ассоциативный массив данных формы входа
 *
 * @return array Массив ошибок валидации или пустой массив, если ошибок нет
 */
function validateFormUserLogin(array $formInputs): array
{
    $required = ['email', 'password'];

    $rules = [
        'email' => function ($value) {
            return validateEmail($value);
        },
        'password' => function ($value) {
            return validateTextLength($value, 8);
        }
    ];
    return getErrorsValidate($formInputs, $rules, $required) ?? [];
}

/**
 * Валидирует данные формы добавления нового лота
 *
 * Функция проверяет все обязательные поля формы создания лота:
 * - lot-name: длина от 5 до 80 символов
 * - category: принадлежность к существующей категории
 * - message: минимальная длина 10 символов
 * - lot-rate: целое число больше нуля
 * - lot-step: целое число больше нуля
 * - lot-date: корректный формат и дата в будущем
 *
 * @param array $formInputs Ассоциативный массив данных формы добавления лота
 * @param array $categories Массив существующих категорий для проверки категории лота
 *
 * @return array Массив ошибок валидации или пустой массив, если ошибок нет
 */
function validateFormAddLot(array $formInputs, array $categories): array
{
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
    return getErrorsValidate($formInputs, $rules, $required) ?? [];
}

/**
 * Валидирует данные формы размещения ставки на лот
 *
 * Функция проверяет поле ставки (cost) на соответствие числовому формату
 * и минимально допустимой ставке для конкретного лота
 *
 * @param array $formInputs Ассоциативный массив данных формы ставки
 * @param array $lot Массив данных лота, должен содержать ключ 'min_bid'
 *                  с минимально допустимым значением ставки
 *
 * @return array Массив ошибок валидации или пустой массив, если ошибок нет
 */
function validateFormBets(array $formInputs, array $lot): array
{
    $required = ['cost'];
    $rules = [
        'cost' => function ($value) use ($lot) {
            return validateBet($value, $lot['min_bid']);
        }
    ];

    return getErrorsValidate($formInputs, $rules, $required) ?? [];
}
