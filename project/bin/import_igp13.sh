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

cd ..
make clean
make
cd -

curl -s -X DELETE $COUCHTEST
curl -s -X PUT $COUCHTEST

ls $WORKINGDIR/data/configuration/$ODG | while read jsonFile
do
    curl -s -X POST -d @data/configuration/$ODG/$jsonFile -H "content-type: application/json" http://$COUCHHOST:$COUCHPORT/$COUCHBASE
done

# bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/etablissement/_view/all\?reduce\=false
# bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/societe/_view/all
# bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/compte/_view/all

echo "Récupération des données"
rsync -av $1 $DATA_DIR/

echo "Opérateurs"

xlsx2csv -d ";" $DATA_DIR/operateurs.xlsx > $DATA_DIR/operateurs.csv

php symfony import:operateur-ia $DATA_DIR/operateurs.csv --application="$ODG" --trace
