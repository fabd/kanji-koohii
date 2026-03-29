#
#  Configs
#
#    Php inis
#      /etc/php/8.3/apache2/conf.d/koohii.php.ini
#
#    Php mods
#      /etc/php/8.3/mods-available/
#
#    Apache sites
#      /etc/apache2/sites-enabled/000-default.conf
#
#

FROM ubuntu:24.04

# The UID/GID of the user in the host - to fix file permissions in the /src folder
ARG UID=1000
ARG GID=1000
ARG USR=koohii

WORKDIR /var/www/html

# Prevent interactive prompts during package installation
ENV DEBIAN_FRONTEND noninteractive

# Install utils
RUN apt-get update -y && \
    apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    less \
    locales \
    nano \
    # sudo \
    vim

# Install Apache + PHP 8.3 (default on Ubuntu 24.04)
RUN apt-get install -y --no-install-recommends \
    apache2 \
    libapache2-mod-php \
    php-bz2 \
    php-cli \
    php-curl \
    php-intl \
    php-mbstring \
    php-mysql \
    php-xml \
    php-zip \
    && a2enmod rewrite \
    && a2enmod expires \
    # Clean
    && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# nodejs (required for Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - && apt-get install -y nodejs

# Set locales
RUN locale-gen en_US.UTF-8 en_GB.UTF-8

# Setup a non-UTC timezone for checking the proper handling of dates & times in the app
ENV TZ=Europe/Brussels
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Create a custom user to run PHP as - to solve file permission issues
RUN userdel -r ubuntu && \
    addgroup --gid ${GID} ${USR} && \
    adduser --disabled-password --uid ${UID} --ingroup ${USR} --no-create-home --shell /bin/bash ${USR} && \
    sed -i "s/APACHE_RUN_USER=www-data/APACHE_RUN_USER=${USR}/g" /etc/apache2/envvars && \
    sed -i "s/APACHE_RUN_GROUP=www-data/APACHE_RUN_GROUP=${USR}/g" /etc/apache2/envvars

ADD ./php/koohii.php.ini /etc/php/8.3/apache2/conf.d/

# Configure vhost
ADD ./php/koohii.conf /etc/apache2/sites-available/

RUN a2ensite koohii

COPY bash/bashrc /root/.bashrc

EXPOSE 80
EXPOSE 443
EXPOSE 5173

CMD ["apache2ctl", "-D", "FOREGROUND"]