#!/bin/bash

## Settings
MANIFEST=manifest.json

## Move to webhook dircetory
cd $(dirname "$0") && cd ..

# echo Content-Type: application/json
WEBHOOK=$(php api/trigger.php webhook-url:$1 $MANIFEST)
PAYLOAD=$(php api/trigger.php $MANIFEST payload $1)
CONTENT=$(php api/trigger.php $MANIFEST content-type $1)
XSECRET=$(php api/trigger.php $MANIFEST secret $1)
XGITHUB=$(echo -n "${PAYLOAD}" \
    | openssl dgst -sha1 -hmac "%{XSECRET}" \
    | awk '{print "X-Hub-Signature: sha1="$2}')

echo "--> Trigger: $WEBHOOK"

curl ${WEBHOOK} \
    -X POST \
    -H "${CONTENT}" \
    -H "${XGITHUB}" \
    -d "${PAYLOAD}" \

echo ""; echo "--> Done."
