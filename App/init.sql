-- Apartmány pod Roháčmi - Databázová schéma
-- MySQL/MariaDB

-- Nastavenie kódovania
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Vymazanie existujúcich tabuliek (ak existujú)
DROP TABLE IF EXISTS `review`;
DROP TABLE IF EXISTS `accommodation_attraction`;
DROP TABLE IF EXISTS `reservation`;
DROP TABLE IF EXISTS `attraction`;
DROP TABLE IF EXISTS `accommodation`;
DROP TABLE IF EXISTS `users`;

-- Tabuľka používateľov
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `heslo` VARCHAR(255) NOT NULL,
    `meno` VARCHAR(255),
    `priezvisko` VARCHAR(255),
    `telefon` VARCHAR(50),
    `rola` ENUM('turista', 'ubytovatel', 'admin') NOT NULL DEFAULT 'turista',
    `datum_vytvorenia` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabuľka ubytovaní
CREATE TABLE `accommodation` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `nazov` VARCHAR(255) NOT NULL,
    `popis` TEXT,
    `adresa` VARCHAR(255),
    `kapacita` INT,
    `cena_za_noc` DECIMAL(10,2),
    `vybavenie` TEXT,
    `obrazok` TEXT,
    `aktivne` TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabuľka rezervácií
CREATE TABLE `reservation` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `accommodation_id` INT NOT NULL,
    `datum_od` DATE NOT NULL,
    `datum_do` DATE NOT NULL,
    `pocet_osob` INT,
    `celkova_cena` DECIMAL(10,2),
    `stav` ENUM('cakajuca', 'potvrdena', 'zrusena', 'dokoncena') DEFAULT 'cakajuca',
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`accommodation_id`) REFERENCES `accommodation`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabuľka atrakcií
CREATE TABLE `attraction` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nazov` VARCHAR(255) NOT NULL,
    `popis` TEXT,
    `typ` VARCHAR(50),
    `cena` INT DEFAULT 0,
    `poloha` VARCHAR(255),
    `obrazok` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prepojovacia tabuľka medzi ubytovaním a atrakciami
CREATE TABLE `accommodation_attraction` (
    `accommodation_id` INT NOT NULL,
    `attraction_id` INT NOT NULL,
    `vzdialenost_km` DECIMAL(5,2),
    PRIMARY KEY (`accommodation_id`, `attraction_id`),
    FOREIGN KEY (`accommodation_id`) REFERENCES `accommodation`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`attraction_id`) REFERENCES `attraction`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabuľka hodnotení
CREATE TABLE `review` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `accommodation_id` INT NOT NULL,
    `hodnotenie` INT CHECK (`hodnotenie` >= 1 AND `hodnotenie` <= 5),
    `komentar` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`accommodation_id`) REFERENCES `accommodation`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vloženie testovacích dát

-- Testovacie účty (heslo pre všetkých je: password)
INSERT INTO `users` (`email`, `heslo`, `meno`, `priezvisko`, `telefon`, `rola`) VALUES
('admin@apartmany.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Systemu', '+421900000000', 'admin'),
('ubytovatel@test.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jan', 'Novak', '+421901234567', 'ubytovatel'),
('turista@test.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter', 'Horvat', '+421909876543', 'turista');

-- Testovacie ubytovanie
INSERT INTO `accommodation` (`user_id`, `nazov`, `popis`, `adresa`, `kapacita`, `cena_za_noc`, `vybavenie`, `aktivne`) VALUES
(2, 'Chata Rohace', 'Utulna horska chata s vyhladom na Rohace', 'Zuberec 123', 6, 89.00, 'WiFi, Parkovisko, Krb, TV, Kuchyna', 1),
(2, 'Apartman Zuberec', 'Moderny apartman v centre obce', 'Zuberec 456', 4, 65.00, 'WiFi, Parkovisko, TV, Kuchyna, Balkon', 1);

-- Testovacie atrakcie
INSERT INTO `attraction` (`nazov`, `popis`, `typ`, `cena`, `poloha`, `obrazok`) VALUES
('Rohacske plesa', 'Nadherne horske plesa v srdci Zapadnych Tatier', 'Turistika', 0, 'Zuberec', NULL),
('Ski Zuberec', 'Lyziarske stredisko s modernymi vlekmi', 'Lyžovanie', 15, 'Zuberec', NULL),
('Muzeum oravskej dediny', 'Skanzen prezentujuci tradicnu oravsku architekturu', 'Kultúra', 5, 'Zuberec', NULL);

-- Prepojenia ubytovanie-atrakcie
INSERT INTO `accommodation_attraction` (`accommodation_id`, `attraction_id`, `vzdialenost_km`) VALUES
(1, 1, 5.0),
(1, 2, 3.5),
(2, 1, 6.2),
(2, 3, 2.0);
