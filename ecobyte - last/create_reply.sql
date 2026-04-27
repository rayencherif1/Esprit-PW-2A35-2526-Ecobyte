CREATE DATABASE IF NOT EXISTS ecobyte;
USE ecobyte;

CREATE TABLE IF NOT EXISTS reply (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  contenu TEXT NOT NULL,
  image VARCHAR(512) NULL DEFAULT NULL COMMENT 'Chemin ou URL de l image',
  datePublication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  post_id INT UNSIGNED NOT NULL,
  likes INT UNSIGNED NOT NULL DEFAULT 0,
  parent_reply_id INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_reply_post_id (post_id),
  KEY idx_reply_parent_id (parent_reply_id),
  CONSTRAINT fk_reply_post FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SHOW TABLES LIKE 'reply';

