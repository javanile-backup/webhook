#!/bin/bash

git clone https://github.com/javanile/webhook

cd webhook

composer install

echo "1. Add cron: * * * * * root $(dirname $0)/cron.sh"
echo "2. Move webhook.php in public web folder"