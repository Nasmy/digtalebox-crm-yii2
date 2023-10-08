#!/bin/bash

cp app-config/.env /var/www/html/.env
sed -i "s/mysqldburlplaceholder/$DB_URL/g" /var/www/html/.env
sed -i "s/mysqldbunameplaceholder/$DB_USER/g" /var/www/html/.env
sed -i "s/mysqldbpwordplaceholder/$DB_PASS/g" /var/www/html/.env
until [ "`curl --silent --show-error --connect-timeout 1 -I $DB_URL:3306 2>&1 | grep '8'`" != "" ];
do
  echo --- waiting for db to start
  sleep 2
done
cd /var/www/html && chmod -R 777 storage bootstrap && setfacl -Rm d::rwx storage bootstrap && php artisan key:generate && php artisan config:cache && php artisan migrate 2>errors.log
/usr/sbin/apache2ctl -D FOREGROUND
