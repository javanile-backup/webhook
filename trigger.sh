#!/bin/bash

echo "--> Start: $2"

SECRET=secret
DATA=$(php webhook-tools.php remote-data $2)
URL=$(php webhook-tools.php remote-url $1)
SIG=$(echo -n "${DATA}" | openssl dgst -sha1 -hmac "%{SECRET}" | awk '{print "X-Hub-Signature: sha1="$2}')

curl \
    -X POST \
    -H "Content-Type: application/json" \
    -H "${SIG}" \
    --data "${DATA}" \
    ${URL}

echo ""; echo "--> Done."
