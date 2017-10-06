#!/bin/bash

## Move to project directory
cd $(dirname $0)/../

## Get User and Group
USER=${1:-$(id -u -n)}
GROUP=${2:-$(id -u -n)}

## Update from reposistory
git checkout master
git fetch --all
git reset --hard origin/master

## Update dependency
composer install
composer update

## Apply permissions
chown $USER:$GROUP * -R

## Apply executables
find . -name '*.sh' -type f | xargs chmod +x
