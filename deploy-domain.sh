#!/usr/bin/env bash
set -euo pipefail

cat >/etc/nginx/sites-available/fordev11.tech <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name fordev11.tech;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
NGINX

ln -sfn /etc/nginx/sites-available/fordev11.tech /etc/nginx/sites-enabled/fordev11.tech
nginx -t
systemctl reload nginx
certbot --nginx -d fordev11.tech --non-interactive --agree-tos --register-unsafely-without-email --redirect
nginx -t
systemctl reload nginx
curl -fsSI https://fordev11.tech
