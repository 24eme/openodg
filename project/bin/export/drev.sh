#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

php symfony drev:export-csv $(curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_all_docs?startkey_docid=DREV-000000000000-0000&endkey_docid=DREV-99999999999-9999" | grep "\-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//g' | sort | tr "\n" " ")