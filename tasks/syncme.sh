#!/bin/bash

cd $(dirname $0)/../

git checkout master
git fetch --all
git reset --hard origin/master

composer install
composer update

chmod 777 logs/*.log
