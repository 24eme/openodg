#!/bin/bash

. bin/config.inc

#Obtient la liste des societes
curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/societe/_view/all | sed 's/{"id":"//' | cut -d '"' -f 1 | grep SOCIETE > $TMPDIR/societeToMigrate.csv

#analyse globale
cat $TMPDIR/societeToMigrate.csv | sed -r 's|(.*)|php symfony societe:migate-all-comptes \1 --application="rhone" --verbose=0|' | bash > $TMPDIR/societeToMigrateAnalyseGlobale.csv

#analyse en profondeur
cat $TMPDIR/societeToMigrate.csv | sed -r 's|(.*)|php symfony societe:migate-all-comptes \1 --application="rhone" --verbose=1|' | bash > $TMPDIR/societeToMigrateAnalyseProfondeur.csv



#Obtient la liste des etablissements
curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/etablissement/_view/all?reduce=false | sed 's/{"id":"//' | cut -d '"' -f 1 | grep ETABLISSEMENT > $TMPDIR/etablissementToMigrate.csv

#analyse globale
cat $TMPDIR/etablissementToMigrate.csv | sed -r 's|(.*)|php symfony etablissement:migate-all-comptes \1 --application="rhone" --verbose=0|' | bash > $TMPDIR/etablissementToMigrateAnalyseGlobale.csv

#analyse en profondeur
cat $TMPDIR/etablissementToMigrate.csv | sed -r 's|(.*)|php symfony etablissement:migate-all-comptes \1 --application="rhone" --verbose=1|' | bash > $TMPDIR/etablissementToMigrateAnalyseProfondeur.csv



## Après analyse et réparation des synchros on enregistre les informations

cat $TMPDIR/societeToMigrate.csv | sed -r 's|(.*)|php symfony societe:migate-all-comptes \1 --application="rhone" --verbose=0 --withSave=1|' | bash > $TMPDIR/societesMigration.log

cat $TMPDIR/etablissementToMigrate.csv | sed -r 's|(.*)|php symfony etablissement:migate-all-comptes \1 --application="rhone" --verbose=0 --withSave=1|' | bash > $TMPDIR/etablissementsMigration.log

sudo service logstash stop
sudo pkill -9 -u logstash

ps aux | grep logsta

sudo rm /var/lib/logstash/odgprovence_couchdb_seq
bash bin/elastic2_configure.sh
sudo service logstash start
