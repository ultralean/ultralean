-- UP

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at VARCHAR(30) NOT NULL,
    updated_at VARCHAR(30) NOT NULL
);

INSERT INTO users (name, username, email, password_hash, status, created_at, updated_at)
VALUES ('User', 'user', 'user@example.com', {{ password('user') }}, 'active', {{ now() }}, {{ now() }});

-- DOWN

DROP TABLE users;
