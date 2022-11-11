FROM php:8.0.25-fpm-alpine3.16

ENV COMPOSER_ALLOW_SUPERUSER="1"

ENV PHPIZE_DEPS \
    build-base \
    autoconf

ENV PERMANENT_DEPS \
    supervisor

RUN set -e \
    && apk add --quiet --no-cache ${PERMANENT_DEPS} \
    && apk add --quiet --no-cache --virtual .build-deps ${PHPIZE_DEPS} \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* \
    && mkdir /app /var/log/supervisor

COPY ./supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY ./entrypoint.sh /etc/entrypoint.sh

WORKDIR /app

RUN chmod +x /etc/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/etc/entrypoint.sh"]