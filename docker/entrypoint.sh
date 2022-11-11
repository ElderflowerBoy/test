#!/usr/bin/env sh

supervisord -c /etc/supervisor/supervisord.conf

php-fpm