#!/bin/bash

if ! test "$1"; then
    echo "Nom du dossier/de l'ODG";
    exit 1;
fi

ODG=$1

. bin/config_$ODG.inc

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

echo "Import des Opérateurs"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/operateurs.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/operateurs.csv
sed -i 's/Choisir Ville//' $DATA_DIR/operateurs.csv
echo "IdentifiantInterne;Commentaire;Auteur;Date" > $DATA_DIR/operateurs_commentaires.csv
ls $DATA_DIR/01_operateurs/fiches/*_commentaires.html | while read file; do ID=$(echo $file | sed -r 's|.+/||' | cut -d "_" -f 1); echo $ID; cat $file |  tr "\n" " " | sed "s/<tr/\n<tr/g" | sed 's|</tr>|</tr>\n|' | grep "<tr" | sed 's#</td>#|#g' | sed 's#</th>#-#g' | sed 's/<[^>]*>//g' | sed -r 's/(^|#)[ \t]*/\1/g' | sed 's/&nbsp;/ /g' | sed 's/&gt;/>/g' | sed 's/;/./g' | grep -Ev "^ ?;" | grep -vE "^Commentaire-Auteur-Date" | grep -v "^ |" | sed "s/^/$ID|/"; done | sed 's/|/;/g' | grep ";" | cut -d ";" -f 1,2,3,4 >> $DATA_DIR/operateurs_commentaires.csv

php symfony import:operateur-ia-aoc $DATA_DIR/operateurs.csv $DATA_DIR/operateurs_commentaires.csv --application="$ODG" --trace

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/operateurs_inactifs.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/operateurs_inactifs.csv
sed -i 's/Choisir Ville//' $DATA_DIR/operateurs_inactifs.csv
php symfony import:operateur-ia-aoc $DATA_DIR/operateurs_inactifs.csv --application="$ODG" --trace

echo "Habilitations"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/habilitations.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/habilitations.csv
# xlsx2csv -l '\r\n' -d ";" $DATA_DIR/historique_DI.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/historique_DI.csv
# sed -i 's/Choisir Ville//' $DATA_DIR/historique_DI.csv
php symfony import:habilitation-ia-aoc $DATA_DIR/habilitations.csv --application="$ODG"

ls $DATA_DIR/01_operateurs/habilitations_inao/ | while read file; do xls2csv -c ";" "$DATA_DIR/01_operateurs/habilitations_inao/$file"; done > $DATA_DIR/habilitations_inao.csv

echo "Import des zones"

ls $DATA_DIR/07_chais/zones/ | while read file; do cat "$DATA_DIR/07_chais/zones/$file" | tr "\n" " " | tr -d "\r" |  sed -r 's/[ ]+/ /g' | sed 's/&nbsp;//g' | sed 's/&amp;//g' | sed 's/&#194;/Â/g' | sed 's/&#200;/È/g' | sed 's/&#224;/à/g' | sed 's/&#226;/â/g' | sed 's/&#231;/ç/g' | sed 's/&#232;/è/g' | sed 's/&#233;/é/g' | sed 's/&#234;/ê/g' | sed 's/&#244;/ê/g' | sed "s/&#39;/'/g" | sed "s/<tr/\n<tr/g" | sed 's|</tr>|</tr>\n|' | grep "<tr" | sed 's|</td>|;|g' | sed 's|</th>|;|g' | sed 's/<[^>]*>//g' | sed -r 's/^[ \t]+//' | sed -r 's/ ?; ?/;/g' | grep -A 999999999 "RaisonSociale;" | sed "s/^/$(echo $file | sed 's/.html//');/" | grep -v ";;;;$"; done | less > $DATA_DIR/zones.csv

echo "Import des chais"

cat $DATA_DIR/07_chais/*.html | tr "\n" " " | sed "s/<tr/\n<tr/g" | sed 's|</tr>|</tr>\n|' | grep "<tr" | sed 's|</td>|;|g' | sed 's|</th>|;|g' | sed 's/<[^>]*>//g' | sed -r 's/(^|;)[ \t]*/\1/g' | sed 's/&nbsp;/ /g' | grep -Ev "^ ?;" | grep -Ev "^(Zone|ODG|Libelle|Raison Sociale|Nom)" | sed 's/^RaisonSociale/00RaisonSociale/' | sort | uniq > $DATA_DIR/chais.csv
php symfony import:chais $DATA_DIR/chais.csv $DATA_DIR/zones.csv --application="$ODG" --trace

echo "Import des Interlocuteurs"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/membres.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/membres.csv
sed -i 's/Choisir Ville//' $DATA_DIR/membres.csv
php symfony import:interlocuteur-ia $DATA_DIR/membres.csv --application="$ODG" --trace

echo "Import DRev"

for annee in 2022 2021 2020 2019 2018; do php symfony import:documents-douaniers "$annee" --dateimport="$annee-12-10" --application="$ODG"; done

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/drev.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/drev.csv
echo -n > $DATA_DIR/vci.csv
ls $DATA_DIR/03_declarations/vci_* | while read vci_file; do
    MILLESIME=$(echo -n $vci_file | sed -r 's|^.*/vci_||' | sed 's/\.xlsx//')
    xlsx2csv -l '\r\n' -d ";" $vci_file | tr -d "\n" | tr "\r" "\n" | sed "s/^/$MILLESIME;/" >> $DATA_DIR/vci.csv
done;

bash bin/updateviews.sh

php symfony import:drev-ia $DATA_DIR/drev.csv $DATA_DIR/vci.csv --application="$ODG" --trace

echo "Import lots PMC"

bash bin/updateviews.sh

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/lots_pmc.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/lots_pmc.csv
php symfony import:pmc-ia $DATA_DIR/lots_pmc.csv --application="$ODG" --trace

bash bin/updateviews.sh

ls $DATA_DIR/04_controles_produits/commissions/*.html | while read file; do
    PRELIGNE=$(cat $file | tr "\n" " " |  sed -r 's/[ ]+/ /g' | sed 's/&nbsp;//g' | sed 's/&amp;//g' | sed "s/<tr/\n<tr/g" | sed 's|</tr>|</tr>\n|' | grep "<tr" | sed 's|</td>|;|g' | sed 's|</th>|;|g' | sed 's/<[^>]*>//g' | sed -r 's/^[ \t]+//' | sed -r 's/ ?; ?/;/g' | grep -E "^(Code|Date|Année|Adresse|Ville)"  | tr -d "\n")

    cat $file | tr "\n" " " |  sed -r 's/[ ]+/ /g' | sed 's/&nbsp;//g' | sed 's/&amp;//g' | sed 's/<table> <tbody><tr>//g' | sed 's|</tr> </tbody></table>||g' | sed "s/<tr/\n<tr/g" | sed 's|</tr>|</tr>\n|' | grep "<tr" | sed 's|</td>|;|g' | sed 's|</th>|;|g' | sed 's/<[^>]*>//g' | sed -r 's/^[ \t]+//' | sed -r 's/ ?; ?/;/g' | grep -A 999999999 "Echantillon;" | sed 's/N&#176;/N /g' | sed -r "s|^|$PRELIGNE|" > $file.csv

    if ! test -s $file.csv; then
        rm $file;
        rm $file.csv;
    fi
done

cat $DATA_DIR/04_controles_produits/commissions/commission_*.html.csv | grep -vE ";(PAULAT Sophie|ANTOINE Christelle);" | grep -v "www.innov-agro.fr" | grep -v "AOC/Coul./Mill." > $DATA_DIR/commissions_syndicat.csv

echo "Import des commissions syndicats"

php symfony import:commissions-aoc-ia $DATA_DIR/commissions_syndicat.csv --application=centre

echo "Import lots de contrôles"

bash bin/updateviews.sh

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/lots_controle.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/lots_controle.csv
php symfony import:lots-oc-ia $DATA_DIR/lots_controle.csv --application="$ODG" --trace

echo "Import des commissions de contrôles"

cat $DATA_DIR/04_controles_produits/commissions/commission_*.html.csv | grep -E ";(PAULAT Sophie|ANTOINE Christelle);" | grep -v "www.innov-agro.fr" | grep -v "AOC/Coul./Mill." > $DATA_DIR/commissions_controle.csv

php symfony import:commissions-aoc-ia $DATA_DIR/commissions_controle.csv --region="OIVC" --application=centre

echo "Contacts"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/contacts.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/contacts.csv
sed -i 's/Choisir Ville//' $DATA_DIR/contacts.csv
php symfony import:contact-ia $DATA_DIR/contacts.csv --application="$ODG" --trace

echo "Mise en reputes conforme des lots en attente"

# curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/mouvement/_view/lotHistory?reduce=false | grep 09_AFFECTABLE_ENATTENTE | awk -F '"' '{if ( $9 < "2020-2021" ) print "php symfony lot:change-statut --application='$ODG' "$4" "$10"-"$12"-"$14" false"}' | bash

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/mouvement/_view/facture | awk -F '"' '{print $4}' | sort -u | grep '[A-Z]' | while read id ; do php symfony declaration:regenerate-mouvements --onlydeletemouvements=true --application=$ODG $id ; done

php symfony parcellaire:update-aire --application="$ODG" --trace

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/etablissement/_view/all?reduce=false | cut -d '"' -f 4 | while read id; do php symfony import:parcellaire-douanier $id --application=centre --noscrapping=1; done

bash bin/update_comptes_tags.sh
