#!/bin/bash

## Move to cron.sh dircetory
cd $(dirname "$0")

##
MANIFEST=manifest.json

## Init cron
php cron.php $MANIFEST init

## Loop for tasks
TASK=1
while [ -n "$TASK" ]; do
    TASK=$(php cron.php $MANIFEST feed)
    if [ -n "$TASK" ]; then
        bash -c "$TASK" &> ./logs/task.log 2>&1
        cat ./logs/task.log >> ./logs/cron.log
        cat ./logs/task.log >> ./logs/event.log
    fi
done

## Close cron
php cron.php $MANIFEST stop
