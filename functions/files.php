<?php
/**
 * Загружает и валидирует изображение на сервер
 *
 * Функция выполняет загрузку файла изображения с проверкой:
 * 1. Проверяет, был ли загружен файл
 * 2. Определяет MIME-тип файла
 * 3. Проверяет разрешенные форматы (JPEG, PNG)
 * 4. Создает директорию для загрузки при необходимости
 * 5. Генерирует уникальное имя файла
 * 6. Перемещает файл в целевую директорию
 *
 * @param array $file Массив с данными загруженного файла из $_FILES['имя_поля']
 * @param string $uploadDir Путь к директории для сохранения загруженных файлов
 *                          (относительно корня скрипта)
 *
 * @return array Результат операции загрузки
 *  Структура возвращаемого массива:
 *  [
 *      'success' => bool,     // true если загрузка успешна, false при ошибке
 *      'path' => ?string,     // Относительный путь к загруженному файлу (только при успехе)
 *      'error' => ?string     // Сообщение об ошибке (только при неудаче)
 *  ]
 */
function uploadImage(array $file, string $uploadDir): array
{
    $result = [
        'success' => false,
        'path' => null,
        'error' => null
    ];

    $allowedTypes = [
        'image/jpeg' => '.jpg',
        'image/png' => '.png'
    ];


    if (empty($file['name'])) {
        $result['error'] = 'Вы не загрузили файл';
        return $result;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedTypes[$fileType])) {
        $result['error'] = 'Загрузите картинку в формате JPEG и PNG';
        return $result;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid() . $allowedTypes[$fileType];
    $uploadPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $result['error'] = 'Ошибка при загрузке файла';
        return $result;
    }

    $result['success'] = true;
    $result['path'] = 'uploads/' . $filename;
    return $result;
}
