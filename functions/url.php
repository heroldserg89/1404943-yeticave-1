<?php

/**
 * Возвращает базовый URL текущего веб-сайта
 *
 * Функция определяет протокол (HTTP/HTTPS), домен и базовый путь до директории,
 * содержащей текущий скрипт. Используется для генерации абсолютных URL-адресов
 * ресурсов сайта (стилей, скриптов, изображений и т.д.)
 *
 * Примеры возвращаемых значений:
 * - https://example.com
 * - http://localhost/project
 * - https://site.com/subdirectory
 *
 * @return string Базовый URL сайта в формате "протокол://домен[/путь]"
 */
function getBaseUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

    $host = $_SERVER['HTTP_HOST'];

    return $protocol . $host;
}
