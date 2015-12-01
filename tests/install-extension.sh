#!/usr/bin/env bash

git clone -q --depth=1 https://github.com/ice/framework.git
cd framework/
./install
phpenv config-add ../tests/ci/ice.ini
