#!/bin/bash

. bin/config.inc

mkdir $TMPDIR 2> /dev/null

DATA_DIR=$TMPDIR"/import_"$(date +%Y%m%d%H%M%S)
mkdir $DATA_DIR 2> /dev/null

if ! test "$1"; then
    echo "Chemin du stockage des données";
    exit 1;
fi

echo "Récupération des données"
cp -r $1"/" $DATA_DIR"/"

NANTES_IMPORT_TMP=$DATA_DIR"/Nantes"

echo "Traitement du fichier listes_operateurs.txt"
recode iso88591..utf8 $NANTES_IMPORT_TMP/listes_operateurs.txt
cat $NANTES_IMPORT_TMP/listes_operateurs.txt| tr '\r' ' ' | sed 's/ $//' | sed -r 's/(.+)(True|False)$/\1\2£/' | tr '\n' ' ' | tr ';' ' ' | sed -r 's|£\ |\n|g' | sed -r 's|\t|;|g' | sed 's|;Vinificateur;Conditionneur;Eleveur\ |;Vinificateur;Conditionneur;Eleveur\n|' | awk -F ";" 'begin{ cpt=0 }{ print cpt";"$0; cpt++}' | sed 's|;EVV principal;Siret;Forme;|Identifiant ligne;EVV principal;Siret;Forme;|' > $NANTES_IMPORT_TMP/listes_operateurs.csv

echo "CSV listes_operateurs.csv créé :"
sleep 2
cat $NANTES_IMPORT_TMP/listes_operateurs.csv
echo ""
echo ""
sleep 2
echo "Traitement de l'import"
sleep 2

php symfony import:entite-from-csv $NANTES_IMPORT_TMP/listes_operateurs.csv --application="nantes" --trace
