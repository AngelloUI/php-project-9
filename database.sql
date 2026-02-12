CREATE TABLE urls (
                      id BIGSERIAL PRIMARY KEY,
                      name VARCHAR(255) UNIQUE NOT NULL,
                      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE url_checks (
                            id BIGSERIAL PRIMARY KEY,
                            url_id BIGINT REFERENCES urls(id) ON DELETE CASCADE,
                            status_code INTEGER,
                            h1 TEXT,
                            title TEXT,
                            description TEXT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
