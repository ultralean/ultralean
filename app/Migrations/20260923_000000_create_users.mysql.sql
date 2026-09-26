-- UP

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at VARCHAR(30) NOT NULL,
    updated_at VARCHAR(30) NOT NULL
);

INSERT INTO users (name, username, email, password_hash, created_at, updated_at)
VALUES ('Administrator', 'admin', 'admin@example.com', {{ password('admin') }}, '2026-09-24T00:00:00Z', '2026-09-24T00:00:00Z');

-- DOWN

DROP TABLE users;
