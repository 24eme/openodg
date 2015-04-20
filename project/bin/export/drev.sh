#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

HEADER=0

php symfony drev:export-csv $(curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous | grep "DREV\-" | grep "\-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//g' | sort | tr "\n" " ") --header=$HEADER