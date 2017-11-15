#!/bin/bash

## Settings
MANIFEST=manifest.json

## Move to webhook dircetory
cd $(dirname "$0") && cd ..

## Init cron
php api/cron.php $MANIFEST init

## Loop for tasks
TASK=1
while [ -n "$TASK" ]; do
    TASK=$(php api/cron.php $MANIFEST feed)
    if [ -n "$TASK" ]; then
        ## Run task
        bash -c "$TASK" &> ./logs/task.log 2>&1
        ## Write logs
        cat ./logs/task.log >> ./logs/cron.log
        cat ./logs/task.log >> ./logs/event.log
    fi
done

## Stop cron
php api/cron.php $MANIFEST stop
