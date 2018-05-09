#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

PATH_DR=$2

if ! test "$PATH_DR"; then
    echo "Vous devez spécifier le chemin vers les documents csv et pdf de la DR"
    exit;
fi

curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/declaration/_view/tous | grep "DREV\-" | grep "\-$CAMPAGNE" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' > /tmp/drev_to_import_dr.csv

ls $PATH_DR | sed 's/DR/DREV/' | sed 's/_/-/g' | sed -r 's/\..+//' | sort | uniq  > /tmp/drev_to_create_from_dr.csv

cat /tmp/drev_to_import_dr.csv /tmp/drev_to_create_from_dr.csv | sort | uniq | while read id
do
    DR=$(echo $id | sed 's/DREV/DR/' | sed 's/-/_/g')
    APPLICATION=ava php symfony drev:import-dr $id $PATH_DR/$DR.csv $PATH_DR/$DR.pdf $SYMFONYTASKOPTIONS
done

rm /tmp/drev_to_import_dr.csv
rm /tmp/drev_to_create_from_dr.csv
