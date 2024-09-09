#!/bin/bash

ODG=ventoux

. bin/config.inc

DATA_DIR=$WORKINGDIR/import/igp/imports/$ODG
mkdir -p $DATA_DIR 2> /dev/null

if test "$1" = "--delete"; then

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

echo "Import des Opérateurs et Habilitations"

#xlsx2csv -l '\r\n' -d ";" $DATA_DIR/ventoux-operateurs-habilites.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/ventoux-operateurs-habilites.csv
php symfony import:operateur-habilitation-ventoux $DATA_DIR/ventoux-operateurs-habilites.csv  --application="$ODG" --trace

echo "Import des opérateurs archivés"

php symfony import:operateur-habilitation-ventoux $DATA_DIR/ventoux-operateurs-habilites_archives.csv  --application="$ODG" --trace --suspendu=1

echo "Import des chais"

ls $DATA_DIR/01_operateurs/fiches/*_identite.html | while read file; do NUM=$(echo -n $file | sed -r 's|.*/||' | sed 's/_identite.html//'); cat $file | tr "\n" " " | sed "s/<tr/\n<tr/g" | sed 's|</tr>|</tr>\n|' | grep "<tr" | sed 's|</td>|;|g' | sed 's|</th>|;|g' | sed 's/<[^>]*>//g' | sed -r 's/(^|;)[ \t]*/\1/g' | sed 's/&nbsp;/ /g' | grep -A 20 "Activité chai" | grep -Ev "^(Nouvelle adresse|Contrôle produit|Standard;Aléatoire)" | grep -Ev "^(Nom de site|Type|Adresse\*|CP\*):" | grep -v "^;$"  | grep -v "^;;$" | grep -v "^$" | sed -r 's/[ ]+/ /g' | sed 's/[ ]*;[ ]*/;/g' | grep -Ev "^Activité chai;" | grep -v ";Nouvelle adresse" | grep -vE "^;[0-9/]*;;;;;;;;" | sed -r "s|^|$NUM;|"; done > $DATA_DIR/01_operateurs/fiches_chais.csv

cat $DATA_DIR/07_chais/*.html | tr "\n" " " | sed "s/<tr/\n<tr/g" | sed 's|</tr>|</tr>\n|' | grep "<tr" | sed 's|</td>|;|g' | sed 's|</th>|;|g' | sed 's/<[^>]*>//g' | sed -r 's/(^|;)[ \t]*/\1/g' | sed 's/&nbsp;/ /g' | grep -Ev "^ ?;" | grep -Ev "^(Zone|ODG|Libelle|Raison Sociale|Nom)" | sed 's/^RaisonSociale/00RaisonSociale/' | sort | uniq > $DATA_DIR/chais.csv
php symfony import:chais $DATA_DIR/chais.csv $DATA_DIR/zones.csv --application="$ODG" --trace

echo "Import des responsables"

ls $DATA_DIR/01_operateurs/fiches/*_identite.html | while read file; do cat $file | grep "_tbResp" | grep "value" | sed 's/.*value="//' |  sed 's/".*//' | tr -d "\n"; echo $file | sed -r 's|.*/|;|' | sed 's/_identite.html//'; done | awk -F ";" '{ print ";" $1 ";;" sprintf("%06d", $2) ";;;;;;;;;;;;;;Responsable"  }' | grep -Ev "^;;;" > $DATA_DIR/membres_responsable.csv
php symfony import:interlocuteur-ia $DATA_DIR/membres_responsable.csv --nocreatesociete=1 --application="$ODG"

echo "Import des contacts"

echo -n > $DATA_DIR/contacts.csv
ls $DATA_DIR/01_operateurs/contacts/*.xlsx | while read file; do
    xlsx2csv -l '\r\n' -d ";" "$file" | tr -d "\n" | tr "\r" "\n" >> $DATA_DIR/contacts.csv;
done

cat $DATA_DIR/contacts.csv | awk -F ";" '{ if($1 == $7) { $7 = "" } if(($6 && $7) || $8) { print $6 ";" $7 ";" $8 ";" $1 ";;;;" $2 ";" $3 ";" $4 ";" $5 ";" $10 ";;" $11 ";" $12 ";;;" $9 }}' | sort | uniq > $DATA_DIR/contacts_formates.csv

php symfony import:interlocuteur-ia $DATA_DIR/contacts_formates.csv --nocreatesociete=1 --application="$ODG"

echo "Import des documents de production"

for annee in 2023 2022 2021 2020 2019 2018; do php symfony import:documents-douaniers "$annee" --dateimport="$annee-12-10" --application="$ODG"; done

echo "Import des drev"

echo -n > $DATA_DIR/drev.csv
ls $DATA_DIR/drev_2.xlsx | sort -r | while read drev_file; do
    xlsx2csv -l '\r\n' -d ";" $drev_file | tr -d "\n" | tr "\r" "\n" >> $DATA_DIR/drev.csv
done;
echo -n > $DATA_DIR/vci.csv
for vci_file in "$DATA_DIR"/03_declarations/vci_*; do
    tail -n +2 "$vci_file" >> "$DATA_DIR"/vci.csv
done
# ls $DATA_DIR/03_declarations/vci_* | while read vci_file; do
#     MILLESIME=$(echo -n $vci_file | sed -r 's|^.*/vci_||' | sed 's/\.xlsx//')
#     xlsx2csv -l '\r\n' -d ";" $vci_file | tr -d "\n" | tr "\r" "\n" | sed "s/^/$MILLESIME;/" >> $DATA_DIR/vci.csv
# done;

bash bin/updateviews.sh

php symfony import:drev-ia $DATA_DIR/drev.csv $DATA_DIR/vci.csv --application="$ODG" --trace

for annee in 2020 2021 2022 2023; do
    #xlsx2csv -l '\r\n' -d ";" $DATA_DIR/drev_ventoux_"$annee".xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/drev_ventoux_"$annee".csv
    php symfony import:drev-ventoux $DATA_DIR/drev_ventoux_"$annee".csv "$annee" --application="$ODG" --trace
done

echo "Parcellaire"

php symfony parcellaire:update-aire --application="$ODG" --trace

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/etablissement/_view/all?reduce=false | cut -d '"' -f 4 | while read id; do php symfony import:parcellaire-douanier $id --application="$ODG" --noscrapping=1; done

for annee in 2023 2024; do
    #xlsx2csv -l '\r\n' -d ";" $DATA_DIR/parcellaire_"$annee".xlsx | tr -d "\n" | tr "\r" "\n" | sed -f $DATA_DIR/cvis_correspondances > $DATA_DIR/parcellaire_"$annee".csv

    echo "Import des declarations d'affections parcellaire, manquant et irrigations"

    php symfony import:parcellaireaffectation-ventoux --env="prod" --application="$ODG" $DATA_DIR/parcellaire_"$annee".csv "$annee"
done

echo "Mise a jour des relations en fonction des documents de production"

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/declaration/_view/export\?reduce\=false | cut -d '"' -f 4 | grep 'DR-\|SV11-\|SV12-' | grep '\-2023' | while read id; do php symfony production:import-relation $id --application="$ODG"; done

echo "Mise à jour des tags de compte"

bash bin/update_comptes_tags.sh
