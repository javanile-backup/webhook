#!/bin/bash

php webhook-tools.php cron-init

TASK=1

while [ -n "$TASK" ];
do
    TASK=$(php webhook-tools.php cron-feed)
    $TASK
done

php webhook-tools.php cron-done
