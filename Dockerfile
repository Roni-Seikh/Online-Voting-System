FROM php:8.2-cli

# Set working directory
WORKDIR /app

# Copy your code into the image
COPY . /app

# Expose the Render port
EXPOSE 10000

# Start the PHP server
CMD ["php", "-S", "0.0.0.0:10000"]
