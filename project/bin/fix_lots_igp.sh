#!/bin/bash

if ! test "$1"; then
    echo "Nom du dossier/de l'ODG";
    exit 1;
fi

ODG=$1

. bin/config_$ODG.inc

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/archivage/_view/all?reduce=false | grep "Lot" | cut -d "," -f 1 | sed 's/{"id":"//' | sed 's/"//' | sort | uniq > /tmp/all_docs_$COUCHBASE
# Mise à jour des changements de dénominations
cat /tmp/all_docs_$COUCHBASE | grep CHGTDENOM | while read id; do php symfony chgtdenom:regenerate-lots $id $SYMFONYTASKOPTIONS; done
echo "Premier passage"
# Première correction des lots
cat /tmp/all_docs_$COUCHBASE | while read id; do php symfony fix:document-lots $id $SYMFONYTASKOPTIONS; done
echo "Deuxième passage"
# Second passage pour cohérence des numéros de lots
cat /tmp/all_docs_$COUCHBASE | while read id; do php symfony fix:document-lots $id $SYMFONYTASKOPTIONS; done
# Troisième passage par prudence cohérence des numéros de lots
echo "Troisième passage"
cat /tmp/all_docs_$COUCHBASE | while read id; do php symfony fix:document-lots $id $SYMFONYTASKOPTIONS; done
