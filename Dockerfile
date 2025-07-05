FROM php:8.2-cli

# Install mysqli and other dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install mysqli

# Set working directory
WORKDIR /app

# Copy app files
COPY . /app

# Expose port (optional)
EXPOSE 8000

# Start PHP dev server (or your entrypoint)
CMD ["php", "-S", "0.0.0.0:10000"]
