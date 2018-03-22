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

curl -X POST -d @data/configuration/provence/config.json  -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/configuration/provence/current.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/configuration/provence/compte.json  -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE
curl -X POST -d @data/configuration/provence/societe.json -H "content-type: application/json"   http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE

#bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/etablissement/_view/all\?reduce\=false
#bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/societe/_view/all
#bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/compte/_view/all



cat $DATA_DIR/20180315_liste_operateur.utf8$TEST.csv | tr "\n" "#" | sed -r "s/(;[0-9]{1})#/\1\n/g" | sed -r "s/(;\"Archivé\")#/\1\n/g" > $DATA_DIR/20180315_liste_alloperateur.utf8$TEST.csv

cat $DATA_DIR/20180315_liste_alloperateur.utf8$TEST.csv | grep -E '(;"N83";|;"N13";)' > $DATA_DIR/20180315_liste_negociantsvinificateurs.utf8$TEST.csv
cat $DATA_DIR/20180315_liste_alloperateur.utf8$TEST.csv | grep -vE '(;"N83";|;"N13";)' > $DATA_DIR/20180315_liste_nonnegociantsvinificateurs.utf8$TEST.csv



cat $DATA_DIR/20180315_liste_nonnegociantsvinificateurs.utf8$TEST.csv | grep -E '(;"CC 83";|;"CC 13";)' > $DATA_DIR/20180315_liste_cavecooperative.utf8$TEST.csv
cat $DATA_DIR/20180315_liste_nonnegociantsvinificateurs.utf8$TEST.csv | grep -Ev '(;"CC 83";|;"CC 13";)' > $DATA_DIR/20180315_liste_noncavecooperative.utf8$TEST.csv



cat $DATA_DIR/20180315_liste_noncavecooperative.utf8$TEST.csv | grep -E '(;"CP 83";|;"CP 13";)' > $DATA_DIR/20180315_liste_caveparticulieres.utf8$TEST.csv
cat $DATA_DIR/20180315_liste_noncavecooperative.utf8$TEST.csv | grep -vE '(;"CP 83";|;"CP 13";)' > $DATA_DIR/20180315_liste_noncaveparticulieres.utf8$TEST.csv

cat $DATA_DIR/20180315_liste_noncaveparticulieres.utf8$TEST.csv | sed 's/;;/;"";/g' | sed 's/;;/;"";/g' | sed 's/;;/;"";/g' | sed -r 's/;([0-9]+);/;"\1";/g' | sed -r 's/;([0-9]+);/;"\1";/g' | sed -r 's/;([0-9]{2}\/[0-9]{2}\/[0-9]{4});/;"\1";/g' | sed -r 's/;([0-9]+)$/;"\1"/g' > $DATA_DIR/20180315_liste_noncaveparticulieres.quoted.utf8$TEST.csv

cat $DATA_DIR/20180315_liste_noncaveparticulieres.quoted.utf8$TEST.csv | awk -F '";"' '{ if($9) print $0; }' > $DATA_DIR/20180315_liste_evv.utf8$TEST.csv

cat $DATA_DIR/20180315_liste_noncaveparticulieres.quoted.utf8$TEST.csv | awk -F '";"' '{ if(!$9) print $0; }' > $DATA_DIR/20180315_liste_nonevv.utf8$TEST.csv



cat $DATA_DIR/20180315_liste_nonevv.utf8$TEST.csv | grep -v ';"Producteur de raisins";' | sed -r 's|(.*)|\1;"NEGOCIANT"|' > $DATA_DIR/20180315_liste_negociants.utf8$TEST.csv

cat $DATA_DIR/20180315_liste_nonevv.utf8$TEST.csv | grep ';"Producteur de raisins";' > $DATA_DIR/20180315_liste_bailleurs.utf8$TEST.csv

cat $DATA_DIR/20180315_liste_evv.utf8$TEST.csv | grep ';"Autre";' > $DATA_DIR/20180315_liste_evvAutres.utf8$TEST.csv
cat $DATA_DIR/20180315_liste_evv.utf8$TEST.csv | grep -v ';"Autre";' > $DATA_DIR/20180315_liste_evvApporteurs.utf8$TEST.csv

echo "Ordre d'import"
echo "CAVECOOP " `ls $DATA_DIR/20180315_liste_cavecooperative.utf8$TEST.csv`
echo "NEGOCIANTS VINIFICATEURS " `ls $DATA_DIR/20180315_liste_negociantsvinificateurs.utf8$TEST.csv`
echo "CAVEPARTICULIERE " `ls $DATA_DIR/20180315_liste_caveparticulieres.utf8$TEST.csv`
echo "NEGOCIANTS " `ls $DATA_DIR/20180315_liste_negociants.utf8$TEST.csv`
echo "BAILLEURS " `ls $DATA_DIR/20180315_liste_bailleurs.utf8$TEST.csv`
echo "AUTRES " `ls $DATA_DIR/20180315_liste_evvAutres.utf8$TEST.csv`
echo "APPORTEURS " `ls $DATA_DIR/20180315_liste_evvApporteurs.utf8$TEST.csv`

echo  "1/ IMPORT DES CAVECOOP"
php symfony import:entite-from-csv $DATA_DIR/20180315_liste_cavecooperative.utf8$TEST.csv --application="provence" --trace

echo  "2/ IMPORT DES NEGOCIANTS VINIFICATEURS";
php symfony import:entite-from-csv $DATA_DIR/20180315_liste_negociantsvinificateurs.utf8$TEST.csv --application="provence" --trace


echo  "3/ IMPORT DES CAVEPARTICULIERE"
php symfony import:entite-from-csv $DATA_DIR/20180315_liste_caveparticulieres.utf8$TEST.csv --application="provence" --trace

echo  "4/ IMPORT DES NEGOCIANTS"
php symfony import:entite-from-csv $DATA_DIR/20180315_liste_negociants.utf8$TEST.csv --application="provence" --trace

echo  "5/ IMPORT DES BAILLEURS"
php symfony import:entite-from-csv $DATA_DIR/20180315_liste_bailleurs.utf8$TEST.csv --application="provence" --trace

echo  "6/ IMPORT DES AUTRES"
php symfony import:entite-from-csv $DATA_DIR/20180315_liste_evvAutres.utf8$TEST.csv --application="provence" --trace

echo  "7/ IMPORT DES APPORTEURS"
php symfony import:entite-from-csv $DATA_DIR/20180315_liste_evvApporteurs.utf8$TEST.csv --application="provence" --trace

echo  "IMPORT DES INTERLOCUTEUR"
php symfony import:interlocuteurs-from-csv $DATA_DIR/20180315_contacts_interlocuteurs.utf8.csv --application="provence" --trace

echo  "IMPORT PARCELLAIRE"
php symfony import:parcellaire-from-csv $DATA_DIR/20180208_parcellaire_aoc_operateurs_identifies.csv.utf8$TEST.csv "2018-02-08" --application="provence"  --trace

echo  "IMPORT HABILITATION"
php symfony import:habilitation-from-csv $DATA_DIR/20180315_liste_operateur.utf8$TEST.csv --application="provence"  --trace

echo "IMPORT LOGIN CIVP"
cat $DATA_DIR/20180315_login_civp.csv | awk -F ';' '{ print "php symfony compte:add-login-alternatif COMPTE-" $1 " " $3 " --application=provence" }' | bash
cat $DATA_DIR/20180315_login_civp.csv | awk -F ';' '{ print "php symfony compte:add-login-alternatif COMPTE-" $1 " " $2 " --application=provence" }' | bash
