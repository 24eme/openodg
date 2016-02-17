#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "Une expression est requise avec \\\$id pour utiliser le paramètre"
    exit;
fi


curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_view/tous?reduce=false | grep -E "PARCELLAIRE[A-Z]*-[0-9]+-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' | sort | uniq | while read id
do
    php symfony parcellaire:send-mail-acheteurs $id
done