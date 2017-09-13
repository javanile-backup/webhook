#!/bin/bash

echo "--> Start: $2"

URL=$(php webhook-tools.php remote-url $1)

#echo $URL

curl -XPOST $URL

echo ""; echo "--> Done."