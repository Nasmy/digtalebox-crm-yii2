FROM yiisoftware/yii2-php:7.3-apache

RUN apt-get update && apt-get -y install cron

COPY db-cron /etc/cron.d/db-cron
RUN chmod 0644 /etc/cron.d/db-cron
RUN crontab /etc/cron.d/db-cron
RUN cron

SHELL ["/bin/bash", "-c"]
COPY protected /app
COPY app-config app-config
RUN mkdir -p runtime web/{assets,erruploads,images,excel,uploads} 
RUN unzip app-config/resources.zip -d web/
RUN app-config/setup-for-prod.sh
RUN mkdir -p /var/www/html/DigitaleBoxMigration
RUN ln -s /app /var/www/html/DigitaleBoxMigration/protected
RUN composer install
RUN chmod -R 777 .


EXPOSE 80
