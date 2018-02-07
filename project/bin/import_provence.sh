#!/bin/bash

. bin/config.inc

curl -X PUT http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE

cd ..
git pull
make clean
make
cd -

curl -X POST -d @data/configuration/provence/config.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/configuration/provence/current.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE


bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/etablissement/_view/all\?reduce\=false
bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/societe/_view/all
bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/compte/_view/all
php symfony import:entite-from-csv /tmp/operateurs_Cotesdeprovence.csv --application="provence" #fichier sur le cloud en csv
# pathCloud/(... path donnees provence)/operateurs_Cotesdeprovence.csv

bash bin/delete_from_view.sh http://127.0.0.1:5984/odgprovence/_design/declaration/_view/tous?reduce=false

php symfony import:parcellaire-from-csv /tmp/20170105_modele_extraction_CVI.csv --application="provence"
#fichier sur le cloud en csv
# pathCloud/(... path donnees provence)/20170105_modele_extraction_CVI.csv
