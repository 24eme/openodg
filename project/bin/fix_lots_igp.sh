#!/bin/bash

if ! test "$1"; then
    echo "Nom du dossier/de l'ODG";
    exit 1;
fi

ODG=$1

. bin/config_$ODG.inc

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/archivage/_view/all?reduce=false | grep "Lot" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' | sort | uniq > /tmp/all_docs_$COUCHBASE
# Mise à jour des changements de dénominations
cat /tmp/all_docs_igparlespreprod | grep CHGTDENOM | while read id; do echo $id; echo php symfony chgtdenom:regenerate-lots $id $SYMFONYTASKOPTIONS; done
# Première correction des lots
cat /tmp/all_docs_igparlespreprod | while read id; do echo "$id"; php symfony fix:document-lots $id $SYMFONYTASKOPTIONS; done
# Second passage pour cohérence des numéros de lots
cat /tmp/all_docs_igparlespreprod | while read id; do echo "$id"; php symfony fix:document-lots $id $SYMFONYTASKOPTIONS; done