<?php
/**
 * Проверяет, авторизован ли пользователь в системе
 *
 * Функция проверяет наличие данных пользователя в сессии.
 * Если пользователь авторизован, возвращает массив с данными пользователя.
 * Если не авторизован - возвращает false.
 * @return array|false Возвращает ассоциативный массив с данными пользователя
 *                     при успешной авторизации, или false если пользователь
 *                     не авторизован.
 */
function isLoggedIn(): false|array
{
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return false;
}

/**
 * Аутентифицирует пользователя по email и паролю
 *
 * Функция выполняет проверку учетных данных пользователя:
 * 1. Ищет пользователя по email в базе данных
 * 2. Сверяет введенный пароль с хешем из БД
 * 3. При успешной аутентификации удаляет пароль из результата и возвращает данные пользователя
 *
 * @param mysqli $con Объект соединения с базой данных MySQL
 * @param string $email Email пользователя для аутентификации
 * @param string $password Пароль пользователя в открытом виде
 *
 * @return array|false Возвращает ассоциативный массив с данными пользователя без поля 'password'
 *                    при успешной аутентификации, или false если аутентификация не удалась
 */
function authenticateUser(mysqli $con, string $email, string $password): array|false
{
    $user = getUsersByEmail($con, $email);

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    unset($user['password']);
    return $user;
}
