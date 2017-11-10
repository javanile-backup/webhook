#!/bin/bash

## Settings
MANIFEST=manifest.json

# echo Content-Type: application/json
WEBHOOK=$(php trigger.php $MANIFEST remote-url $1)?hook=$2
PAYLOAD=$(php trigger.php $MANIFEST remote-data $2)
CONTENT=$(php trigger.php $MANIFEST content-type $2)
XSECRET=$(php trigger.php $MANIFEST secret $2)
XGITHUB=$(echo -n "${PAYLOAD}" \
    | openssl dgst -sha1 -hmac "%{XSECRET}" \
    | awk '{print "X-Hub-Signature: sha1="$2}')

echo "--> Trigger: $WEBHOOK"

curl \
    -X POST \
    -H "${CONTENT}" \
    -H "${XGITHUB}" \
    -d "${PAYLOAD}" \
    ${WEBHOOK}

echo ""; echo "--> Done."
