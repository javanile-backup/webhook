#!/bin/bash

git clone https://github.com/javanile/webhook
cd webhook
composer install
cp manifest.json.sample manifest.json
cp secret.php.sample secret.php

BASE=$(realpath $PWD)

echo "1. Add cron: * * * * * root $BASE/cron.sh"
echo "2. Move 'webhook.php' in public web folder"
echo "3. Edit 'webhook.php' variable \$_WEBHOOK_DIR = '$BASE';"
echo "4. Browse 'webhook.php' and access with: admin"
