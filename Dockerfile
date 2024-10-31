FROM wordpress
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
	chmod +x wp-cli.phar && \
	mv wp-cli.phar /usr/local/bin/wp
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo_mysql
RUN apt-get update && apt-get install default-mysql-client subversion -y
RUN pecl install xdebug && echo 'zend_extension=xdebug.so' >> $PHP_INI_DIR/php.ini && \
	echo 'xdebug.client_host = 10.10.0.1' >> $PHP_INI_DIR/php.ini && \
	echo 'xdebug.start_with_request = yes' >> $PHP_INI_DIR/php.ini && \
	echo 'xdebug.mode = debug' >> $PHP_INI_DIR/php.ini && \
	echo 'xdebug.idekey = idea' >> $PHP_INI_DIR/php.ini
