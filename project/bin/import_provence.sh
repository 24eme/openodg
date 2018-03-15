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

# ls $DATA_DIR/20180306_liste_operateur.utf8$TEST.csv
#
# cat $DATA_DIR/20180306_liste_operateur.utf8$TEST.csv | sed 's/;;/;"";/g' | sed 's/;;/;"";/g' | sed 's/;;/;"";/g' | sed 's/;$/;"/' | sed -r 's/;([0-9]+);/;"\1";/g' | sed -r 's/;([0-9]+);/;"\1";/g' | sed -r 's/;([0-9]{2}\/[0-9]{2}\/[0-9]{4});/;"\1";/g' | sed 's/^"//' | sed -r 's/;([0-9]+)$/;"\1/g' | awk -F '";"' '{ print  "\""$27"\";" $28 }' | grep CDP | sort | uniq | sed 's|;1$|;"COOPERATIVE"|' | sed 's|;0$|;"NEGOCIANT"|' > $DATA_DIR/20180306_liste_cavecoop_nego.utf8$TEST.csv
#
#
#
# cat $DATA_DIR/20180306_liste_operateur.utf8$TEST.csv | sort | uniq > $DATA_DIR/20180306_liste_operateur.utf8$TEST.csv.sorted
#
# join -t ";" -1 1 -2 1 -a 1 $DATA_DIR/20180306_liste_operateur.utf8$TEST.csv.sorted $DATA_DIR/20180306_liste_cavecoop_nego.utf8$TEST.csv > $DATA_DIR/20180306_liste_alloperateur.utf8$TEST.csv

cat $DATA_DIR/20180306_liste_operateur.utf8$TEST.csv > $DATA_DIR/20180306_liste_alloperateur.utf8$TEST.csv

ls $DATA_DIR/20180306_liste_alloperateur.utf8$TEST.csv

cat $DATA_DIR/20180306_liste_alloperateur.utf8$TEST.csv | grep -E '(;"N83";|;"N13";)' > $DATA_DIR/20180306_liste_negociantsvinificateurs.utf8$TEST.csv
cat $DATA_DIR/20180306_liste_alloperateur.utf8$TEST.csv | grep -vE '(;"N83";|;"N13";)' > $DATA_DIR/20180306_liste_nonnegociantsvinificateurs.utf8$TEST.csv



cat $DATA_DIR/20180306_liste_nonnegociantsvinificateurs.utf8$TEST.csv | grep -E '(;"CC 83";|;"CC 13";)' > $DATA_DIR/20180306_liste_cavecooperative.utf8$TEST.csv
cat $DATA_DIR/20180306_liste_nonnegociantsvinificateurs.utf8$TEST.csv | grep -Ev '(;"CC 83";|;"CC 13";)' > $DATA_DIR/20180306_liste_noncavecooperative.utf8$TEST.csv



cat $DATA_DIR/20180306_liste_noncavecooperative.utf8$TEST.csv | grep -E '(;"CP 83";|;"CP 13";)' > $DATA_DIR/20180306_liste_caveparticulieres.utf8$TEST.csv
cat $DATA_DIR/20180306_liste_noncavecooperative.utf8$TEST.csv | grep -vE '(;"CP 83";|;"CP 13";)' > $DATA_DIR/20180306_liste_noncaveparticulieres.utf8$TEST.csv

cat $DATA_DIR/20180306_liste_noncaveparticulieres.utf8$TEST.csv | sed 's/;;/;"";/g' | sed 's/;;/;"";/g' | sed 's/;;/;"";/g' | sed -r 's/;([0-9]+);/;"\1";/g' | sed -r 's/;([0-9]+);/;"\1";/g' | sed -r 's/;([0-9]{2}\/[0-9]{2}\/[0-9]{4});/;"\1";/g' | sed -r 's/;([0-9]+)$/;"\1/g' > $DATA_DIR/20180306_liste_noncaveparticulieres.quoted.utf8$TEST.csv

cat $DATA_DIR/20180306_liste_noncaveparticulieres.quoted.utf8$TEST.csv | awk -F '";"' '{ if($9) print $0; }' > $DATA_DIR/20180306_liste_evv.utf8$TEST.csv

cat $DATA_DIR/20180306_liste_noncaveparticulieres.quoted.utf8$TEST.csv | awk -F '";"' '{ if(!$9) print $0; }' > $DATA_DIR/20180306_liste_nonevv.utf8$TEST.csv



cat $DATA_DIR/20180306_liste_nonevv.utf8$TEST.csv | grep -v ';"Producteur de raisins";' | sed -r 's|(.*)|\1;"NEGOCIANT"|' > $DATA_DIR/20180306_liste_negociants.utf8$TEST.csv

cat $DATA_DIR/20180306_liste_nonevv.utf8$TEST.csv | grep ';"Producteur de raisins";' > $DATA_DIR/20180306_liste_bailleurs.utf8$TEST.csv

cat $DATA_DIR/20180306_liste_evv.utf8$TEST.csv | grep ';"Autre";' > $DATA_DIR/20180306_liste_evvAutres.utf8$TEST.csv
cat $DATA_DIR/20180306_liste_evv.utf8$TEST.csv | grep -v ';"Autre";' > $DATA_DIR/20180306_liste_evvApporteurs.utf8$TEST.csv

echo  "1/ IMPORT DES CAVECOOP"
php symfony import:entite-from-csv $DATA_DIR/20180306_liste_cavecooperative.utf8$TEST.csv --application="provence" --trace

echo  "2/ IMPORT DES NEGOCIANTS VINIFICATEURS";
php symfony import:entite-from-csv $DATA_DIR/20180306_liste_negociantsvinificateurs.utf8$TEST.csv --application="provence" --trace


echo  "3/ IMPORT DES CAVEPARTICULIERE"
php symfony import:entite-from-csv $DATA_DIR/20180306_liste_caveparticulieres.utf8$TEST.csv --application="provence" --trace

echo  "4/ IMPORT DES NEGOCIANTS"
php symfony import:entite-from-csv $DATA_DIR/20180306_liste_negociants.utf8$TEST.csv --application="provence" --trace

echo  "5/ IMPORT DES BAILLEURS"
php symfony import:entite-from-csv $DATA_DIR/20180306_liste_bailleurs.utf8$TEST.csv --application="provence" --trace

echo  "6/ IMPORT DES AUTRES"
php symfony import:entite-from-csv $DATA_DIR/20180306_liste_evvAutres.utf8$TEST.csv --application="provence" --trace

echo  "7/ IMPORT DES APPORTEURS"
php symfony import:entite-from-csv $DATA_DIR/20180306_liste_evvApporteurs.utf8$TEST.csv --application="provence" --trace


echo  "IMPORT PARCELLAIRE"
php symfony import:parcellaire-from-csv $DATA_DIR/20180208_parcellaire_aoc_operateurs_identifies.csv.utf8$TEST.csv "2018-02-08" --application="provence"

echo  "IMPORT HABILITATION"
php symfony import:habilitation-from-csv $DATA_DIR/20180306_liste_operateur.utf8$TEST.csv --application="provence"

echo "IMPORT LOGIN CIVP"

cat $DATA_DIR/20180315_logins_civp.csv | awk -F ';' '{ print "php symfony compte:add-login-alternatif COMPTE-" $1 " " $3 " --application=provence" }' | bash
cat $DATA_DIR/20180315_logins_civp.csv | awk -F ';' '{ print "php symfony compte:add-login-alternatif COMPTE-" $1 " " $2 " --application=provence" }' | bash
