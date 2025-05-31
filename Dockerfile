# Stage 1: Node - build Vue assets
FROM node:20-alpine as node_builder
WORKDIR /app

# Vite build args
ARG VITE_APP_NAME
ARG VITE_API_URL
ENV VITE_APP_NAME=$VITE_APP_NAME
ENV VITE_API_URL=$VITE_API_URL

RUN echo "🧪 VITE_APP_NAME=$VITE_APP_NAME" && \
    echo "🧪 VITE_API_URL=$VITE_API_URL"

COPY . .
RUN npm install && npm run build

# Stage 2: PHP base image with Composer and extensions
FROM php:8.3-fpm-alpine as base_php
WORKDIR /var/www

RUN apk add --no-cache git curl libzip-dev zip oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql zip bcmath

RUN echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini

# Copy Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy source code (excluding build/vendor for now)
COPY . .

# Copy Vue assets from builder
COPY --from=node_builder /app/public/build ./public/build

# Install dependencies in the final container's working directory
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Add startup logic
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
