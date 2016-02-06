#!/bin/bash

echo "Getting OS up-to-date"
apt-get -qq update
apt-get -qq dist-upgrade -y

# Development
#
echo "Installing development tools and requirements"
apt-get -qq install php5-cli php5-curl patch nodejs nodejs-legacy -y

# Composer
php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer
