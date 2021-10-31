FROM nginx:alpine

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/sites/backend.conf /etc/nginx/sites-available/backend.conf
COPY ./docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

COPY ./public/index.php /app/public/index.php

CMD ["nginx"]

EXPOSE 80 443
