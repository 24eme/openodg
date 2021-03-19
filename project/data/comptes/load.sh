#!/bin/bash

cd $(dirname $0)"/."

ls */* | awk -F '/' '{print "curl -d @data/comptes/"$1"/"$2" -X PUT http://127.0.0.1:5984/"$1"_prod/"$2}'
ls */* | awk -F '/' '{print "php symfony compte:ldap-update --application="$1" --env=prod "$2}'  | grep COMPTE-
