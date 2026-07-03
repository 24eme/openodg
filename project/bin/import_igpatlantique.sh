#!/bin/bash

ODG=igpatlantique

. bin/config.inc

echo "Using database http://$COUCHHOST:$COUCHPORT/$COUCHBASE"

DATA_DIR=$WORKINGDIR/import/igp/imports/$ODG
mkdir -p $DATA_DIR 2> /dev/null

if test "$1" = "--delete"; then

    echo -n "Delete database http://$COUCHHOST:$COUCHPORT/$COUCHBASE, type database name to confirm ($COUCHBASE) : "
    read databasename

    if test "$databasename" = "$COUCHBASE"; then
        curl -sX DELETE http://$COUCHHOST:$COUCHPORT/$COUCHBASE
        echo "Suppression de la base couchdb"
    fi
fi

echo "Création de la base couchdb"

curl -sX PUT http://$COUCHHOST:$COUCHPORT/$COUCHBASE

cd .. > /dev/null
make clean > /dev/null
make couchurl=http://$COUCHHOST:$COUCHPORT/$COUCHBASE > /dev/null
cd - > /dev/null

echo "Création des documents de configuration"

ls $WORKINGDIR/data/configuration/$ODG | while read jsonFile
do
    curl -s -X POST -d @data/configuration/$ODG/$jsonFile -H "content-type: application/json" http://$COUCHHOST:$COUCHPORT/$COUCHBASE
done

xlsx2csv -s 4 -d ';' $DATA_DIR/FICHIER_IGP_ATLANTIQUE_2025-2026_17.06.2026.xlsx $TMPDIR/f4.csv
xlsx2csv -s 5 -d ';' $DATA_DIR/FICHIER_IGP_ATLANTIQUE_2025-2026_17.06.2026.xlsx $TMPDIR/f5.csv
xlsx2csv -s 6 -d ';' $DATA_DIR/FICHIER_IGP_ATLANTIQUE_2025-2026_17.06.2026.xlsx $TMPDIR/f6.csv
xlsx2csv -s 7 -d ';' $DATA_DIR/FICHIER_IGP_ATLANTIQUE_2025-2026_17.06.2026.xlsx $TMPDIR/f7.csv
grep -E '^[0-9]{2}/[0-9]{2}/[0-9]{4}' $TMPDIR/f4.csv > $TMPDIR/PVC.csv
grep -E '^[0-9]{2}/[0-9]{2}/[0-9]{4}' $TMPDIR/f5.csv > $TMPDIR/VC.csv
grep -E '^[0-9]{2}/[0-9]{2}/[0-9]{4}' $TMPDIR/f6.csv > $TMPDIR/C.csv
grep -E '^[0-9]{2}/[0-9]{2}/[0-9]{4}' $TMPDIR/f7.csv > $TMPDIR/P.csv
rm $TMPDIR/f4.csv
rm $TMPDIR/f5.csv
rm $TMPDIR/f6.csv
rm $TMPDIR/f7.csv

echo "Import des Opérateurs et Habilitations PVC"

php symfony import:operateur-habilitation-igpatlantique $TMPDIR/PVC.csv  --application="$ODG" --trace

echo "Import des Opérateurs et Habilitations VC"

php symfony import:operateur-habilitation-igpatlantique $TMPDIR/VC.csv  --application="$ODG" --trace

echo "Import des Opérateurs et Habilitations C"

php symfony import:operateur-habilitation-igpatlantique $TMPDIR/C.csv  --application="$ODG" --trace

echo "Import des Opérateurs et Habilitations P"

php symfony import:operateur-habilitation-igpatlantique $TMPDIR/P.csv  --application="$ODG" --trace
