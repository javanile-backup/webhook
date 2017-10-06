#!/bin/bash

cd $(dirname "$0")

php webhook-tools.php cron-init

TASK=1

while [ -n "$TASK" ]; do
    TASK=$(php webhook-tools.php cron-feed)
    if [ -n "$TASK" ]; then
        { $TASK } &> ./logs/task.log 2>&1
        cat ./logs/task.log >> ./logs/cron.log
        cat ./logs/task.log >> ./logs/event.log
    fi
done

php webhook-tools.php cron-done
