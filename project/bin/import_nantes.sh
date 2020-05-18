#!/bin/bash

. bin/config.inc

mkdir $TMPDIR 2> /dev/null

DATA_DIR=$TMPDIR"/import_"$(date +%Y%m%d%H%M%S)
mkdir $DATA_DIR 2> /dev/null

if ! test "$1"; then
    echo "Chemin du stockage des données";
    exit 1;
fi

cd ..
make clean
make
cd -

ls $WORKINGDIR/data/configuration/nantes | while read jsonFile
do
    php symfony document:delete $(echo $jsonFile | sed 's/\.json//')
    curl -s -X POST -d @data/configuration/nantes/$jsonFile -H "content-type: application/json" http://$COUCHHOST:$COUCHPORT/$COUCHBASE
done

bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/etablissement/_view/all\?reduce\=false
bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/societe/_view/all
bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/compte/_view/all

echo "Récupération des données"
cp -r $1"/" $DATA_DIR"/"

NANTES_IMPORT_TMP=$DATA_DIR"/Nantes"

echo "Traitement du fichier listes_operateurs.txt"
# recode iso88591..utf8 $NANTES_IMPORT_TMP/listes_operateurs.txt
# cat $NANTES_IMPORT_TMP/listes_operateurs.txt | sed 's|EARL DOMAINE DE LA COMBE|DOMAINE DE LA COMBE|' | tr '\r' ' ' | sed 's/ $//' | sed -r 's/(.+)(True|False)$/\1\2£/' | tr '\n' ' ' | tr ';' ' ' | sed -r 's|£\ |\n|g' | sed -r 's|\t|;|g' | sed 's|;Vinificateur;Conditionneur;Eleveur\ |;Vinificateur;Conditionneur;Eleveur\n|' | awk -F ";" 'begin{ cpt=0 }{ print cpt";"$0; cpt++}' | sed 's|;EVV principal;Siret;Forme;|Identifiant ligne;EVV principal;Siret;Forme;|' | sort -t ';' -k 2,2 > $NANTES_IMPORT_TMP/listes_operateurs.csv.tmp

cat $NANTES_IMPORT_TMP/listes_operateurs.propre.v2.csv | sort -t ';' -k 2,2 > $NANTES_IMPORT_TMP/listes_operateurs.csv.tmp


cat $NANTES_IMPORT_TMP/EVV_operateur_archives.csv | cut -d ';' -f 1 | grep -vE '^0$' | sed -r "s|(.+)|grep '\1' $NANTES_IMPORT_TMP/listes_operateurs.propre.csv |" | bash > $NANTES_IMPORT_TMP"/operateurs_archives_trouves.csv.tmp"

cat $NANTES_IMPORT_TMP"/operateurs_archives_trouves.csv.tmp" | cut -d ';' -f 1 | sed -r 's|(.+)|\1;1|' | sort -t ';' -k 1,1  > $NANTES_IMPORT_TMP"/operateurs_archives_trouves.csv"
rm $NANTES_IMPORT_TMP"/operateurs_archives_trouves.csv.tmp"

join -t ";" -1 2 -2 1 -a 1 $NANTES_IMPORT_TMP/listes_operateurs.csv.tmp $NANTES_IMPORT_TMP/operateurs_archives_trouves.csv > $NANTES_IMPORT_TMP/listes_operateurs.notsorted
cat $NANTES_IMPORT_TMP/listes_operateurs.notsorted | sed -r 's|([0-9A-Za-z ]*);([0-9A-Za-z ]+);(.+)|\2;\1;\3|' | sort -t ';' -k 1,1  > $NANTES_IMPORT_TMP/listes_operateurs.csv



echo "CSV listes_operateurs.csv créé :"
sleep 2
cat $NANTES_IMPORT_TMP/listes_operateurs.propre.v2.csv
echo ""
echo ""
sleep 2
echo "Traitement de l'import"
sleep 2

php symfony import:entite-from-csv $NANTES_IMPORT_TMP/listes_operateurs.propre.v2.csv $NANTES_IMPORT_TMP/societe_negoce.csv $NANTES_IMPORT_TMP/EVV_operateur_archives.csv --application="nantes" --trace

echo ""
echo ""
echo "Traitement du fichier habilitations.csv"

cat $NANTES_IMPORT_TMP/habilitations.csv | tail -n +11 > $NANTES_IMPORT_TMP/habilitations_proper_inao.csv
echo "CSV habilitations_proper.csv créé :"
sleep 2
echo ""
cat $NANTES_IMPORT_TMP/habilitations_proper_inao.csv
sleep 2
echo ""

bash bin/delete_from_view.sh http://$COUCHHOST":"$COUCHDBPORT"/"$COUCHBASE/_design/habilitation/_view/historique
sleep 2
echo ""
php symfony import:habilitations-csv-inao $NANTES_IMPORT_TMP/habilitations_proper_inao.csv --application="nantes" --trace
sleep 2
echo ""

recode iso88591..utf8 $NANTES_IMPORT_TMP/Lignes_de_revendication.txt
cat $NANTES_IMPORT_TMP/Lignes_de_revendication.txt | tr '\r' ' ' | sed 's/ $//' | sed -r 's|\t|;|g' > $NANTES_IMPORT_TMP/lignes_de_revendication.csv

recode iso88591..utf8 $NANTES_IMPORT_TMP/VCI_constitue_2018.txt
cat $NANTES_IMPORT_TMP/VCI_constitue_2018.txt | tr '\r' ' ' | sed -r 's|\t|;|g' | grep -v "^EVV principal;" > $NANTES_IMPORT_TMP/VCI_constitue_2018.csv

php symfony import:drev-csv $NANTES_IMPORT_TMP/lignes_de_revendication.csv $NANTES_IMPORT_TMP/VCI_constitue_2018.csv --application="nantes" --trace

echo ""
echo "Import des DR depuis Prodouane"
sleep 2
echo ""
php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/2019_dr_douane.csv --application=nantes


echo ""
echo "Import des DR, SV12 et SV11 depuis VINSI"
sleep 2
echo ""
php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/dr.csv --application=nantes
php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/sv12.csv --application=nantes
php symfony douane:import $URL_EXPORT_REMOTE_OPENDOG/sv11.csv --application=nantes

echo ""
echo "Import des DRev de cette année"
sleep 2
echo ""
php symfony drev:import $URL_EXPORT_REMOTE_OPENDOG/drev.csv --application=nantes
