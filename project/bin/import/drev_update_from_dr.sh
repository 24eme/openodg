#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi


curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous?reduce=false | grep "DREV\-" | grep "\-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' > /tmp/drev_to_sauvegarde_update.csv

echo "Update DRev";

cat /tmp/drev_to_sauvegarde_update.csv | sort | uniq | while read id  
do
    php symfony drev:import-dr $id false false --forceupdate=true --trace
done

rm /tmp/drev_to_sauvegarde_update.csv
