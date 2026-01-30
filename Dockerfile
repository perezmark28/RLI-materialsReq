# PHP with mysqli for Railway
FROM php:8.2-cli

# Install mysqli (required for db_connect.php; not in Railway's default PHP)
RUN docker-php-ext-install mysqli

WORKDIR /app
COPY . /app

# Railway sets PORT (e.g. 8080). PHP built-in server must listen on that port.
ENV PORT=8080
EXPOSE 8080
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t /app"]
