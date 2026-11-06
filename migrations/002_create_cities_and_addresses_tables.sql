CREATE TABLE cities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(80) NOT NULL,
    country_code CHAR(2) NOT NULL DEFAULT 'BH',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    deleted_at DATETIME NULL,
    deleted_by INT UNSIGNED NULL,
    UNIQUE KEY uq_cities_code (code),
    KEY ix_cities_name_country (name, country_code),
    KEY ix_cities_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    city_id INT UNSIGNED NOT NULL,
    street VARCHAR(120) NOT NULL,
    building VARCHAR(20) NULL,
    block VARCHAR(20) NULL,
    area VARCHAR(80) NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    deleted_at DATETIME NULL,
    deleted_by INT UNSIGNED NULL,
    UNIQUE KEY uq_addresses_code (code),
    KEY ix_addresses_city (city_id),
    KEY ix_addresses_deleted (deleted_at),
    CONSTRAINT fk_addresses_city FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
