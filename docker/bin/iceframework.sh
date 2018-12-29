#!/bin/bash

cd / && mkdir /ext
cd /ext && curl -sSL "https://github.com/ice/framework/archive/$ICE_FRAMEWORK_VERSION.tar.gz" | tar -xz
cd framework-${ICE_FRAMEWORK_VERSION}

sed -i -e "s/sudo / /" install
sed -i -e "s/sudo / /" build/php7/install
sed -i -e "s/sudo / /" build/php5/install

chmod u+x install
chmod u+x build/php7/install
chmod u+x build/php7/install

./install

echo "extension=ice.so" > ${PHP_MODS_AVAILABLE_DIR}/ice.ini
echo "extension=ice.so" > ${PHP_FPM_CONFD_DIR}/ice.ini
echo "extension=ice.so" > ${PHP_CLI_CONFD_DIR}/ice.ini


service php7.2-fpm reload