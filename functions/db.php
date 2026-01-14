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
    return $con;
}


function getCategories(mysqli $con): array
{
    $sql = 'SELECT * FROM categories';
    $result = mysqli_query($con, $sql);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getLots(mysqli $con): array
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
GROUP BY l.id, l.created_at
ORDER BY l.created_at DESC';

    $result = mysqli_query($con, $sql);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getLotById(mysqli $con, int $lotId): array
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
    return mysqli_fetch_assoc($result);
}
