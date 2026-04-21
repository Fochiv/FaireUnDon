-- Schéma SQLite pour développement (Replit)
-- Pour WampServer/MySQL, voir db/schema_mysql.sql

CREATE TABLE IF NOT EXISTS admins (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    username      TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at    TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    key_name  TEXT NOT NULL UNIQUE,
    value     TEXT NOT NULL,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS countries (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    code          TEXT NOT NULL UNIQUE,
    name          TEXT NOT NULL,
    people_helped INTEGER NOT NULL DEFAULT 0,
    description   TEXT,
    image_url     TEXT,
    created_at    TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS donations (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    is_anonymous    INTEGER NOT NULL DEFAULT 0,
    first_name      TEXT,
    last_name       TEXT,
    country         TEXT,
    city            TEXT,
    phone           TEXT,
    email           TEXT,
    amount          REAL NOT NULL,
    currency        TEXT DEFAULT 'XAF',
    payment_method  TEXT NOT NULL,        -- 'mobile' ou 'card'
    operator        TEXT,                  -- ex: MTN Mobile Money
    payment_phone   TEXT,                  -- numéro qui paie
    reference       TEXT UNIQUE,           -- réf interne
    provider_ref    TEXT,                  -- réf retournée par l'API
    status          TEXT NOT NULL DEFAULT 'pending', -- pending, validated, failed, expired
    created_at      TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at      TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_donations_status  ON donations(status);
CREATE INDEX IF NOT EXISTS idx_donations_created ON donations(created_at);
