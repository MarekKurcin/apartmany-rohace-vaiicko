-- =====================================================
-- SEED DATA - Apartmány pod Roháčmi
-- Testovacia databáza pre oblasť Západných Tatier
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Vymazanie existujúcich dát (v správnom poradí)
TRUNCATE TABLE `review`;
TRUNCATE TABLE `reservation`;
TRUNCATE TABLE `accommodation_attraction`;
TRUNCATE TABLE `accommodation_image`;
TRUNCATE TABLE `accommodation`;
TRUNCATE TABLE `attraction`;
TRUNCATE TABLE `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. POUŽÍVATELIA
-- Heslo pre všetkých: password
-- =====================================================

INSERT INTO `users` (`id`, `email`, `heslo`, `meno`, `priezvisko`, `telefon`, `rola`) VALUES
-- Administrátor
(1, 'admin@apartmany.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Systém', '+421900000001', 'admin'),

-- Ubytovatelia
(2, 'jan.horvat@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ján', 'Horváth', '+421905123456', 'ubytovatel'),
(3, 'maria.kovacova@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mária', 'Kováčová', '+421907234567', 'ubytovatel'),
(4, 'peter.novak@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Peter', 'Novák', '+421911345678', 'ubytovatel'),
(5, 'anna.kralova@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Anna', 'Kráľová', '+421915456789', 'ubytovatel'),
(6, 'milan.toth@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Milan', 'Tóth', '+421917567890', 'ubytovatel'),

-- Turisti
(7, 'lucia.nemcova@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lucia', 'Nemcová', '+421902111222', 'turista'),
(8, 'martin.baran@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Martin', 'Baran', '+421903222333', 'turista'),
(9, 'eva.szabova@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eva', 'Szabová', '+421904333444', 'turista'),
(10, 'tomas.molnar@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tomáš', 'Molnár', '+421906444555', 'turista'),
(11, 'zuzana.polakova@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zuzana', 'Poláková', '+421908555666', 'turista'),
(12, 'jakub.varga@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jakub', 'Varga', '+421909666777', 'turista'),
(13, 'katarina.kiss@email.sk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Katarína', 'Kiss', '+421910777888', 'turista');

-- =====================================================
-- 2. ATRAKCIE
-- Skutočné atrakcie v oblasti Roháčov a okolia
-- =====================================================

INSERT INTO `attraction` (`id`, `nazov`, `popis`, `typ`, `cena`, `poloha`, `obrazok`) VALUES
(1, 'Roháčske plesá',
'Skupina piatich horských jazier v Roháčskej doline. Prvé Roháčske pleso je najväčšie a najkrajšie. Túra vedie krásnou dolinou lemovanou kosodrevinou a skalnými stenami. Náročnosť: stredná, dĺžka túry cca 4-5 hodín.',
'Príroda', 0, 'Roháčska dolina, Západné Tatry',
'https://images.unsplash.com/photo-1439066615861-d1af74d74000?w=800'),

(2, 'Volovec (2063 m)',
'Najvyšší vrch slovenských Západných Tatier. Z vrcholu sa ponúka výhľad na celé Tatry, Oravu aj Liptov. Túra zo Zverovky cez Roháčsku dolinu trvá približne 5-6 hodín.',
'Turistika', 0, 'Západné Tatry',
'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800'),

(3, 'Juráňova dolina',
'Malebná dolina s náučným chodníkom. Ľahká turistika vhodná pre rodiny s deťmi. Na konci doliny sa nachádza chata s občerstvením.',
'Turistika', 0, 'Zuberec - Juráňova dolina',
'https://images.unsplash.com/photo-1501554728187-ce583db33af7?w=800'),

(4, 'Smutná dolina',
'Jedna z najkrajších dolín Západných Tatier. Vedie cez ňu turistická trasa na Baníkov a Tri kopy. Počas túry uvidíte vodopády a alpínske lúky.',
'Turistika', 0, 'Smutná dolina, Západné Tatry',
'https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=800'),

(5, 'Osobitá (1687 m)',
'Dominantný vrch nad Zubercom. Nenáročná túra s krásnymi výhľadmi. Ideálne pre začínajúcich turistov a rodiny. Dĺžka túry cca 3 hodiny.',
'Turistika', 0, 'Zuberec - Osobitá',
'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800'),

(6, 'Meander Park Oravice',
'Termálne kúpalisko s celoročnou prevádzkou. Bazény s termálnou vodou, tobogány, wellness centrum. Teplota vody 30-38°C. Ideálne na relax po náročnej túre.',
'Wellness', 25, 'Oravice 290, 027 32 Vitanová',
'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800'),

(7, 'Thermal Park Bešeňová',
'Najväčší aquapark na Liptove. Termálne bazény, vlnový bazén, tobogány pre deti aj dospelých. Celoročná prevádzka, wellness služby.',
'Wellness', 30, 'Bešeňová 136, 034 83 Bešeňová',
'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800'),

(8, 'Múzeum oravskej dediny',
'Skanzen v Zuberci s autentickými drevenými stavbami z Oravy. Ukážky tradičných remesiel, salašníctva a života na dedine. Vstup s výkladom.',
'Kultúra', 8, 'Brestová 1354, 027 32 Zuberec',
'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?w=800'),

(9, 'Oravský hrad',
'Jeden z najkrajších hradov na Slovensku. Národná kultúrna pamiatka na vápencovom brale nad riekou Orava. Prehliadky s výkladom, nočné prehliadky.',
'Kultúra', 10, 'Oravský Podzámok 1, 027 41',
'https://images.unsplash.com/photo-1599946347371-68eb71b16afc?w=800'),

(10, 'Vlkolínec',
'Pamiatka UNESCO - zachovaná ľudová architektúra. Obec s drevenými domami z 19. storočia. Živé múzeum horského osídlenia.',
'Kultúra', 3, 'Vlkolínec, 034 03 Ružomberok',
'https://images.unsplash.com/photo-1573843981267-be1999ff37cd?w=800'),

(11, 'Ski Brestová',
'Lyžiarske stredisko nad Zubercom. 3 vleky, 4 km zjazdoviek, večerné lyžovanie. Ski servis, požičovňa, lyžiarska škola.',
'Lyžovanie', 28, 'Brestová, 027 32 Zuberec',
'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800'),

(12, 'Ski Roháče - Spálená',
'Moderné lyžiarske stredisko s umelým zasnežovaním. 7 km zjazdoviek rôznych obtiažností. Snowpark, detský areál.',
'Lyžovanie', 35, 'Spálená dolina, 027 32 Zuberec',
'https://images.unsplash.com/photo-1565992441121-4367c2967103?w=800'),

(13, 'Lanovka Chopok',
'Kabínková lanovka na Chopok. Výhľad na Nízke Tatry a okolie. V lete turistika, v zime lyžovanie. Reštaurácia na vrchole.',
'Adrenalín', 22, 'Jasná, Demänovská Dolina',
'https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?w=800'),

(14, 'Rafting Dunajec',
'Splav rieky Dunajec na tradičných pltiach. Cesta prielomom Pienin s výhľadmi na Tri koruny. Nezabudnuteľný zážitok.',
'Adrenalín', 25, 'Červený Kláštor, Pieniny',
'https://images.unsplash.com/photo-1504851149312-7a075b496cc7?w=800'),

(15, 'Chodník korunami stromov',
'Jedinečný drevený chodník vo výške korún stromov v Bachledovej doline. Vyhliadková veža, edukatívne tabule.',
'Príroda', 18, 'Bachledova dolina, Ždiar',
'https://images.unsplash.com/photo-1448375240586-882707db888b?w=800');

-- =====================================================
-- 3. UBYTOVANIA
-- Reálne lokality v oblasti Roháčov
-- =====================================================

INSERT INTO `accommodation` (`id`, `user_id`, `nazov`, `popis`, `adresa`, `kapacita`, `cena_za_noc`, `vybavenie`, `obrazok`, `aktivne`) VALUES

(1, 2, 'Chata Roháčka',
'Útulná horská chata s výhľadom na Roháčske plesá. Ideálne východisko na túry do Západných Tatier. Chata je kompletne zariadená, vrátane plne vybavenej kuchyne. K dispozícii je krb, sauna a vonkajšie posedenie s grilom. Parkovanie priamo pri chate.',
'Zverovka 145, 027 32 Zuberec', 8, 120.00,
'WiFi, Parkovisko, Kuchyňa, Krb, Sauna, Gril, TV, Práčka',
'https://images.unsplash.com/photo-1449158743715-0a90ebb6d2d8?w=800', 1),

(2, 2, 'Apartmán Tatranský sen',
'Moderný apartmán v centre Zuberca s kompletným vybavením. Blízko lyžiarskeho strediska Brestová a termálneho kúpaliska Oravice. Vhodný pre rodiny aj páry. Balkón s výhľadom na hory.',
'Hlavná 234, 027 32 Zuberec', 4, 75.00,
'WiFi, Parkovisko, Kuchyňa, TV, Balkón, Práčka',
'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800', 1),

(3, 3, 'Drevenica pod Roháčmi',
'Tradičná oravská drevenica s moderným interiérom. Autentický zážitok z pobytu v horách s komfortom 21. storočia. Veľká záhrada s detským ihriskom a trampolínou. Ideálne pre rodiny s deťmi.',
'Habovka 89, 027 32 Habovka', 10, 150.00,
'WiFi, Parkovisko, Kuchyňa, Záhrada, Detské ihrisko, Krb, TV, Práčka, Gril',
'https://images.unsplash.com/photo-1510798831971-661eb04b3739?w=800', 1),

(4, 3, 'Horský apartmán Oravice',
'Luxusný apartmán len 5 minút od termálneho kúpaliska Oravice. Moderné vybavenie, priestranná terasa s jacuzzi. Ideálne pre wellness pobyt v lone prírody.',
'Oravice 56, 027 32 Vitanová', 6, 180.00,
'WiFi, Parkovisko, Kuchyňa, Jacuzzi, Terasa, TV, Klimatizácia',
'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800', 1),

(5, 4, 'Chata u Medveďa',
'Rozľahlá chata pre väčšie skupiny priamo pod lesom. Perfektné miesto pre oslavy, firemné akcie alebo rodinné stretnutia. Veľká spoločenská miestnosť s krbom, plne vybavená kuchyňa.',
'Zuberec 567, 027 32 Zuberec', 16, 280.00,
'WiFi, Parkovisko, Kuchyňa, Krb, Spoločenská miestnosť, TV, Gril, Záhrada',
'https://images.unsplash.com/photo-1542718610-a1d656d1884c?w=800', 1),

(6, 4, 'Studio Zverovka',
'Menšie štúdio pre páry alebo jednotlivcov hľadajúcich pokoj a súkromie. Minimalistický dizajn, maximálny komfort. Priamo na turistickej trase do Roháčskej doliny.',
'Zverovka 23, 027 32 Zuberec', 2, 55.00,
'WiFi, Parkovisko, Kuchynka, TV',
'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800', 1),

(7, 5, 'Vila Roháče',
'Elegantná vila s 5 spálňami a vlastným wellness. Prémiové ubytovanie pre náročných hostí. Panoramatický výhľad na Západné Tatry, súkromná sauna a vírivka.',
'Pribylina 123, 032 42 Pribylina', 12, 350.00,
'WiFi, Parkovisko, Kuchyňa, Sauna, Vírivka, Krb, TV, Klimatizácia, Záhrada, Terasa',
'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800', 1),

(8, 5, 'Apartmán Smreková',
'Príjemný apartmán obklopený smrekovým lesom. Tiché prostredie, čistý vzduch a pohoda. Vhodný pre rodinu alebo skupinu priateľov. Blízko cyklotrás.',
'Liptovský Trnovec 45, 032 42 Liptovský Trnovec', 6, 90.00,
'WiFi, Parkovisko, Kuchyňa, TV, Balkón, Bicykle',
'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=800', 1),

(9, 6, 'Penzión Brestová',
'Rodinný penzión pri lyžiarskom stredisku Brestová. Ski-in/ski-out v zimnej sezóne. Raňajky v cene, lyžiareň, sušiareň na výstroj.',
'Brestová 1, 027 32 Zuberec', 20, 45.00,
'WiFi, Parkovisko, Raňajky, Lyžiareň, Sušiareň, TV, Reštaurácia',
'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800', 1),

(10, 6, 'Chalupa Západné Tatry',
'Autentická horská chalupa s históriou. Zachovaný pôvodný ráz s modernými prvkami komfortu. Veľká terasa s grilovacím kútom a výhľadom do doliny.',
'Zuberec 789, 027 32 Zuberec', 8, 130.00,
'WiFi, Parkovisko, Kuchyňa, Krb, Terasa, Gril, TV',
'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=800', 1);

-- =====================================================
-- 4. PREPOJENIE UBYTOVANÍ S ATRAKCIAMI
-- =====================================================

INSERT INTO `accommodation_attraction` (`accommodation_id`, `attraction_id`, `vzdialenost_km`) VALUES
-- Chata Roháčka (id 1)
(1, 1, 3.5),
(1, 2, 8.0),
(1, 3, 4.0),
(1, 6, 8.0),
(1, 8, 2.0),
(1, 11, 3.0),

-- Apartmán Tatranský sen (id 2)
(2, 1, 5.0),
(2, 5, 3.0),
(2, 6, 10.0),
(2, 8, 1.0),
(2, 11, 4.0),

-- Drevenica pod Roháčmi (id 3)
(3, 1, 6.0),
(3, 5, 4.0),
(3, 6, 5.0),
(3, 8, 3.0),
(3, 11, 5.0),

-- Horský apartmán Oravice (id 4)
(4, 6, 0.5),
(4, 1, 10.0),
(4, 8, 8.0),
(4, 11, 10.0),

-- Chata u Medveďa (id 5)
(5, 1, 4.0),
(5, 3, 2.0),
(5, 5, 2.5),
(5, 6, 9.0),
(5, 8, 1.5),
(5, 11, 3.5),

-- Studio Zverovka (id 6)
(6, 1, 2.0),
(6, 2, 6.0),
(6, 4, 3.0),
(6, 11, 4.0),

-- Vila Roháče (id 7)
(7, 7, 15.0),
(7, 13, 20.0),
(7, 10, 35.0),

-- Apartmán Smreková (id 8)
(8, 7, 10.0),
(8, 13, 15.0),
(8, 10, 25.0),

-- Penzión Brestová (id 9)
(9, 11, 0.2),
(9, 12, 8.0),
(9, 1, 5.0),
(9, 8, 2.0),
(9, 6, 9.0),

-- Chalupa Západné Tatry (id 10)
(10, 1, 4.5),
(10, 3, 3.0),
(10, 5, 3.5),
(10, 8, 1.0),
(10, 11, 4.0),
(10, 6, 10.0);

-- =====================================================
-- 5. RECENZIE
-- =====================================================

INSERT INTO `review` (`user_id`, `accommodation_id`, `hodnotenie`, `komentar`, `created_at`) VALUES
-- Recenzie pre Chata Roháčka
(7, 1, 5, 'Úžasná chata s perfektným výhľadom! Sauna po túre na Roháčske plesá bola fantastická. Určite sa vrátime.', '2024-08-15 14:30:00'),
(8, 1, 4, 'Veľmi pekné miesto, len cesta autom bola trochu náročná. Inak všetko super.', '2024-09-20 10:15:00'),
(9, 1, 5, 'Najlepšie ubytovanie čo sme mali v Tatrách. Hostiteľ veľmi príjemný.', '2024-10-05 18:45:00'),

-- Recenzie pre Apartmán Tatranský sen
(10, 2, 4, 'Moderný apartmán, všetko čisté a funkčné. Dobrá poloha blízko centra.', '2024-07-22 09:00:00'),
(11, 2, 5, 'Perfektné pre pár. Balkón s výhľadom na hory bol bonus.', '2024-08-30 16:20:00'),

-- Recenzie pre Drevenicu pod Roháčmi
(7, 3, 5, 'S deťmi sme boli nadšení! Ihrisko, trampolína, veľká záhrada. Drevenica krásna.', '2024-06-10 11:30:00'),
(12, 3, 5, 'Autentický zážitok v prekrásnom prostredí. Odporúčam všetkým!', '2024-07-18 14:00:00'),
(8, 3, 4, 'Veľmi príjemné ubytovanie, trochu ďalej od obchodu, ale to nám nevadilo.', '2024-09-12 08:45:00'),

-- Recenzie pre Horský apartmán Oravice
(9, 4, 5, 'Jacuzzi na terase s výhľadom na hory - nezabudnuteľné! A termály hneď vedľa.', '2024-08-05 20:30:00'),
(10, 4, 4, 'Luxusné ubytovanie, cena zodpovedá kvalite.', '2024-10-18 12:00:00'),

-- Recenzie pre Chatu u Medveďa
(11, 5, 5, 'Mali sme tu oslavu 50-tky, všetko perfektné. Veľká spoločenská miestnosť, krb, kapacita super.', '2024-09-28 19:15:00'),
(7, 5, 4, 'Pre väčšiu skupinu ideálne. Kuchyňa dobre vybavená.', '2024-10-10 15:30:00'),

-- Recenzie pre Studio Zverovka
(12, 6, 5, 'Malé ale útulné. Pre dvoch ideálne. Ticho, pokoj, príroda.', '2024-07-05 10:00:00'),
(8, 6, 4, 'Jednoduchý, čistý, všetko čo potrebujete. Dobrá cena.', '2024-08-22 17:45:00'),

-- Recenzie pre Vilu Roháče
(9, 7, 5, 'WOW! Takéto luxusné ubytovanie som ešte nevidel. Sauna, vírivka, výhľady...', '2024-06-28 21:00:00'),
(10, 7, 5, 'Prémiová kvalita, každý detail premyslený. Za cenu dostanete maximum.', '2024-09-15 13:30:00'),

-- Recenzie pre Apartmán Smreková
(11, 8, 4, 'Príjemný apartmán v pokojnej lokalite. Dobré na relax a cyklistiku.', '2024-07-30 09:30:00'),

-- Recenzie pre Penzión Brestová
(12, 9, 4, 'Super na lyžovačku - vyskočíš z postele a si na svahu. Raňajky dobré.', '2024-02-15 08:00:00'),
(7, 9, 5, 'Najlepší penzión pre lyžiarov! Sušiareň, lyžiareň, jedlo - všetko top.', '2024-03-01 18:30:00'),
(8, 9, 4, 'V lete trochu menej atrakcií, ale ako základňa na túry výborné.', '2024-08-10 14:15:00'),

-- Recenzie pre Chalupu Západné Tatry
(9, 10, 5, 'Krásna autentická chalupa. Krb, terasa, gril - romantika!', '2024-07-12 19:00:00'),
(10, 10, 4, 'Pôvodný štýl s moderným komfortom. Veľmi príjemný pobyt.', '2024-09-05 11:45:00');

-- =====================================================
-- 6. REZERVÁCIE
-- =====================================================

INSERT INTO `reservation` (`user_id`, `accommodation_id`, `datum_od`, `datum_do`, `pocet_osob`, `celkova_cena`, `stav`) VALUES
-- Aktívne/čakajúce rezervácie (budúce dátumy)
(7, 1, '2025-02-10', '2025-02-15', 6, 600.00, 'potvrdena'),
(8, 3, '2025-02-14', '2025-02-16', 8, 300.00, 'cakajuca'),
(9, 4, '2025-03-01', '2025-03-05', 4, 720.00, 'potvrdena'),
(10, 9, '2025-02-20', '2025-02-24', 4, 180.00, 'cakajuca'),
(11, 2, '2025-03-10', '2025-03-14', 4, 300.00, 'potvrdena'),
(12, 6, '2025-02-28', '2025-03-02', 2, 110.00, 'cakajuca'),
(7, 5, '2025-04-01', '2025-04-05', 12, 1120.00, 'potvrdena'),
(8, 7, '2025-05-15', '2025-05-20', 10, 1750.00, 'cakajuca'),

-- Dokončené rezervácie (minulé dátumy)
(9, 1, '2024-08-10', '2024-08-15', 6, 600.00, 'dokoncena'),
(10, 2, '2024-07-20', '2024-07-25', 4, 375.00, 'dokoncena'),
(11, 3, '2024-06-05', '2024-06-10', 8, 750.00, 'dokoncena'),
(12, 5, '2024-09-25', '2024-09-28', 14, 840.00, 'dokoncena'),
(7, 9, '2024-02-10', '2024-02-14', 4, 180.00, 'dokoncena'),
(8, 10, '2024-07-08', '2024-07-12', 6, 520.00, 'dokoncena'),

-- Zrušená rezervácia
(9, 8, '2024-08-01', '2024-08-05', 4, 360.00, 'zrusena');

-- =====================================================
-- KONIEC SEED DATA
-- =====================================================
