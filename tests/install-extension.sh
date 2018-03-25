#!/usr/bin/env bash

git clone -q --depth=1 https://github.com/ice/framework.git
cd framework/

PHP7=`php -r "echo (int) version_compare(PHP_VERSION, '7.0.0', '>=');"`

if (( $PHP7 == 1 )); then
    cd build/php7
else
    cd build/php5
fi

source ./install

phpenv config-add ../../tests/ci/ice.ini
