-- =====================================================================
--  BB Caterers — PostgreSQL (Neon) tables.
--  The seed rows are shared with schema.sql; install.php converts them.
--  Every statement is safe to run again (IF NOT EXISTS).
-- =====================================================================

CREATE TABLE IF NOT EXISTS admins (
  id                   SERIAL PRIMARY KEY,
  name                 VARCHAR(100) NOT NULL,
  username             VARCHAR(50)  NOT NULL UNIQUE,
  email                VARCHAR(150) NOT NULL UNIQUE,
  password_hash        VARCHAR(255) NOT NULL,
  must_change_password SMALLINT     NOT NULL DEFAULT 0,
  reset_token_hash     CHAR(64)     NULL,
  reset_expires_at     TIMESTAMP(0) NULL,
  last_login_at        TIMESTAMP(0) NULL,
  created_at           TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at           TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS login_attempts (
  id           SERIAL PRIMARY KEY,
  ip           VARCHAR(45)  NOT NULL,
  attempted_at TIMESTAMP(0) NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_login_attempts_ip_time ON login_attempts (ip, attempted_at);

CREATE TABLE IF NOT EXISTS settings (
  "key"      VARCHAR(80)  NOT NULL PRIMARY KEY,
  "value"    TEXT         NULL,
  updated_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS menu_items (
  id         SERIAL PRIMARY KEY,
  label      VARCHAR(60)  NOT NULL,
  url        VARCHAR(255) NOT NULL,
  sort_order INTEGER      NOT NULL DEFAULT 0,
  is_active  SMALLINT     NOT NULL DEFAULT 1,
  created_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS hero_slides (
  id         SERIAL PRIMARY KEY,
  eyebrow    VARCHAR(120) NULL,
  title      VARCHAR(255) NOT NULL,
  subtitle   TEXT         NULL,
  image      VARCHAR(255) NULL,
  btn1_text  VARCHAR(60)  NULL,
  btn1_link  VARCHAR(255) NULL,
  btn2_text  VARCHAR(60)  NULL,
  btn2_link  VARCHAR(255) NULL,
  sort_order INTEGER      NOT NULL DEFAULT 0,
  is_active  SMALLINT     NOT NULL DEFAULT 1,
  created_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS services (
  id          SERIAL PRIMARY KEY,
  title       VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  image       VARCHAR(255) NULL,
  icon        VARCHAR(50)  NULL,
  sort_order  INTEGER      NOT NULL DEFAULT 0,
  is_active   SMALLINT     NOT NULL DEFAULT 1,
  created_at  TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at  TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS gallery (
  id         SERIAL PRIMARY KEY,
  title      VARCHAR(120) NULL,
  image      VARCHAR(255) NOT NULL,
  layout     VARCHAR(10)  NOT NULL DEFAULT 'normal' CHECK (layout IN ('normal','wide','tall','large')),
  sort_order INTEGER      NOT NULL DEFAULT 0,
  is_active  SMALLINT     NOT NULL DEFAULT 1,
  created_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS highlights (
  id         SERIAL PRIMARY KEY,
  title      VARCHAR(120) NOT NULL,
  icon       VARCHAR(50)  NULL,
  sort_order INTEGER      NOT NULL DEFAULT 0,
  is_active  SMALLINT     NOT NULL DEFAULT 1,
  created_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS events (
  id         SERIAL PRIMARY KEY,
  title      VARCHAR(120) NOT NULL,
  image      VARCHAR(255) NULL,
  icon       VARCHAR(50)  NULL,
  sort_order INTEGER      NOT NULL DEFAULT 0,
  is_active  SMALLINT     NOT NULL DEFAULT 1,
  created_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS features (
  id          SERIAL PRIMARY KEY,
  title       VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  icon        VARCHAR(50)  NULL,
  sort_order  INTEGER      NOT NULL DEFAULT 0,
  is_active   SMALLINT     NOT NULL DEFAULT 1,
  created_at  TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at  TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS testimonials (
  id         SERIAL PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  role       VARCHAR(120) NULL,
  message    TEXT         NOT NULL,
  rating     SMALLINT     NOT NULL DEFAULT 5,
  photo      VARCHAR(255) NULL,
  sort_order INTEGER      NOT NULL DEFAULT 0,
  is_active  SMALLINT     NOT NULL DEFAULT 1,
  created_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);

CREATE TABLE IF NOT EXISTS enquiries (
  id         SERIAL PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  phone      VARCHAR(30)  NULL,
  event_date DATE         NULL,
  event_type VARCHAR(120) NULL,
  guests     INTEGER      NULL CHECK (guests IS NULL OR guests >= 0),
  message    TEXT         NULL,
  status     VARCHAR(12)  NOT NULL DEFAULT 'new' CHECK (status IN ('new','contacted','confirmed','closed')),
  admin_note TEXT         NULL,
  ip         VARCHAR(45)  NULL,
  created_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0),
  updated_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);
CREATE INDEX IF NOT EXISTS idx_enquiries_status  ON enquiries (status);
CREATE INDEX IF NOT EXISTS idx_enquiries_created ON enquiries (created_at);

-- Admin uploads when MEDIA_STORAGE=database (Render's disk is wiped on each deploy)
CREATE TABLE IF NOT EXISTS media_files (
  path       VARCHAR(255) NOT NULL PRIMARY KEY,
  mime       VARCHAR(60)  NOT NULL,
  size       INTEGER      NOT NULL,
  data       TEXT         NOT NULL,   -- base64
  created_at TIMESTAMP(0) NOT NULL DEFAULT LOCALTIMESTAMP(0)
);
