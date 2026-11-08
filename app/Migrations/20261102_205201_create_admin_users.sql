-- UP

CREATE TABLE admin_accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at VARCHAR(30) NOT NULL,
    updated_at VARCHAR(30) NOT NULL
);

INSERT INTO admin_accounts (name, username, email, password_hash, status, created_at, updated_at)
VALUES ('Administrator', 'admin', 'admin@example.com', {{ password('admin') }}, 'active', {{ now() }}, {{ now() }});

-- DOWN

DROP TABLE admin_accounts;
