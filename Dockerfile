# Stage 1: Build frontend assets using Node
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Production PHP-FPM + Nginx server
FROM richarvey/nginx-php-fpm:3.1.6

WORKDIR /var/www/html

# Environment settings for richarvey image
ENV SKIP_COMPOSER=0
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Copy application files
COPY . /var/www/html

# Copy pre-compiled Vite frontend assets from node-builder
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Setup storage and cache permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Make deploy script executable
RUN chmod +x /var/www/html/scripts/00-laravel-deploy.sh
