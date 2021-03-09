#!/bin/bash
cd imports
git init 2> /dev/null
cd ..

ls config_*.json | while read config_file;
do
    echo "Scrapping $config_file"
    bash scrapping.sh $config_file
    cd imports
    git add .
    git commit -m "Scrapping $config_file" > /dev/null
    cd ..
done