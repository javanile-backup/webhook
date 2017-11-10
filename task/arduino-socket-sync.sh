#!/bin/bash

cd /app/iot-connect/arduino-socket

git checkout master
git fetch --all
git reset --hard origin/master

./build.sh