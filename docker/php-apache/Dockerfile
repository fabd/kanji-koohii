#
#  Configs
#
#    Php inis
#      /etc/php/7.4/apache2/conf.d/koohii.php.ini
#
#    Php mods
#      /etc/php/7.4/mods-available/
#
#    Apache sites
#      /etc/apache2/sites-enabled/000-default.conf
#

FROM ubuntu:20.04

# The UID/GID of the user in the host - to fix file permissions in the /src folder
ARG UID=1000
ARG GID=1000
ARG USR=koohii

WORKDIR /var/www/html

# makes  the  default  answers  be used for all questions
ENV DEBIAN_FRONTEND noninteractive

# Bash configs
COPY bash/.inputrc /root/

# Update
RUN apt-get update -y

# Install utils
RUN apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    less \
    nano \
    locales \
    vim

# Install Apache + PHP
RUN apt-get install -y --no-install-recommends \
    apache2 \
    libapache2-mod-php7.4 \
    php7.4-bz2 \
    php7.4-cli \
    php7.4-curl \
    php7.4-json \
    php7.4-mbstring \
    php7.4-mysql \
    php7.4-xml \
    php7.4-zip \
    # Configure Apache + PHP
    && a2enmod rewrite \
    && a2enmod expires \
    # Clean
    && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# nodejs (required for Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_12.x | bash - && apt-get install -y nodejs

# Set locales
RUN locale-gen en_US.UTF-8 en_GB.UTF-8

# Setup a non-UTC timezone for checking the proper handling of dates & times in the app
ENV TZ=Europe/Brussels
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Configure PHP
ADD koohii.php.ini /etc/php/7.4/apache2/conf.d/

# Configure vhost
ADD koohii.vhosts.conf /etc/apache2/sites-enabled/000-default.conf

# Create a custom user to run PHP as - to solve file permission issues
RUN adduser --system --no-create-home --group --shell /bin/bash --uid ${UID} ${USR} && \
    sed -i "s/APACHE_RUN_USER=www-data/APACHE_RUN_USER=${USR}/g" /etc/apache2/envvars && \
    sed -i "s/APACHE_RUN_GROUP=www-data/APACHE_RUN_GROUP=${USR}/g" /etc/apache2/envvars

# Ports
#   3000 ... Vite dev server
EXPOSE 80
EXPOSE 443
EXPOSE 3000   

COPY bash/.bash_aliases /root/.bash_aliases

CMD ["apache2ctl", "-D", "FOREGROUND"]