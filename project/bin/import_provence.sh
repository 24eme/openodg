#!/bin/bash

. bin/config.inc

DATA_DIR=$TMPDIR/donnees_odgprovence

if ! test "$1"; then
    echo "Chemin du stockage des données";
    exit 1;
fi

TEST=""
if test "$2"; then
    echo "-----------------"
    echo "MODE TEST ===> ON"
    echo "-----------------"
    TEST=".test"
fi

echo "Récupération des données"

scp $1 $DATA_DIR.tar.xz

echo "Désarchivage"
rm -rf $DATA_DIR 2>/dev/null
mkdir $DATA_DIR 2>/dev/null
cd $DATA_DIR
tar xf $DATA_DIR.tar.xz
cd -

curl -X PUT http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE

cd ..
git pull
make clean
make
cd -

curl -X POST -d @data/configuration/provence/config.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/configuration/provence/current.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE

#bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/etablissement/_view/all\?reduce\=false
#bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/societe/_view/all
#bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/compte/_view/all

php symfony import:entite-from-csv $DATA_DIR/20180215_liste_operateur.utf8$TEST.csv --application="provence" #fichier sur le cloud en csv

#bash bin/delete_from_view.sh http://127.0.0.1:5984/odgprovence/_design/declaration/_view/tous?reduce=false

php symfony import:parcellaire-from-csv $DATA_DIR/20180208_parcellaire_aoc_operateurs_identifies.csv.utf8$TEST.csv --application="provence"
