#!/bin/bash

URL=$(php webhook-tools.php remote-url $1)

echo $URL

#curl -XPOST