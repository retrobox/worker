#!/bin/bash

while :
do
  php worker.php
  echo "Worker exit, restarting..."
  sleep 1
done
