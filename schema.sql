CREATE DATABASE IF NOT EXISTS yeticave
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;
USE yeticave;

CREATE TABLE categories
(
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(80) NOT NULL,
  symbol_code VARCHAR(50),
  UNIQUE INDEX idx_symbol_cole (symbol_code),
  UNIQUE INDEX idx_title (title)
);

CREATE TABLE users
(
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  email      VARCHAR(80) NOT NULL,
  name       VARCHAR(80) NOT NULL,
  password   CHAR(60)    NOT NULL,
  contacts   TEXT        NOT NULL,
  UNIQUE INDEX idx_email (email)
);

CREATE TABLE lots
(
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  title       VARCHAR(80)  NOT NULL,
  description TEXT         NOT NULL,
  img_url     VARCHAR(255) NOT NULL,
  price_start INT          NOT NULL,
  end_at      DATE         NOT NULL,
  price_step  INT UNSIGNED NOT NULL,
  author_id   INT UNSIGNED NOT NULL,
  winner_id   INT UNSIGNED NULL,
  category_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (author_id) REFERENCES users (id),
  FOREIGN KEY (winner_id) REFERENCES users (id),
  FOREIGN KEY (category_id) REFERENCES categories (id),
  INDEX idx_title (title),
  INDEX idx_created_ad (created_at)
);

CREATE TABLE bets
(
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  price      INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  lot_id     INT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users (id),
  FOREIGN KEY (lot_id) REFERENCES lots (id),
  INDEX idx_created_at (created_at)
);

