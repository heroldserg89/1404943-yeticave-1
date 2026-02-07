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

/**
 * Устанавливает соединение с базой данных MySQL
 *
 * Функция создает подключение к базе данных используя переданные параметры,
 * устанавливает кодировку UTF-8 и временную зону сервера.
 * При неудачном подключении логирует ошибку и завершает выполнение скрипта.
 *
 * @param array $config Ассоциативный массив с параметрами подключения:
 *                     - 'host' => хост базы данных
 *                     - 'user' => имя пользователя
 *                     - 'password' => пароль пользователя
 *                     - 'database' => имя базы данных
 *
 * @return mysqli Объект подключения к базе данных MySQL
 */
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

/**
 * Получает все категории из базы данных
 *
 * Функция выполняет SQL-запрос для получения всех записей из таблицы категорий.
 * В случае ошибки выполнения запроса логирует ошибку и завершает выполнение скрипта.
 *
 * @param mysqli $con Объект подключения к базе данных
 *
 * @return array|false Возвращает ассоциативный массив всех категорий
 *                    или false, если результат пустой
 */
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

/**
 * Получает список активных лотов с текущей ценой и категорией
 *
 * Функция выполняет SQL-запрос для получения активных лотов (с датой окончания в будущем),
 * включая текущую цену (максимальную ставку или стартовую цену) и название категории.
 * Результат ограничивается 6 последними лотами, отсортированными по дате окончания.
 *
 * @param mysqli $con Объект подключения к базе данных
 *
 * @return array|false Возвращает ассоциативный массив лотов с полями:
 *                    - id: идентификатор лота
 *                    - title: название лота
 *                    - price_start: стартовая цена
 *                    - img_url: URL изображения
 *                    - author_id: ID автора лота
 *                    - end_at: дата окончания торгов
 *                    - current_price: текущая цена (макс. ставка или стартовая)
 *                    - category: название категории
 *                    Возвращает false, если результат пустой
 */
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

/**
 * Получает информацию о конкретном лоте по его идентификатору
 *
 * Функция выполняет SQL-запрос для получения данных лота с расчетом текущей цены
 * и минимальной следующей ставки. Включает название категории лота.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param int $lotId Идентификатор лота для поиска
 *
 * @return array|false Возвращает ассоциативный массив с данными лота или false,
 *                    если лот не найден. Массив содержит поля:
 *                    - все поля из таблицы lots
 *                    - category_name: название категории
 *                    - current_price: текущая цена (максимальная ставка или стартовая)
 *                    - min_bid: минимальная следующая ставка (текущая цена + шаг ставки)
 */
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

/**
 * Получает все ставки для конкретного лота
 *
 * Функция выполняет SQL-запрос для получения истории ставок по указанному лоту,
 * включая информацию о пользователях, сделавших ставки.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param int $lotId Идентификатор лота для получения ставок
 *
 * @return array|false Возвращает ассоциативный массив ставок или false,
 *                    если ставки не найдены. Каждая ставка содержит:
 *                    - price: сумма ставки
 *                    - created_at: дата и время ставки
 *                    - user_name: имя пользователя
 *                    - user_id: идентификатор пользователя
 */
function getBetsByLotID(mysqli $con, int $lotId): array|false
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
    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? false;
}

/**
 * Находит пользователя в базе данных по email
 *
 * Функция выполняет безопасный подготовленный SQL-запрос
 * для поиска пользователя по email адресу.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param string $email Email адрес для поиска пользователя
 *
 * @return array|false Возвращает ассоциативный массив с данными пользователя или false,
 *                    если пользователь не найден. Массив содержит поля:
 *                    - id: идентификатор пользователя
 *                    - password: хеш пароля пользователя
 *                    - name: имя пользователя
 *                    - email: email пользователя
 */
function getUsersByEmail(mysqli $con, string $email): array|false
{
    $sql = "SELECT id, password, name, email FROM users WHERE email = ?";
    $stmt = dbGetPrepareStmt($con, $sql, [$email]);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    return $user ?? false;
}
/**
 * Получает данные пользователя по его ID
 *
 * Функция выполняет безопасный запрос к базе данных с использованием подготовленных выражений
 * для получения информации о пользователе по его уникальному идентификатору.
 *
 * @param mysqli $con Объект соединения с базой данных MySQLi
 * @param int $id Уникальный идентификатор пользователя
 *
 * @return array|false Возвращает ассоциативный массив с данными пользователя или false, если пользователь не найден.
 */
function getUsersById(mysqli $con, int $id): array|false
{
    $sql = "SELECT id, name, email FROM users WHERE id = ?";
    $stmt = dbGetPrepareStmt($con, $sql, [$id]);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    return $user ?? false;
}

/**
 * Получает категорию по её идентификатору
 *
 * Функция выполняет SQL-запрос для получения данных категории по указанному ID.
 * В случае ошибки выполнения запроса логирует ошибку и завершает выполнение скрипта.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param int $categoryId Идентификатор категории для поиска
 *
 * @return array|false Возвращает ассоциативный массив с данными категории или false,
 *                    если категория не найдена
 */
function getCategoryById(mysqli $con, int $categoryId): array|false
{
    $sql = "SELECT * FROM categories
            WHERE id = $categoryId";
    $result = mysqli_query($con, $sql);
    if (!$result) {
        error_log(mysqli_error($con));
        die("Внутренняя ошибка сервера");
    }
    return mysqli_fetch_assoc($result) ?? false;
}

/**
 * Добавляет новый лот в базу данных
 *
 * Функция создает новый лот на основе данных формы и информации о пользователе.
 * При успешном добавлении перенаправляет на страницу созданного лота.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param array $formInputs Данные формы создания лота, должны содержать:
 *                         - title: название лота
 *                         - category_id: идентификатор категории
 *                         - description: описание лота
 *                         - price_start: стартовая цена
 *                         - price_step: шаг ставки
 *                         - end_at: дата окончания торгов
 *                         - img_url: URL изображения лота
 * @param array $user Массив данных пользователя, должен содержать:
 *                   - id: идентификатор пользователя (автора лота)
 *
 * @return void При успешном добавлении выполняет редирект на страницу лота
 */
function addLot(mysqli $con, array $formInputs, array $user): void
{
    $formInputs['author_id'] = $user['id'];
    $sql = "INSERT INTO lots (title, category_id, description, price_start, price_step, end_at, img_url, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = dbGetPrepareStmt($con, $sql, $formInputs);
    $res = mysqli_stmt_execute($stmt);
    if ($res) {
        $lotId = mysqli_insert_id($con);
        header('location: lot.php?id=' . $lotId);
        exit;
    }
}

/**
 * Получает информацию о пагинации
 *
 * @param mysqli $con Соединение с БД
 * @param string $sql SQL запрос для подсчёта COUNT(*)
 * @param array $params Параметры для подстановки
 * @param int $currentPage Текущая страница
 * @param int $itemsPerPage Количество элементов на странице
 * @return array Массив с ключами: ['total', 'pages', 'offset', 'pageCount']
 */
function getPagination(mysqli $con, string $sql, array $params, int $currentPage = 1, int $itemsPerPage = 12): array
{
    $stmt = dbGetPrepareStmt($con, $sql, $params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total = (int)mysqli_fetch_assoc($result)['total'];

    $pageCount = (int)ceil($total / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    $pages = range(1, $pageCount);

    return [
        'pages' => $pages,
        'offset' => $offset
    ];
}

/**
 * Получает данные пагинации для результатов поиска лотов
 *
 * Функция выполняет SQL-запрос для подсчета общего количества лотов,
 * соответствующих поисковому запросу, и рассчитывает данные пагинации.
 * Использует полнотекстовый поиск по названию и описанию лотов.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param string $search Поисковый запрос для фильтрации лотов
 * @param int $curPage Текущая страница пагинации
 * @param int $pageItems Количество элементов на странице
 *
 * @return array Массив с данными пагинации, содержащий:
 *              - total: общее количество найденных лотов
 *              - current_page: текущая страница
 *              - page_items: количество элементов на странице
 *              - pages: общее количество страниц
 *              - offset: смещение для SQL-запроса LIMIT
 */
function getSearchedLotsPagination(mysqli $con, string $search, int $curPage, int $pageItems): array
{
    $sql = 'SELECT COUNT(DISTINCT l.id) as total FROM lots l
            WHERE l.end_at > CURDATE()
            AND MATCH(l.title, l.description) AGAINST(?)';
    return getPagination($con, $sql, [$search], $curPage, $pageItems);
}

/**
 * Получает данные пагинации для лотов определенной категории
 *
 * Функция выполняет SQL-запрос для подсчета общего количества активных лотов
 * в указанной категории и рассчитывает данные пагинации.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param int $categoryId Идентификатор категории для фильтрации лотов
 * @param int $curPage Текущая страница пагинации
 * @param int $pageItems Количество элементов на странице
 *
 * @return array Массив с данными пагинации, содержащий:
 *              - total: общее количество лотов в категории
 *              - current_page: текущая страница
 *              - page_items: количество элементов на странице
 *              - pages: общее количество страниц
 *              - offset: смещение для SQL-запроса LIMIT
 */
function getLotsByCategoryPagination(mysqli $con, int $categoryId, int $curPage, int $pageItems): array
{
    $sql = 'SELECT COUNT(DISTINCT l.id) as total FROM lots l
            WHERE l.category_id = ? AND l.end_at > CURDATE()';
    return getPagination($con, $sql, [$categoryId], $curPage, $pageItems);
}

/**
 * Выполняет SQL-запрос с параметрами для получения лотов с пагинацией
 *
 * Функция использует подготовленные выражения для безопасного выполнения
 * SQL-запроса с передачей параметров, размера страницы и смещения.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param string $sql SQL-запрос с плейсхолдерами для параметров
 * @param mixed $params Параметры для SQL-запроса (строка или число)
 * @param int $pageItems Количество элементов на странице для пагинации
 * @param int $offset Смещение для SQL-запроса (LIMIT)
 *
 * @return array Массив ассоциативных массивов с данными лотов
 */
function getLotsParameter(mysqli $con, string $sql, $params, $pageItems, $offset): array
{

    $stmt = dbGetPrepareStmt($con, $sql, [
        $params,
        $pageItems,
        $offset
    ]);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Получает лоты по поисковому запросу с поддержкой пагинации
 *
 * Функция выполняет полнотекстовый поиск по названию и описанию лотов
 * с использованием булевого режима и возвращает результаты с учетом пагинации.
 * Включает текущую цену (максимальную ставку или стартовую цену) и название категории.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param string $search Поисковый запрос для полнотекстового поиска
 * @param int $pageItems Количество лотов на странице
 * @param int $offset Смещение для пагинации
 *
 * @return array Массив лотов, найденных по поисковому запросу,
 *              отсортированных по дате создания (сначала новые).
 *              Каждый лот содержит:
 *              - id: идентификатор лота
 *              - title: название лота
 *              - price_start: стартовая цена
 *              - img_url: URL изображения
 *              - end_at: дата окончания торгов
 *              - category: название категории
 *              - current_price: текущая цена
 */
function getLotsBySearch(mysqli $con, string $search, int $pageItems, int $offset): array
{
    $sql = 'SELECT
        l.id,
        l.title,
        l.price_start,
        l.img_url,
        l.end_at,
         c.title AS category,
        COALESCE(MAX(b.price), l.price_start) AS current_price
    FROM lots l
    JOIN categories c ON c.id = l.category_id
    LEFT JOIN bets b ON b.lot_id = l.id
    WHERE
        MATCH(l.title, l.description) AGAINST (? IN BOOLEAN MODE)
        AND l.end_at > NOW()
    GROUP BY l.id, l.created_at
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?';
    return getLotsParameter($con, $sql, $search, $pageItems, $offset);
}

/**
 * Получает лоты по идентификатору категории с поддержкой пагинации
 *
 * Функция возвращает активные лоты, принадлежащие указанной категории,
 * с учетом пагинации. Включает текущую цену и название категории.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param int $categoryId Идентификатор категории для фильтрации лотов
 * @param int $pageItems Количество лотов на странице
 * @param int $offset Смещение для пагинации
 *
 * @return array Массив лотов указанной категории,
 *              отсортированных по дате создания (сначала новые).
 *              Каждый лот содержит:
 *              - id: идентификатор лота
 *              - title: название лота
 *              - price_start: стартовая цена
 *              - img_url: URL изображения
 *              - end_at: дата окончания торгов
 *              - category: название категории
 *              - current_price: текущая цена
 */
function getLotsByCategory(mysqli $con, int $categoryId, int $pageItems, int $offset): array
{
    $sql = 'SELECT
            l.id,
            l.title,
            l.price_start,
            l.img_url,
            l.end_at,
             c.title AS category,
            COALESCE(MAX(b.price), l.price_start) AS current_price
        FROM lots l
        JOIN categories c ON c.id = l.category_id
        LEFT JOIN bets b ON b.lot_id = l.id
        WHERE
            l.category_id = ?
            AND l.end_at > NOW()
        GROUP BY l.id, l.created_at
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?';
    return getLotsParameter($con, $sql, $categoryId, $pageItems, $offset);
}

/**
 * Добавляет новую ставку на лот в базу данных
 *
 * Функция создает новую ставку для указанного лота от конкретного пользователя
 * и перенаправляет на страницу лота после успешного добавления.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param int $lotId Идентификатор лота, на который делается ставка
 * @param int $userId Идентификатор пользователя, делающего ставку
 * @param int $price Сумма ставки
 *
 * @return void При успешном добавлении выполняет редирект на страницу лота
 */
function addBets(mysqli $con, int $lotId, int $userId, int $price): void
{
    $sql = 'INSERT INTO bets (price, user_id, lot_id) VALUES (?, ?, ?);';
    $stmt = dbGetPrepareStmt($con, $sql, [$price, $userId, $lotId]);
    mysqli_stmt_execute($stmt);
    header('location: lot.php?id=' . $lotId);
}

/**
 * Получает все ставки текущего пользователя с детальной информацией о лотах
 *
 * Функция выполняет SQL-запрос для получения полной истории ставок пользователя,
 * включая информацию о лотах, их категориях и контактах авторов лотов.
 *
 * @param mysqli $con Объект подключения к базе данных
 * @param int $userId Идентификатор пользователя, чьи ставки нужно получить
 *
 * @return array|false Возвращает ассоциативный массив ставок пользователя или false,
 *                    если ставки не найдены. Каждая ставка содержит:
 *                    - id: идентификатор ставки
 *                    - price: сумма ставки
 *                    - created_at: дата и время ставки
 *                    - lot_id: идентификатор лота
 *                    - title: название лота
 *                    - img_url: URL изображения лота
 *                    - end_at: дата окончания торгов
 *                    - winner_id: идентификатор победителя лота
 *                    - category: название категории лота
 *                    - contacts: контактная информация автора лота
 *
 * @throws RuntimeException При ошибке выполнения SQL-запроса
 */
function getMyBets(mysqli $con, int $userId): array|false
{
    $sql = "SELECT b.id,
                   b.price,
                   b.created_at,
                   l.id AS lot_id,
                   l.title,
                   l.img_url,
                   l.end_at,
                   l.winner_id,
                   c.title AS category,
                   u.contacts as contacts

            FROM bets b
            JOIN lots l ON b.lot_id = l.id
            JOIN categories c ON l.category_id = c.id
            JOIN users u ON u.id = l.author_id
            LEFT JOIN bets b2 ON l.id = b2.lot_id
            WHERE b.user_id = $userId
            GROUP BY b.id, b.created_at, l.id, l.title, l.img_url, l.end_at, l.winner_id, c.title, l.price_start
            ORDER BY b.created_at DESC";
    $result = mysqli_query($con, $sql);
    if (!$result) {
        error_log(mysqli_error($con));
        die("Внутренняя ошибка сервера");
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? false;
}

/**
 * Регистрирует нового пользователя в системе
 *
 * Функция выполняет регистрацию пользователя, хеширует пароль и сохраняет данные в БД.
 * В случае успеха перенаправляет пользователя на страницу входа.
 * При ошибке регистрации возвращает HTTP 500 и завершает выполнение скрипта.
 *
 * @param mysqli $con Объект соединения с базой данных MySQLi
 * @param array $formInputs Ассоциативный массив с данными формы регистрации
 * @return void Функция не возвращает значение, выполняет перенаправление на страницу входа
 */
function registerUser(mysqli $con, array $formInputs): void
{
    $password = password_hash($formInputs['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (email, password, name, contacts) VALUES (?, ?, ?, ?)";
    $stmt = dbGetPrepareStmt($con, $sql, [
        $formInputs['email'],
        $password,
        $formInputs['name'],
        $formInputs['message']
    ]);

    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_error($con));
        http_response_code(500);
        die("Внутренняя ошибка сервера");
    }
    header("Location: /login.php");
    exit();
}


/**
 * Получает список завершенных лотов со ставками и определяет победителей
 *
 * Функция выполняет запрос к базе данных для получения лотов, время которых истекло,
 * но победитель еще не назначен. Для каждого лота определяется максимальная ставка
 * и пользователь, сделавший эту ставку (потенциальный победитель).
 *
 * Алгоритм работы:
 * 1. Выбирает лоты с истекшим временем (end_at < NOW()), где winner_id IS NULL
 * 2. Для каждого лота находит максимальную ставку
 * 3. Определяет пользователя, сделавшего максимальную ставку (через подзапрос)
 * 4. Возвращает результаты, отсортированные по дате окончания (новые первыми)
 *
 * @param mysqli $con Объект соединения с базой данных MySQLi
 *
 * @return array|false Возвращает ассоциативный массив с результатами или false в случае ошибки.
 */

function getFinishedLotsWithBets(mysqli $con): array|false
{
    $sql = 'SELECT l.id,
               l.title,
                MAX(b.price) AS current_price,
                    (SELECT u.id FROM bets b_inner
                    JOIN users u ON u.id = b_inner.user_id
                    WHERE b_inner.lot_id = l.id
                    ORDER BY b_inner.price DESC
                    LIMIT 1) AS winner_id
                FROM lots l
                JOIN bets b ON l.id = b.lot_id
                WHERE l.end_at < NOW() and l.winner_id is null
                GROUP BY l.id, l.end_at
                ORDER BY l.end_at DESC';
    $result = mysqli_query($con, $sql);
    if (!$result) {
        error_log(mysqli_error($con));
        die("Внутренняя ошибка сервера");
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC) ?? false;
}

/**
 * Массово обновляет победителей для лотов на основе массива данных
 *
 * Функция выполняет массовое обновление поля winner_id в таблице lots
 * с использованием конструкции SQL CASE для эффективного обновления
 * нескольких записей одним запросом.
 *
 * Алгоритм работы:
 * 1. Проверяет, что входной массив не пустой
 * 2. Формирует CASE-выражения для каждого лота в формате "WHEN id THEN winner_id"
 * 3. Собирает список ID лотов для условия WHERE
 * 4. Выполняет UPDATE запрос с конструкцией CASE
 * 5. Возвращает количество обновленных строк или код ошибки
 *
 * Особенности:
 * - Обновляет только лоты, у которых winner_id еще не назначен (IS NULL)
 * - Использует подготовку данных для предотвращения SQL-инъекций через явное приведение типов
 * - Обрабатывает ошибки выполнения запроса с логированием
 *
 * @param mysqli $con Объект соединения с базой данных MySQLi
 * @param array $lots Массив лотов для обновления. Каждый элемент должен содержать:
 *                    [
 *                        'id' => int|string,        // ID лота
 *                        'winner_id' => int|string  // ID пользователя-победителя
 *                    ]
 *                    Может содержать дополнительные поля, которые игнорируются.
 *
 * @return int Возвращает:
 *            - 0, если входной массив пустой
 *            - Количество обновленных строк (>= 0), если операция успешна
 *            - -1, если произошла ошибка выполнения SQL-запроса
 */

function updateLotsWinnersFromArray(mysqli $con, array $lots): int
{
    if (empty($lots)) {
        return 0;
    }

    $cases = '';
    $ids = [];

    foreach ($lots as $lot) {
        $lotId = (int)$lot['id'];
        $winnerId = (int)$lot['winner_id'];
        $ids[] = $lotId;
        $cases .= "WHEN $lotId THEN $winnerId ";
    }

    $idList = implode(',', $ids);

    $sql = "UPDATE lots
            SET winner_id = CASE id
                $cases
            END
            WHERE id IN ($idList)
            AND winner_id IS NULL";

    if (!mysqli_query($con, $sql)) {
        error_log('Ошибка обновления лотов: ' . mysqli_error($con));
        return -1;
    }

    return mysqli_affected_rows($con);
}
