-- TACHA MVP database v2
DROP DATABASE IF EXISTS tacha_db;
CREATE DATABASE tacha_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tacha_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(190) NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('participant', 'organizer', 'admin') NOT NULL DEFAULT 'participant',
    shop_name VARCHAR(150) NULL,
    shop_city VARCHAR(100) NULL,
    shop_phone VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    city VARCHAR(100) NOT NULL,
    venue VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    price INT NOT NULL,
    capacity INT NOT NULL,
    image_path VARCHAR(255) NOT NULL DEFAULT 'img/Baniere_accuil.png',
    organizer_id INT NOT NULL,
    description TEXT,
    ticket_template_path VARCHAR(255) NOT NULL DEFAULT 'img/Baniere_accuil.png',
    qr_x INT NOT NULL DEFAULT 0,
    qr_y INT NOT NULL DEFAULT 0,
    qr_size INT NOT NULL DEFAULT 220,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_organizer FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NULL,
    quantity INT NOT NULL DEFAULT 1,
    token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('valid', 'used', 'revoked') NOT NULL DEFAULT 'valid',
    buyer_firstname VARCHAR(120) NULL,
    buyer_lastname VARCHAR(120) NULL,
    buyer_email VARCHAR(190) NULL,
    buyer_phone VARCHAR(40) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tickets_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    method ENUM('mtn', 'orange', 'card', 'simu') NOT NULL DEFAULT 'simu',
    amount INT NOT NULL,
    status ENUM('success', 'failed', 'pending') NOT NULL DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ticket_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    organizer_id INT NOT NULL,
    validated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_validations_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_validations_organizer FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (name, phone, email, password_hash, role, shop_name, shop_city, shop_phone) VALUES
('Organisateur Tacha', '+237670000001', 'org@tacha.cm', '$2y$10$noaJ.XtLn3wY5.W2XkEjTOstuaH1dx1wdZ07V5cyuxUJ93swXQLl.', 'organizer', 'Tacha Store', 'Douala', '+237670000001'),
('Utilisateur Tacha', '+237670000002', 'user@tacha.cm', '$2y$10$noaJ.XtLn3wY5.W2XkEjTOstuaH1dx1wdZ07V5cyuxUJ93swXQLl.', 'participant', NULL, NULL, NULL),
('Super Admin Tacha', '+237670000003', 'admin@tacha.cm', '$2y$10$noaJ.XtLn3wY5.W2XkEjTOstuaH1dx1wdZ07V5cyuxUJ93swXQLl.', 'admin', NULL, NULL, NULL);

INSERT INTO events (title, city, venue, event_date, event_time, price, capacity, image_path, organizer_id, description, ticket_template_path, qr_x, qr_y, qr_size, is_active) VALUES
('Festival Urbain Douala 2026', 'Douala', 'Palais des Sports', '2026-04-18', '18:30:00', 5000, 1200, 'img/Baniere_accuil.png', 1, 'Une soiree live avec artistes urbains, DJ sets et animations digitales.', 'img/Baniere_accuil.png', 0, 0, 220, 1),
('Nuit Afrobeat Douala', 'Douala', 'Canal Olympia', '2026-05-09', '20:00:00', 7000, 900, 'img/Baniere_accuil.png', 1, 'Afrobeat non-stop, line-up local et experience premium.', 'img/Baniere_accuil.png', 0, 0, 220, 1),
('Tech and Music Yaounde', 'Yaounde', 'Centre des Congres', '2026-04-25', '17:00:00', 4500, 750, 'img/Baniere_accuil.png', 1, 'Conference creative suivie d un show musical et networking.', 'img/Baniere_accuil.png', 0, 0, 220, 1),
('Soiree Gospel Yaounde', 'Yaounde', 'Stade Omnisports', '2026-06-14', '16:00:00', 3000, 3000, 'img/Baniere_accuil.png', 1, 'Concert gospel communautaire avec chorales et invites.', 'img/Baniere_accuil.png', 0, 0, 220, 1),
('Campus Party Bafoussam', 'Bafoussam', 'Maison du Parti', '2026-07-03', '19:30:00', 3500, 650, 'img/Baniere_accuil.png', 1, 'Evenement etudiant: dancefloor, concours et cadeaux sponsors.', 'img/Baniere_accuil.png', 0, 0, 220, 1),
('Sunset Lounge Bafoussam', 'Bafoussam', 'Espace Monts', '2026-08-22', '18:00:00', 6000, 500, 'img/Baniere_accuil.png', 1, 'Ambiance lounge, food court et performances acoustiques.', 'img/Baniere_accuil.png', 0, 0, 220, 1);
