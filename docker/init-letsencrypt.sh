#!/bin/sh
# ─────────────────────────────────────────────────────────────────────────────
# Первичный выпуск Let's Encrypt сертификата для tasin-mobile.ru.
# Запускать ОДИН РАЗ на сервере из корня проекта:  sh docker/init-letsencrypt.sh
# Дальнейшее продление делает сервис certbot автоматически.
# ─────────────────────────────────────────────────────────────────────────────
set -e

domain="tasin-mobile.ru"
email="mekan.developer@gmail.com"   # для уведомлений об истечении/безопасности
staging=0                           # 1 = тестовый сервер LE (без лимитов, для отладки)
rsa_key_size=4096

compose="docker compose"
cert_path="/etc/letsencrypt/live/$domain"

echo "### 1/5 Загрузка рекомендованных TLS-параметров ..."
$compose run --rm --entrypoint "\
  sh -c 'mkdir -p /etc/letsencrypt && \
    wget -qO /etc/letsencrypt/options-ssl-nginx.conf https://raw.githubusercontent.com/certbot/certbot/main/certbot-nginx/src/certbot_nginx/_internal/tls_configs/options-ssl-nginx.conf && \
    wget -qO /etc/letsencrypt/ssl-dhparams.pem https://raw.githubusercontent.com/certbot/certbot/main/certbot/certbot/ssl-dhparams.pem'" certbot

echo "### 2/5 Временный self-signed сертификат (чтобы nginx смог стартовать) ..."
$compose run --rm --entrypoint "\
  sh -c 'mkdir -p $cert_path && \
    openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
      -keyout $cert_path/privkey.pem \
      -out $cert_path/fullchain.pem \
      -subj /CN=localhost'" certbot

echo "### 3/5 Запуск nginx ..."
$compose up -d nginx

echo "### 4/5 Удаление временного сертификата ..."
$compose run --rm --entrypoint "\
  sh -c 'rm -rf /etc/letsencrypt/live/$domain \
    /etc/letsencrypt/archive/$domain \
    /etc/letsencrypt/renewal/$domain.conf'" certbot

echo "### 5/5 Запрос боевого сертификата у Let's Encrypt ..."
staging_arg=""
[ "$staging" != "0" ] && staging_arg="--staging"

$compose run --rm --entrypoint "\
  certbot certonly --webroot -w /var/www/certbot \
    $staging_arg \
    --email $email \
    -d $domain \
    --rsa-key-size $rsa_key_size \
    --agree-tos --no-eff-email --force-renewal" certbot

echo "### Готово. Перезагрузка nginx ..."
$compose exec nginx nginx -s reload

echo "✅ Сертификат для $domain выпущен. Сайт доступен по https://$domain"
