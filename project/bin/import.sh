#!/bin/bash

task=$1

cd ..
git pull
make clean
make
cd -
. bin/config.inc

if test $task = "CONTACTS" ; then

curl -X POST -d @data/configuration/rhone/config_previous.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/configuration/rhone/config.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/configuration/rhone/current.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/COMPTE-connexion.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE

php symfony cc
bash bin/import_identites.sh ~/odgrhone_identite_antsys.xml.gz > /tmp/importEntites_$$.log
php symfony import:etablissements-cvis  --application="declaration" ~/scrapping/data/cvi.csv > /tmp/importCvis_$$.log
php symfony import:Habilitations --application="declaration" ~/scrapping/data/habilitation.csv  ~/scrapping/data/dossiers.csv > /tmp/habilitation_$$.log
php symfony import:Chais --application="declaration"  ~/scrapping/data/chais.csv > /tmp/importChais_$$.log
php symfony import:relations-etablissements --application="declaration" ~/scrapping/data/bailleurs_metayers_dr.csv > /tmp/importMetayers_$$.log
php symfony import:repreneurs --application="declaration" ~/scrapping/data/reprise_repreneur.csv > /tmp/importRepreneurs_$$.log
php symfony import:Commentaires --application="declaration" ~/scrapping/data/commentaires.csv > /tmp/importCommentaires_$$.log

fi

if test $task = "DOCUMENTS" ; then

bash bin/import_fichiers.sh /home/actualys/prodouane_scrapy/documents/ > /tmp/importDouane_$$.log

php symfony import:AntsysDRev --application="declaration" ~/scrapping/data/drev.csv > /tmp/importDRev_$$.log

php symfony compte:updateTagsFromHabilitations --application="declaration" > /tmp/habilitation_tags_$$.log

bash bin/importDocuments.sh ~/prodouane_scrapy/phantomjs_scrapping/data/documents.csv ~/prodouane_scrapy/phantomjs_scrapping/documents/ > /tmp/documents_$$.log

fi
