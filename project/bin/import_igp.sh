#!/bin/bash

if ! test "$1"; then
    echo "Nom du dossier/de l'ODG";
    exit 1;
fi

ODG=$1

. bin/config_$ODG.inc

EXPORT=$2

DATA_DIR=$WORKINGDIR/import/igp/imports/$ODG
mkdir -p $DATA_DIR 2> /dev/null

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
make couchurl=http://$COUCHHOST:$COUCHPORT/$COUCHBASE > /dev/null
cd - > /dev/null

echo "Création des documents de configuration"

ls $WORKINGDIR/data/configuration/$ODG | while read jsonFile
do
    curl -s -X POST -d @data/configuration/$ODG/$jsonFile -H "content-type: application/json" http://$COUCHHOST:$COUCHPORT/$COUCHBASE
done

cp $DATA_DIR/01_operateurs/operateurs.xlsx $DATA_DIR/
cp $DATA_DIR/01_operateurs/operateurs_inactifs.xlsx $DATA_DIR/
cp $DATA_DIR/06_administration/membres.xlsx $DATA_DIR/
cp $DATA_DIR/03_declarations/lots.xlsx $DATA_DIR/
cp $DATA_DIR/03_declarations/lots_primeur.xlsx $DATA_DIR/
cp $DATA_DIR/03_declarations/lots_changements.xlsx $DATA_DIR/
cp $DATA_DIR/03_declarations/traitees/changement_denomination.xls $DATA_DIR/
cp $DATA_DIR/03_declarations/syntheses/declassements.xlsx $DATA_DIR/
cp $DATA_DIR/04_controles_produits/commissions.csv $DATA_DIR/
cp $DATA_DIR/04_controles_produits/gestion_nc.xlsx $DATA_DIR/
cp $DATA_DIR/01_operateurs/apporteurs_de_raisins.xlsx $DATA_DIR/
cp $DATA_DIR/01_operateurs/habilitations.xlsx $DATA_DIR/
cp $DATA_DIR/01_operateurs/historique_DI.xlsx $DATA_DIR/
cp $DATA_DIR/01_operateurs/contacts.xlsx $DATA_DIR/

echo "Import des Opérateurs"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/operateurs.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/operateurs.csv
sed -i 's/Choisir Ville//' $DATA_DIR/operateurs.csv
php symfony import:operateur-ia $DATA_DIR/operateurs.csv --application="$ODG" --trace

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/operateurs_inactifs.xlsx | tr -d "\n" | tr "\r" "\n" | awk -F ";" 'BEGIN { OFS=";"} { $3=$3 ";;"; $21="SUSPENDU"; print $0 }' > $DATA_DIR/operateurs_inactifs.csv
sed -i 's/Choisir Ville//' $DATA_DIR/operateurs_inactifs.csv
php symfony import:operateur-ia $DATA_DIR/operateurs_inactifs.csv --application="$ODG" --trace

echo "Import des Interlocuteurs"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/membres.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/membres.csv
sed -i 's/Choisir Ville//' $DATA_DIR/membres.csv
php symfony import:interlocuteur-ia $DATA_DIR/membres.csv --application="$ODG" --trace

echo "Import Lots"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/lots.xlsx | tr -d "\n" | tr "\r" "\n" | sort -t ";" -k 3,4 -k 14,14 -k 24,24 -k 34,38 > $DATA_DIR/lots.csv # tri identifiant, campagne, type
sed -i 's/Choisir Ville//' $DATA_DIR/lots.csv
sed -i 's/;"200;1+CF80;1";/;"200 1+CF80 1";/' $DATA_DIR/lots.csv
sed -i 's/;"4+CF100;3";/;"4+CF100 3";/' $DATA_DIR/lots.csv

php symfony import:lots-ia $DATA_DIR/lots.csv --application="$ODG" --trace

echo "Identification des Lots Primeur"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/lots_primeur.xlsx | tr -d "\n" | tr "\r" "\n" | sort -t ";" -k 3,4 -k 14,14 -k 24,24 -k 34,38 > $DATA_DIR/lots_primeur.csv
sed -i 's/Choisir Ville//' $DATA_DIR/lots_primeur.csv
sed -i 's/;"200;1+CF80;1";/;"200 1+CF80 1";/' $DATA_DIR/lots_primeur.csv
sed -i 's/;"4+CF100;3";/;"4+CF100 3";/' $DATA_DIR/lots_primeur.csv

php symfony import:lots-primeur-ia $DATA_DIR/lots_primeur.csv --application="$ODG" --trace

echo "Import des Changements de denomination"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/lots_changements.xlsx | tr -d "\n" | tr "\r" "\n" | awk -F ";" 'BEGIN { OFS=";"}{print substr($33, 7, 4) substr($33, 4, 2) substr($33, 1, 2) sprintf("%05d", $1) sprintf("%05d", $2) ";" $23 ";" $29 ";" $30}' | sort | uniq | sort -t ";" -k 1,1 | grep -E "^[0-9]+" > $DATA_DIR/lots_changements.csv

cat $DATA_DIR/changement_denomination.xls | tr -d "\n" | tr -d "\r" | sed "s|</s:Row>|\n|g" | sed -r 's|<s:Data s:Type="[a-Z]+"[ /]*>|;|g' | sed -r 's/<[^<>]*>//g' | sed -r 's/[ ]+/ /g' | sed 's/ ;/;/g' | sed 's/^;//' | sed 's/;CVI;/CVI;/' | awk -F ";" 'BEGIN { OFS=";"}{print substr($10, 7, 4) substr($10, 4, 2) substr($10, 1, 2) sprintf("%05d", $2) sprintf("%05d", $3) ";" $0}' | sort -t ";" -k 1,1 > $DATA_DIR/changement_denomination.csv

join -t ";" -1 1 -2 1 -a 1 $DATA_DIR/changement_denomination.csv $DATA_DIR/lots_changements.csv > $DATA_DIR/changement_denomination_millesime.csv

php symfony import:chgt-denom-ia $DATA_DIR/changement_denomination_millesime.csv --application="$ODG" --trace

echo "Import des Degustations - Commissions"

sed -i 's/\xC2\xA0//g' $DATA_DIR/commissions.csv
php symfony import:commissions-ia $DATA_DIR/commissions.csv --application="$ODG" --trace

echo "Import des Degustations - Non conformité"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/gestion_nc.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/gestion_nc.csv
sed -i 's/4+CF100;3/4+CF100,3/' $DATA_DIR/gestion_nc.csv
sed -i 's/Event ; Oxydé/Event , Oxydé/' $DATA_DIR/gestion_nc.csv
sed -i 's/Maigre ; Oxydé ; /Maigre , Oxydé/' $DATA_DIR/gestion_nc.csv
sed -i 's/Oxydé ; Event ; Usé/Oxydé , Event , Usé/' $DATA_DIR/gestion_nc.csv
sed -i 's/Pas net ; pharmaceutique (camphre), oxydatif/Pas net , pharmaceutique (camphre), oxydatif/' $DATA_DIR/gestion_nc.csv
php symfony import:degustations-non-conformite-ia $DATA_DIR/gestion_nc.csv --application="$ODG" --trace

echo "Import des déclassements"
xlsx2csv -l '\r\n' -d ";" $DATA_DIR/declassements.xlsx | tr -d "\n" | tr "\r" "\n" | sort -t ";" -k 17.7,17.13 -k 17.4,17.5 -k 17.1,17.3 > $DATA_DIR/declassements.csv
php symfony import:declassement-ia $DATA_DIR/declassements.csv --application="$ODG" --trace

echo "Apporteurs de raisins"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/apporteurs_de_raisins.xlsx | tr -d "\n" | tr "\r" "\n" | awk -F ";" 'BEGIN { OFS=";"} { acheteur=$4; $4=""; $3=";Producteur de raisin"; print $0 ";;" acheteur }' | sort | uniq > $DATA_DIR/apporteurs_de_raisins.csv
sed -i 's/Choisir Ville//' $DATA_DIR/apporteurs_de_raisins.csv
php symfony import:operateur-ia $DATA_DIR/apporteurs_de_raisins.csv --application="$ODG" --trace

echo "Habilitations"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/habilitations.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/habilitations.csv
sed -i 's/Choisir Ville//' $DATA_DIR/habilitations.csv
xlsx2csv -l '\r\n' -d ";" $DATA_DIR/historique_DI.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/historique_DI.csv
sed -i 's/Choisir Ville//' $DATA_DIR/historique_DI.csv
php symfony import:habilitation-ia $DATA_DIR/habilitations.csv $DATA_DIR/historique_DI.csv --application="$ODG" --trace

echo "Contacts"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/contacts.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/contacts.csv
sed -i 's/Choisir Ville//' $DATA_DIR/contacts.csv
php symfony import:contact-ia $DATA_DIR/contacts.csv --application="$ODG" --trace

echo "Mise en reputes conforme des lots en attente"

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/mouvement/_view/lotHistory?reduce=false | grep 09_AFFECTABLE_ENATTENTE | awk -F '"' '{if ( $9 < "2020-2021" ) print "php symfony lot:change-statut --application='$ODG' "$8" "$10"-"$12"-"$14" false"}' | bash
