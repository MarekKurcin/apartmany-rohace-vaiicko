-- Migration: Pridanie tabuľky pre galériu obrázkov
-- Spustite tento SQL skript ak už máte existujúcu databázu

CREATE TABLE IF NOT EXISTS `accommodation_image` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `accommodation_id` INT NOT NULL,
    `image_path` VARCHAR(500) NOT NULL,
    `is_primary` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`accommodation_id`) REFERENCES `accommodation`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
