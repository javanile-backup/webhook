#!/bin/bash

WEBHOOK=$(php webhook-tools.php remote-url $1)?hook=$2
PAYLOAD=$(php webhook-tools.php remote-data $2)
CONTENT=$(echo Content-Type: application/json)
XSECRET=$(php webhook-tools.php secret $2)
XGITHUB=$(echo -n "${PAYLOAD}" \
    | openssl dgst -sha1 -hmac "%{XSECRET}" \
    | awk '{print "X-Hub-Signature: sha1="$2}')

echo "--> Trigger: $WEBHOOK"

curl \
    -X POST \
    -H "${CONTENT}" \
    -H "${XGITHUB}" \
    --data "${PAYLOAD}" \
    ${WEBHOOK}

echo ""; echo "--> Done."
