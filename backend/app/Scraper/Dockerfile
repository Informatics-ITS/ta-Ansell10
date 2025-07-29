FROM php:8.2-fpm
WORKDIR /var/www/html/Scraper        # <— directly inside Scraper

# OS + Python deps
RUN apt-get update && apt-get install -y --no-install-recommends \
      python3 python3-pip python3-venv \
      libonig-dev libxml2-dev libzip-dev \
 && docker-php-ext-install pdo_mysql mbstring xml zip \
 && python3 -m venv /opt/venv \
 && ln -s /usr/bin/python3 /usr/bin/python \
 && rm -rf /var/lib/apt/lists/*

# Python packages
COPY requirements.txt .
RUN /opt/venv/bin/pip install --no-cache-dir -r requirements.txt

# Copy the scraper code
COPY . .

# Make cron executable
RUN chmod +x cron.sh

CMD ["./cron.sh"]
