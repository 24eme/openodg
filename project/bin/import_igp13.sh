#!/bin/bash

. bin/config.inc

mkdir $TMPDIR 2> /dev/null

ODG=igp13


echo "Export données"
cd $WORKINGDIR/import/igp/
bash scrapping.sh
cd $WORKINGDIR

DATA_DIR=$TMPDIR/import_$ODG
mkdir $DATA_DIR 2> /dev/null

if ! test "$1"; then
    echo "Nom du dossier";
    exit 1;
fi

if test "$2" = "--delete"; then

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
make > /dev/null
cd - > /dev/null

echo "Création des documents de configuration"

ls $WORKINGDIR/data/configuration/$ODG | while read jsonFile
do
    curl -s -X POST -d @data/configuration/$ODG/$jsonFile -H "content-type: application/json" http://$COUCHHOST:$COUCHPORT/$COUCHBASE
done

rsync -a $DOCUMENTSDIR$1/ $DATA_DIR/

echo "Import des Opérateurs"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/operateurs.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/operateurs.csv
php symfony import:operateur-ia $DATA_DIR/operateurs.csv --application="$ODG" --trace

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/apporteurs_de_raisins.xlsx | tr -d "\n" | tr "\r" "\n" | awk -F ";" 'BEGIN { OFS=";"} { $4=""; $3=";Producteur de raisin"; print $0 }' | sort | uniq > $DATA_DIR/apporteurs_de_raisins.csv
php symfony import:operateur-ia $DATA_DIR/apporteurs_de_raisins.csv --application="$ODG" --trace

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/operateurs_inactifs.xlsx | tr -d "\n" | tr "\r" "\n" | awk -F ";" 'BEGIN { OFS=";"} { $3=$3 ";;"; $21="SUSPENDU"; print $0 }' > $DATA_DIR/operateurs_inactifs.csv
php symfony import:operateur-ia $DATA_DIR/operateurs_inactifs.csv --application="$ODG" --trace



echo "Contacts"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/contacts.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/contacts.csv
php symfony import:contact-ia $DATA_DIR/contacts.csv --application="$ODG" --trace



echo "Import des interlocuteurs"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/membres.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/membres.csv
php symfony import:interlocuteur-ia $DATA_DIR/membres.csv --application="$ODG" --trace

echo "Habilitations"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/habilitations.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/habilitations.csv
php symfony import:habilitation-ia $DATA_DIR/habilitations.csv --application="$ODG" --trace
