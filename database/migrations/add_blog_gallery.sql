-- Mascardi Lifestyle — Blog & Gallery migration
-- Run this against an existing database that already has the base schema.
-- All statements use IF NOT EXISTS so this file is safe to re-run.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Blog: categories
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_categories (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    slug       VARCHAR(120) NOT NULL UNIQUE,
    sort_order INT          NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Blog: posts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_posts (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id      INT UNSIGNED NULL,
    author_id        INT UNSIGNED NULL,
    slug             VARCHAR(200) NOT NULL UNIQUE,
    title            VARCHAR(200) NOT NULL,
    excerpt          TEXT         NULL,
    body             LONGTEXT     NULL,
    cover_image_path VARCHAR(255) NULL,
    status           ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at     DATETIME NULL DEFAULT NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_blog_posts_category FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_blog_posts_author   FOREIGN KEY (author_id)   REFERENCES admin_users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE blog_posts ADD INDEX IF NOT EXISTS idx_blog_posts_status_published (status, published_at);

-- ---------------------------------------------------------------------
-- Gallery: albums
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gallery_albums (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug             VARCHAR(150) NOT NULL UNIQUE,
    name             VARCHAR(150) NOT NULL,
    description      TEXT         NULL,
    cover_image_path VARCHAR(255) NULL,
    sort_order       INT          NOT NULL DEFAULT 0,
    is_active        TINYINT(1)   NOT NULL DEFAULT 1,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Gallery: images (belong to an album)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gallery_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    album_id   INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption    VARCHAR(255) NULL,
    sort_order INT          NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gallery_images_album FOREIGN KEY (album_id) REFERENCES gallery_albums(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE gallery_images ADD INDEX IF NOT EXISTS idx_gallery_images_album_sort (album_id, sort_order);

SET FOREIGN_KEY_CHECKS = 1;
