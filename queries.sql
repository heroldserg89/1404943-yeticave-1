USE yeticave;

INSERT IGNORE INTO categories (title, symbol_code)
VALUES ('Доски и лыжи', 'boards'),
       ('Крепления', 'attachment'),
       ('Ботинки', 'boots'),
       ('Одежда', 'clothing'),
       ('Инструменты', 'tools'),
       ('Разное', 'other');

INSERT INTO users (email, name, password, contacts)
VALUES ('herold8989@yeticave.ru', 'Сергей', 'test1',
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ac.'),
       ('heroldserg89@yeticave.ru', 'Сергей2', 'test1',
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ac.');

INSERT INTO lots (title, description, img_url, price_start, end_at, price_step, author_id, winner_id,
                  category_id)
VALUES ('2014 Rossignol District Snowboard', '', 'img/lot-1.jpg', 10999, '2026-01-15', 1000, 1, NULL, 1),

       ('DC Ply Mens 2016/2017 Snowboard', '', 'img/lot-2.jpg', 159999, '2026-01-15', 1000, 1, NULL, 1),

       ('Крепления Union Contact Pro 2015 года размер L/XL', '', 'img/lot-3.jpg', 8000, '2026-01-15', 1000, 2, NULL, 2),

       ('Ботинки для сноуборда DC Mutiny Charcoal', '', 'img/lot-4.jpg', 10999, '2026-01-15', 1000, 2, NULL, 3),

       ('Куртка для сноуборда DC Mutiny Charcoal', '', 'img/lot-5.jpg', 7500, '2026-01-15', 1000, 1, NULL, 4),

       ('Маска Oakley Canopy', '', 'img/lot-6.jpg', 5400, '2026-01-15', 1000, 1, NULL, 6);

INSERT INTO bets (price, user_id, lot_id)
VALUES (9000, 1, 3);
INSERT INTO bets (created_at, price, user_id, lot_id)
VALUES (DATE_ADD(NOW(), INTERVAL 1 HOUR), 10000, 1, 3);

# получить все категории;
SELECT *
FROM categories;

# получить самые новые, открытые лоты. Каждый лот должен включать название, стартовую цену, ссылку на изображение, цену, название категории;
SELECT l.title,
       price_start,
       img_url,
       author_id,
       COALESCE(MAX(b.price), l.price_start) AS current_price,
       c.title                               AS category
FROM lots l
       JOIN categories c ON l.category_id = c.id
       LEFT JOIN bets b ON l.id = b.lot_id
WHERE l.end_at > NOW()
GROUP BY l.id, l.created_at
ORDER BY l.created_at DESC;

# показать лот по его ID. Получите также название категории, к которой принадлежит лот;
SELECT l.*,
       c.title AS category
FROM lots l
       JOIN categories c ON l.category_id = c.id
WHERE l.id = 3;

# обновить название лота по его идентификатору;
UPDATE lots
SET title = '2014 Rossignol District Snowboard'
WHERE id = 1;

# получить список ставок для лота по его идентификатору с сортировкой по дате.
SELECT price
FROM bets
WHERE lot_id = 3
ORDER BY created_at DESC;
