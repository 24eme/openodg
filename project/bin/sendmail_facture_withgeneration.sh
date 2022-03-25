#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

cat | while read generation_id; do curl -s "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/$generation_id" | jq '.documents' | grep "FACTURE" ; done | sed 's/"//g' | cut -d "-" -f 2 | sed 's/^/COMPTE-/' | sort | uniq | while read compte_id; do
    echo php symfony facture:send-mail "$compte_id" "$CAMPAGNE" "$SYMFONYTASKOPTIONS";
    sleep 2
done;
