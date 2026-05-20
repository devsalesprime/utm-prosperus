#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
apt update -y
apt install -y curl wget gnupg2 ca-certificates lsb-release apt-transport-https software-properties-common

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
npm install -g pm2

# Install Nginx
apt install -y nginx

# Install MariaDB
apt install -y mariadb-server

# Install PHP 8.3 FPM and MySQL ext
add-apt-repository -y ppa:ondrej/php
apt update -y
apt install -y php8.3-fpm php8.3-mysql php8.3-cli php8.3-common php8.3-mbstring php8.3-xml php8.3-curl

# Ensure services are started
systemctl enable nginx mariadb php8.3-fpm
systemctl start nginx mariadb php8.3-fpm

echo "SETUP_DONE"
