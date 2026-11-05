# Deploying BB Caterers to Render (one link) with Neon PostgreSQL

One Render **web service** runs everything on a single URL:

| URL | What |
|---|---|
| `https://<your-app>.onrender.com/` | React website |
| `/admin` | Admin panel (PHP) |
| `/api/*` | JSON API (PHP) |
| `/media/*` | Images (repo files + admin uploads stored in Neon) |

Local development is unchanged: **MySQL on your PC**, **Neon only on Render**.
The app picks the database by environment variable — when `DATABASE_URL` is set it uses Neon,
otherwise it uses `DB_*` (MySQL) from `backend/.env`.

---

## 1. Copy your local MySQL content into Neon (any time you want to re-sync)

```bash
cd backend
NEON_DATABASE_URL="postgresql://USER:PASSWORD@HOST/neondb?sslmode=require&channel_binding=require" \
  php -d extension=pdo_pgsql database/copy_mysql_to_neon.php
```
- Reads MySQL only (never changes it), creates the Neon tables, then **replaces** Neon's rows.
- In PowerShell use: `$env:NEON_DATABASE_URL="postgresql://..."; C:\xampp\php\php.exe -d extension=pdo_pgsql database\copy_mysql_to_neon.php`

## 2. Create the service on Render

1. **render.com → New → Blueprint** → connect the GitHub repo **BB-Caterers-Landing** (it reads `render.yaml`).
   *(Or: New → Web Service → the repo → Language **Docker**, Dockerfile path `./Dockerfile`, plan Free.)*
2. When asked for the secret values, enter:

| Key | Value |
|---|---|
| `DATABASE_URL` | your Neon connection string (Neon dashboard → **Connect** → pooled connection) |
| `APP_KEY` | **the same** `APP_KEY` as in your local `backend/.env` (it decrypts the saved SMTP password) |

   These are already set by `render.yaml`: `MEDIA_STORAGE=database`, `FRONTEND_URL=/`, `APP_DEBUG=false`, `APP_TIMEZONE=Asia/Kolkata`.
3. Click **Apply / Create**. The first build takes ~5–8 minutes (it builds React, then PHP + Apache).
4. On start the container runs `database/install.php` — it only creates missing tables and never overwrites data.

## 3. After the first deploy

- Open `https://<your-app>.onrender.com` → site; `https://<your-app>.onrender.com/admin` → admin.
- Log in and **change the admin password** (Profile & Password).
- Admin → **SMTP / Email** → enter your mail server → **Send test email**.
- Every `git push` to `main` redeploys automatically.

## Notes

- **Free plan:** the service sleeps after ~15 min without visits; the first visit afterwards takes ~30–60 s.
- **Uploads:** Render's disk is wiped on each deploy, so admin uploads are stored in Neon (`media_files` table)
  and served by `media.php`. Images committed in `backend/media/` are part of the code.
- **Secrets:** never commit `DATABASE_URL`, `.env` files or database passwords. They live only in Render → Environment.
