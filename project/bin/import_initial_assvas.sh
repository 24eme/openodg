#! /bin/bash

. bin/config.inc

mkdir $TMPDIR 2> /dev/null

echo "Mise à jour des vues"

cd ..
make clean
make
cd - || exit

echo "Création de la conf"

ls $WORKINGDIR/data/configuration/assvas | while read jsonFile
do
    php symfony document:delete $(echo $jsonFile | sed 's/\.json//')
    curl -s -X POST -d @data/configuration/nantes/$jsonFile -H "content-type: application/json" http://$COUCHHOST:$COUCHPORT/$COUCHBASE
done

echo "Import des établissements"

bash bin/update_etablissement_assvas.sh

echo "Création des aires de parcelles"

php symfony parcellaire:update-aire $SYMFONYTASKOPTIONS
