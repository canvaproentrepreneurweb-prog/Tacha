-- Migration TACHA v2
USE tacha_db;

ALTER TABLE users
    ADD COLUMN shop_name VARCHAR(150) NULL AFTER role,
    ADD COLUMN shop_city VARCHAR(100) NULL AFTER shop_name,
    ADD COLUMN shop_phone VARCHAR(30) NULL AFTER shop_city;

ALTER TABLE events
    ADD COLUMN ticket_template_path VARCHAR(255) NOT NULL DEFAULT 'img/Baniere_accuil.png' AFTER description,
    ADD COLUMN qr_x INT NOT NULL DEFAULT 0 AFTER ticket_template_path,
    ADD COLUMN qr_y INT NOT NULL DEFAULT 0 AFTER qr_x,
    ADD COLUMN qr_size INT NOT NULL DEFAULT 220 AFTER qr_y;

ALTER TABLE tickets
    MODIFY COLUMN user_id INT NULL,
    DROP FOREIGN KEY fk_tickets_user,
    ADD COLUMN buyer_firstname VARCHAR(120) NULL AFTER status,
    ADD COLUMN buyer_lastname VARCHAR(120) NULL AFTER buyer_firstname,
    ADD COLUMN buyer_email VARCHAR(190) NULL AFTER buyer_lastname,
    ADD COLUMN buyer_phone VARCHAR(40) NULL AFTER buyer_email,
    ADD CONSTRAINT fk_tickets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
