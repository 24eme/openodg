#!/bin/bash

. bin/config.inc

mkdir $TMPDIR 2> /dev/null

ODG=igp13

DATA_DIR=$TMPDIR/import_$ODG
mkdir $DATA_DIR 2> /dev/null

if ! test "$1"; then
    echo "Chemin du stockage des données";
    exit 1;
fi

if test "$2" = "--delete"; then

    echo -n "Delete database http://$COUCHHOST:$COUCHPORT/$COUCHBASE, type database name to confirm ($COUCHBASE) : "
    read databasename

    if test "$databasename" = "$COUCHBASE"; then
        curl -X DELETE http://$COUCHHOST:$COUCHPORT/$COUCHBASE
    else
        echo "Delete database cancel"
    fi
fi

curl -sX PUT http://$COUCHHOST:$COUCHPORT/$COUCHBASE

cd ..
make clean
make
cd -

ls $WORKINGDIR/data/configuration/$ODG | while read jsonFile
do
    curl -s -X POST -d @data/configuration/$ODG/$jsonFile -H "content-type: application/json" http://$COUCHHOST:$COUCHPORT/$COUCHBASE
done

echo "Récupération des données"
rsync -av $1 $DATA_DIR/

echo "Opérateurs"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/operateurs.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/operateurs.csv

php symfony import:operateur-ia $DATA_DIR/operateurs.csv --application="$ODG" --trace
