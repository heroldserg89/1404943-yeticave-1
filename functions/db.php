<?php

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function dbGetPrepareStmt(mysqli $link, string $sql, array $data = []): mysqli_stmt
{
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmtData = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            } elseif (is_string($value)) {
                $type = 's';
            } elseif (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmtData[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmtData);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

function connectDB(array $config): mysqli
{
    $con = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
    mysqli_set_charset($con, 'utf8');
    mysqli_query($con, "SET time_zone = '+03:00'");
    if (!$con) {
        error_log(mysqli_connect_error());
        die("Внутренняя ошибка сервера");
    }

    return $con;
}


function getCategories(mysqli $con): array|false
{
    $sql = 'SELECT * FROM categories';
    $result = mysqli_query($con, $sql);
    if (!$result) {
        error_log(mysqli_error($con));
        die("Внутренняя ошибка сервера");
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? false;
}

function getLots(mysqli $con): array|false
{
    $sql = 'SELECT l.id,
       l.title,
       price_start,
       img_url,
       author_id,
       end_at,
       COALESCE(MAX(b.price), l.price_start) AS current_price,
       c.title                               AS category
FROM lots l
       JOIN categories c ON l.category_id = c.id
       LEFT JOIN bets b ON l.id = b.lot_id
WHERE l.end_at > NOW()
GROUP BY l.id, l.end_at
ORDER BY l.end_at DESC limit 6';

    $result = mysqli_query($con, $sql);
    if (!$result) {
        error_log(mysqli_error($con));
        die("Внутренняя ошибка сервера");
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? false;
}

function getLotById(mysqli $con, int $lotId): array|false
{
    $sql = "SELECT l.*, c.title AS category_name,
            COALESCE(MAX(b.price), l.price_start) AS current_price,
            (COALESCE(MAX(b.price), l.price_start) + l.price_step) AS min_bid
            FROM lots l
            JOIN categories c ON l.category_id = c.id
            LEFT JOIN bets b ON l.id = b.lot_id
            WHERE l.id = $lotId
            GROUP BY l.id; ";
    $result = mysqli_query($con, $sql);
    if (!$result) {
        error_log(mysqli_error($con));
        die("Внутренняя ошибка сервера");
    }
    $lot = mysqli_fetch_assoc($result);
    return $lot ?? false;
}

function getBetsByLotID(mysqli $con, $lotId): array|false
{
    $sql = "SELECT b.price, b.created_at, u.name AS user_name, u.id as user_id
            FROM bets b
            JOIN users u ON u.id = user_id
            WHERE lot_id = $lotId
            ORDER BY b.created_at DESC;";


    $result = mysqli_query($con, $sql);
    if (!$result) {
        error_log(mysqli_error($con));
        die("Внутренняя ошибка сервера");
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getUsersByEmail(mysqli $con, string $email): array|false
{
    $sql = "SELECT id, password, name, email FROM users WHERE email = ?";
    $stmt = dbGetPrepareStmt($con, $sql, [$email]);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    return $user ?? false;
}
