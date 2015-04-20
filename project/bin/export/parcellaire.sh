#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

HEADER=1

curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous | grep "PARCELLAIRE" | grep "\-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//g' | while read id  
do
    php symfony parcellaire:export-csv $id --header=$HEADER
done