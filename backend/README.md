# BB Caterers — Backend (Core PHP + MySQL 8)

```
backend/
├── admin/          Admin panel (login, dashboard, content editors, enquiries, SMTP, password)
├── api/            Public JSON API for the React site
│   ├── site.php      GET  → all landing-page content in one response
│   └── enquiry.php   POST → save contact-form enquiry + send emails
├── config/         config.php (your DB details — never served)
├── core/           Database, helpers, SMTP mailer
├── database/       schema.sql + install.php
├── media/          All images (uploaded from admin), organised per section
└── router.php      Router for PHP's built-in dev server
```

## 1. Configure
Edit `config/config.php` → set `db.name`, `db.user`, `db.password` for your MySQL 8 server.

## 2. Create tables + seed content
```bash
php database/install.php
```
(or import `database/schema.sql` in MySQL Workbench — change the DB name at the top if yours differs.)

## 3. Run
```bash
php -S 127.0.0.1:8090 router.php        # backend (from this folder)
npm run dev                             # frontend (from ../frontend)
```
- Admin panel: http://localhost:5173/admin  (Vite proxies /admin, /api, /media to PHP)
- Default login: **admin / admin123** — change it in *Profile & Password*.

## 4. Email (SMTP)
Admin → **SMTP / Email** → enter host, port, username, password → *Send test email*.
Used for: new-enquiry notifications, customer auto-replies, enquiry replies, forgot-password links.

## Production
Upload `backend/` contents next to the built React site so `/admin`, `/api` and `/media`
are on the same domain. `.htaccess` files block `config/`, `core/`, `database/` and script
execution inside `media/`. Set `'debug' => false` in config.php.
