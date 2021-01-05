#!/usr/bin/env sh

DIR=$(dirname "$0")
DOMAIN=$(basename "$DIR")
cd $DIR

#cp /var/www/$DOMAIN/api/env/$DEPLOY.env /var/www/$DOMAIN/api/.env
echo "COPY /var/www/$DOMAIN/api/env/$DEPLOY.env /var/www/$DOMAIN/api/.env"

sed s/example.com/$DOMAIN/g nginx-dev.conf > /etc/nginx/conf.d/site.conf


mkdir -p /run/nginx

# Run Nginx
nginx
echo "[$DEPLOY] $DOMAIN Nginx started"
echo "PHP is started"

echo "please enter command in terminal: docker exec -it policy-seathailand-com_web sh"
echo "Then: cd /var/www/$DOMAIN/html && yarn start"
/usr/sbin/php-fpm7.3 -F --fpm-config=/etc/php/7.3/fpm/pool.d/www.conf
RUN cd /var/www/$DOMAIN/html && yarn start