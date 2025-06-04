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
ls $DATA_DIR/01_operateurs/fiches/*_identite.html | while read file; do cat $file | grep "cblProfil" | grep 'type="checkbox"' | sed "s|<td>|\n|g" | grep 'checked="checked"' | sed 's|.*">||' | sed 's/<.*//' | tr -d "\n"; echo $file | sed -r 's|.*/|;|' | sed 's/_identite.html//'; done > $DATA_DIR/operateurs_categorie.csv

php symfony import:operateur-ia-aoc $DATA_DIR/operateurs.csv $DATA_DIR/operateurs_commentaires.csv $DATA_DIR/operateurs_categorie.csv --application="$ODG" --trace

echo "Import des opérateurs archivés"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/operateurs_inactifs.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/operateurs_inactifs.csv
sed -i 's/Choisir Ville//' $DATA_DIR/operateurs_inactifs.csv
php symfony import:operateur-ia-aoc $DATA_DIR/operateurs_inactifs.csv $DATA_DIR/operateurs_commentaires.csv $DATA_DIR/operateurs_categorie.csv --application="$ODG" --trace

echo "Import des Habilitations"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/habilitations.xlsx | tr -d "\n" | tr "\r" "\n" | grep -v "(F);" > $DATA_DIR/habilitations.csv
# xlsx2csv -l '\r\n' -d ";" $DATA_DIR/historique_DI.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/historique_DI.csv
# sed -i 's/Choisir Ville//' $DATA_DIR/historique_DI.csv
#ls $DATA_DIR/01_operateurs/habilitations_inao/ | while read file; do xls2csv -c ";" "$DATA_DIR/01_operateurs/habilitations_inao/$file"; done > $DATA_DIR/habilitations_inao.csv
php symfony import:habilitation-ia-aoc $DATA_DIR/habilitations.csv $DATA_DIR/habilitations_inao.csv --application="$ODG"


echo "Import des zones"

ls $DATA_DIR/07_chais/zones/ | while read file; do cat "$DATA_DIR/07_chais/zones/$file" | tr "\n" " " | tr -d "\r" |  sed -r 's/[ ]+/ /g' | sed 's/&nbsp;//g' | sed 's/&amp;//g' | sed 's/&#194;/Â/g' | sed 's/&#200;/È/g' | sed 's/&#224;/à/g' | sed 's/&#226;/â/g' | sed 's/&#231;/ç/g' | sed 's/&#232;/è/g' | sed 's/&#233;/é/g' | sed 's/&#234;/ê/g' | sed 's/&#244;/ê/g' | sed "s/&#39;/'/g" | sed "s/<tr/\n<tr/g" | sed 's|</tr>|</tr>\n|' | grep "<tr" | sed 's|</td>|;|g' | sed 's|</th>|;|g' | sed 's/<[^>]*>//g' | sed -r 's/^[ \t]+//' | sed -r 's/ ?; ?/;/g' | grep -A 999999999 "RaisonSociale;" | sed "s/^/$(echo $file | sed 's/.html//');/" | grep -v ";;;;$"; done | less > $DATA_DIR/zones.csv

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

echo "Import des dégustateurs"

ls $DATA_DIR/04_controles_produits/jures/*.xlsx | while read file; do
    REGION=$(echo -n $file | sed -r 's|^.*/jures_||' | sed -r 's/\.xlsx//');
    xlsx2csv -l '\r\n' -d ";" "$file" | tr -d "\n" | tr "\r" "\n" > $file.csv;
    php symfony import:interlocuteur-ia $file.csv --application="$ODG" --region=$REGION
done

echo "Import DRev"

for annee in 2023 2022 2021 2020 2019 2018; do php symfony import:documents-douaniers "$annee" --dateimport="$annee-12-10" --application="$ODG"; done

echo -n > $DATA_DIR/drev.csv
ls $DATA_DIR/drev*.xlsx | sort -r | while read drev_file; do
    xlsx2csv -l '\r\n' -d ";" $drev_file | tr -d "\n" | tr "\r" "\n" >> $DATA_DIR/drev.csv
done;
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
#xls2csv -c ";" $DATA_DIR/lots_synthese.xls > $DATA_DIR/lots_synthese.csv
php symfony import:lots-oc-ia $DATA_DIR/lots_controle.csv $DATA_DIR/lots_synthese.csv --application="$ODG" --trace

echo "Import des commissions de contrôles"

cat $DATA_DIR/04_controles_produits/commissions/commission_*.html.csv | grep -E ";(PAULAT Sophie|ANTOINE Christelle);" | grep -v "www.innov-agro.fr" | grep -v "AOC/Coul./Mill." > $DATA_DIR/commissions_controle.csv

php symfony import:commissions-aoc-ia $DATA_DIR/commissions_controle.csv --region="OIVC" --application=centre

echo "Contacts"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/contacts.xlsx | tr -d "\n" | tr "\r" "\n" > $DATA_DIR/contacts.csv
sed -i 's/Choisir Ville//' $DATA_DIR/contacts.csv
php symfony import:contact-ia $DATA_DIR/contacts.csv --application="$ODG" --trace

echo "Parcellaire"

php symfony parcellaire:update-aire --application="$ODG" --trace

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/etablissement/_view/all?reduce=false | cut -d '"' -f 4 | while read id; do php symfony import:parcellaire-douanier $id --application="$ODG" --noscrapping=1; done

echo "Import des declarations de pieds manquants"

xlsx2csv -l '\r\n' -d ";" $DATA_DIR/02_recoltes/pieds_manquants/pieds_manquants_2022.xlsx | tr -d "\n" | tr "\r" "\n" | sed 's/^/2022;/' > $DATA_DIR/pieds_manquants.csv

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/declaration/_view/tous\?reduce\=false | cut -d '"' -f 4 | grep 'DR-' | grep '\-2022' | awk -F '-' '{print "php symfony import:parcellairemanquant-ia-aoc --application=centre "$2" "$3""}' | sort -u | sed "s|$| $DATA_DIR/pieds_manquants.csv|" | bash

echo "Mise en reputes conforme des lots en attente"

#curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/mouvement/_view/lotHistory?reduce=false | grep 09_MANQUEMENT_EN_ATTENTE | grep '"initial_type":"PMC"' | grep '"document_ordre":"02"' | awk -F '"' '{ print "php symfony lot:lever-convormite --application='$ODG' "$8" "$10"-"$12"-"$14" \"PMCNC non trouvé lors de la reprise historique\"" }' | bash

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/mouvement/_view/facture | awk -F '"' '{print $4}' | sort -u | grep '[A-Z]' | while read id ; do php symfony declaration:regenerate-mouvements --onlydeletemouvements=true --application=$ODG $id ; done

echo "Mise a jour des relations en fonction des documents de production"

curl -s http://$COUCHHOST:$COUCHPORT/$COUCHBASE/_design/declaration/_view/tous\?reduce\=false | cut -d '"' -f 4 | grep 'DR-\|SV11-\|SV12-' | grep '\-2022' | while read id; do php symfony production:import-relation $id --application=centre; done

echo "Import des factures"

php symfony import:factures-ia --application="$ODG" --trace --region=CHATEAUMEILLANT $DATA_DIR/08_facture/ChateauMeillant.csv
php symfony import:factures-ia --application="$ODG" --trace --region=GIENNOIS $DATA_DIR/08_facture/Giennois.csv
php symfony import:factures-ia --application="$ODG" --trace --region=MENETOUSALON $DATA_DIR/08_facture/Menetousalon.csv
php symfony import:factures-ia --application="$ODG" --trace --region=OIVC $DATA_DIR/08_facture/OIVC.csv
php symfony import:factures-ia --application="$ODG" --trace --region=POUILLY $DATA_DIR/08_facture/Pouilly.csv
php symfony import:factures-ia --application="$ODG" --trace --region=QUINCY $DATA_DIR/08_facture/Quincy.csv
php symfony import:factures-ia --application="$ODG" --trace --region=REUILLY $DATA_DIR/08_facture/Reuilly.csv
php symfony import:factures-ia --application="$ODG" --trace --region=SANCERRE $DATA_DIR/08_facture/Sancerre.csv

echo "Mise à jour des tags de compte"

bash bin/update_comptes_tags.sh
