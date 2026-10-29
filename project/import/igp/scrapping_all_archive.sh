#!/bin/bash

mkdir imports 2> /dev/null
cd imports
git init 2> /dev/null
cd ..

ls config_*.json | while read config_file;
do
    echo "Scrapping $config_file"
    bash scrapping.sh $config_file
    cd imports
    echo "Scrapping $config_file" > /tmp/commit_message
    git add .
    git commit -F /tmp/commit_message > /dev/null
    cd ..
done
