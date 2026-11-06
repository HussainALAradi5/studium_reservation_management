CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    stadium_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment VARCHAR(1000) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    deleted_at DATETIME NULL,
    deleted_by INT UNSIGNED NULL,
    UNIQUE KEY uq_reviews_code (code),
    KEY ix_reviews_stadium (stadium_id, deleted_at, created_at),
    UNIQUE KEY uq_reviews_stadium_customer (stadium_id, customer_id),
    KEY ix_reviews_customer (customer_id),
    CONSTRAINT fk_reviews_stadium FOREIGN KEY (stadium_id) REFERENCES stadiums (id) ON DELETE RESTRICT,
    CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE stadiums
    ADD COLUMN rating_average DECIMAL(3,2) NOT NULL DEFAULT 0 AFTER slot_minutes,
    ADD COLUMN rating_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER rating_average;

INSERT IGNORE INTO role_permissions (code, role, permission, created_at, updated_at)
VALUES
    (CONCAT('RPM-', UPPER(SUBSTRING(MD5(RAND()), 1, 8))), 'customer', 'reviews.write', NOW(), NOW()),
    (CONCAT('RPM-', UPPER(SUBSTRING(MD5(RAND()), 1, 8))), 'manager', 'reviews.moderate', NOW(), NOW());
