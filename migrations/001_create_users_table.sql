CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    role ENUM('admin','manager','customer') NOT NULL DEFAULT 'customer',
    full_name VARCHAR(120) NOT NULL,
    username VARCHAR(40) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    gender ENUM('male','female') NULL,
    birth_date DATE NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    deleted_at DATETIME NULL,
    deleted_by INT UNSIGNED NULL,
    UNIQUE KEY uq_users_code (code),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone),
    KEY ix_users_role_active (role, is_active),
    KEY ix_users_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(160) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    KEY ix_login_attempts_lookup (identifier, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
