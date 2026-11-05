# =====================================================================
#  BB Caterers — one container, one URL (Render)
#    /            React site (built with Vite)
#    /api/*       PHP JSON API
#    /admin/*     PHP admin panel
#    /media/*     images (repo files + admin uploads stored in Neon)
# =====================================================================

# ---- 1) Build the React frontend -------------------------------------
FROM node:22-bookworm-slim AS frontend
WORKDIR /app
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY frontend/ ./
# Same domain as the PHP backend → the site calls /api directly
ENV VITE_API_URL=/api
RUN npm run build

# ---- 2) PHP 8.2 + Apache ---------------------------------------------
FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev libpng-dev libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
 && docker-php-ext-install -j"$(nproc)" pdo_pgsql pdo_mysql gd \
 && a2enmod rewrite headers expires \
 && rm -rf /var/lib/apt/lists/*

# Production PHP settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && { echo 'upload_max_filesize=10M'; echo 'post_max_size=12M'; echo 'memory_limit=256M'; \
      echo 'expose_php=Off'; echo 'session.cookie_httponly=1'; echo 'date.timezone=Asia/Kolkata'; } \
    > "$PHP_INI_DIR/conf.d/bbcaterers.ini"

WORKDIR /var/www/html
COPY backend/ ./
COPY --from=frontend /app/dist/ ./
COPY deploy/apache-site.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/start.sh /usr/local/bin/start.sh

RUN rm -f .env router.php \
 && sed -i 's/\r$//' /usr/local/bin/start.sh /etc/apache2/sites-available/000-default.conf \
 && chmod +x /usr/local/bin/start.sh \
 && chown -R www-data:www-data /var/www/html

# Render provides $PORT (default 10000)
ENV PORT=10000
EXPOSE 10000
CMD ["start.sh"]
