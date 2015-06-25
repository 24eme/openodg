#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

php symfony parcellaire:export-csv $(curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous?reduce=false" | grep "PARCELLAIRE" | grep "\-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//g' | sort | tr "\n" " ")