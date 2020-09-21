#!/bin/bash

. bin/config.inc

mkdir $TMPDIR 2> /dev/null

DATA_DIR=$TMPDIR"/import_"$(date +%Y%m%d%H%M%S)
mkdir $DATA_DIR 2> /dev/null

if ! test "$1"; then
    echo "Chemin du stockage des données";
    exit 1;
fi

cd ..
make clean
make
cd -

ls $WORKINGDIR/data/configuration/igp13 | while read jsonFile
do
    php symfony document:delete $(echo $jsonFile | sed 's/\.json//')
    curl -s -X POST -d @data/configuration/igp13/$jsonFile -H "content-type: application/json" http://$COUCHHOST:$COUCHPORT/$COUCHBASE
done

bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/etablissement/_view/all\?reduce\=false
bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/societe/_view/all
bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/compte/_view/all

echo "Récupération des données"
cp -r $1"/" $DATA_DIR"/"

IGP13_IMPORT_TMP=$DATA_DIR"/Igp13"

echo "CSV Opérateur :"
sleep 2
cat $IGP13_IMPORT_TMP/operateurs.csv
echo ""
echo ""
sleep 2
echo "Traitement de l'import"
sleep 2

php symfony import:entite-from-csv $IGP13_IMPORT_TMP/operateurs.csv --application="igp13" --trace


