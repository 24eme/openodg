#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous?reduce=false | grep -E "PARCELLAIRE[A-Z]*-[0-9]+-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' | sort | uniq | while read id
do
    php symfony parcellaire:send-mail-acheteurs $id --trace
done