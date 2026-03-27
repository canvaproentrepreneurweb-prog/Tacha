-- Migration TACHA v3 - Espace proprietaire (admin)
USE tacha_db;

ALTER TABLE users
    MODIFY COLUMN role ENUM('participant', 'organizer', 'admin') NOT NULL DEFAULT 'participant';

INSERT INTO users (name, phone, email, password_hash, role, shop_name, shop_city, shop_phone)
SELECT 'Super Admin Tacha', '+237670000003', 'admin@tacha.cm', '$2y$10$noaJ.XtLn3wY5.W2XkEjTOstuaH1dx1wdZ07V5cyuxUJ93swXQLl.', 'admin', NULL, NULL, NULL
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@tacha.cm'
);
