#!/bin/sh
set -e

# Listen on the port Render gives us
PORT="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
export PORT

# Pass Render's environment variables through to PHP under Apache
for v in DATABASE_URL APP_KEY APP_URL FRONTEND_URL CORS_ORIGINS APP_DEBUG APP_TIMEZONE MEDIA_STORAGE DB_DRIVER; do
  eval "val=\${$v:-}"
  [ -n "$val" ] && echo "PassEnv $v" >> /etc/apache2/conf-enabled/bbcaterers-env.conf
done
sort -u -o /etc/apache2/conf-enabled/bbcaterers-env.conf /etc/apache2/conf-enabled/bbcaterers-env.conf 2>/dev/null || true

# Create tables + seed content on first boot (safe to run every time: never overwrites data)
if [ -n "${DATABASE_URL:-}" ]; then
  php /var/www/html/database/install.php || echo "WARNING: database install failed — check DATABASE_URL"
fi

exec apache2-foreground
