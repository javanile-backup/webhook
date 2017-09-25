#!/bin/bash

cd $(dirname "$0")

php webhook-tools.php cron-init

TASK=1

while [ -n "$TASK" ]; do
    TASK=$(php webhook-tools.php cron-feed)
    if [ -n "$TASK" ]; then
        $TASK >> ./logs/cron.log 2>&1
    fi
done

php webhook-tools.php cron-done
