FROM php:7.0-apache
RUN apt-get clean && apt-get update
RUN apt-get -qy install zip
COPY src/ /var/www/html/
COPY apache2/servername.conf /etc/apache2/conf-available/
COPY apache2/security.conf /etc/apache2/conf-available/
COPY apache2/ssl.conf /etc/apache2/mods-available/
COPY php/php.ini /usr/local/etc/php/
COPY sslkeys/host.pem /etc/ssl/certs/ssl-cert-snakeoil.pem
COPY sslkeys/host.key /etc/ssl/private/ssl-cert-snakeoil.key
RUN a2ensite default-ssl
RUN a2enconf security
RUN a2enconf servername
RUN a2enmod ssl
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
RUN composer update --no-plugins --no-scripts
RUN chown -R www-data:www-data /var/www/html/
EXPOSE 443
