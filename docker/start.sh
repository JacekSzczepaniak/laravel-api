#!/usr/bin/env bash
set -e

# PHP-FPM in background
php-fpm -D

# Nginx in foreground
nginx -g "daemon off;"
