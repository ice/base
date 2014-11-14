#!/usr/bin/env bash

git clone -q --depth=1 https://github.com/ice/framework.git
cd framework/ext
./install
phpenv config-add ../tests/ci/ice.ini
