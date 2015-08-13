#!/bin/bash

CLIENTS_CSV_FILE=$1
FACTURE_CSV_FILE=$2

if ! test $CLIENTS_CSV_FILE; then
    echo "Le fichier CSV des société est requis"
    exit;
fi

if ! test $FACTURE_CSV_FILE; then
    echo "Le fichier CSV des factures est requis"
    exit;
fi

#echo  "#FLG 001" | sed 's/$/\r/' >> $TMP/$VINSIEXPORT
echo "#VER 14" | sed 's/$/\r/'
echo "#DEV EUR" | sed 's/$/\r/'
cat $CLIENTS_CSV_FILE | sort | uniq | perl bin/facture/convertExportSociete2SAGE.pl | iconv -f UTF8 -t IBM437//TRANSLIT | sed 's/$/\r/'
cat $FACTURE_CSV_FILE | perl bin/facture/convertExportFacture2SAGE.pl | iconv -f UTF8 -t IBM437//TRANSLIT | sed 's/$/\r/'
echo "#FIN" | sed 's/$/\r/'