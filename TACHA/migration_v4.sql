-- Migration TACHA v4 - Actions admin
USE tacha_db;

ALTER TABLE events
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER qr_size;

ALTER TABLE tickets
    MODIFY COLUMN status ENUM('valid', 'used', 'revoked') NOT NULL DEFAULT 'valid';
