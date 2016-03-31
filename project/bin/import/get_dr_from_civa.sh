#!/bin/bash

. bin/config.inc

CAMPAGNE=$1

if ! test "$CAMPAGNE"; then
    echo "La campagne est requise"
    exit;
fi

DATA_DIR=data/dr/$CAMPAGNE
TMP_PDF_DIR=$TMPDIR"/dr_"$CAMPAGNE"_pdf"
TMP_CSV_DIR=$TMPDIR"/dr_"$CAMPAGNE"_csv"

mkdir $DATA_DIR 2> /dev/null

echo "Récupération des PDF"
# Récupération des PDF
rm -rf $TMP_PDF_DIR $TMP_PDF_DIR.zip
wget -O $TMP_PDF_DIR.zip $HTTP_CIVA_DATA/DR/$CAMPAGNE.zip
mkdir $TMP_PDF_DIR 2> /dev/null
unzip -d $TMP_PDF_DIR/ $TMP_PDF_DIR.zip
rename 's/(DR_[0-9]{10}_[0-9]{4}).*\.pdf/\1.pdf/' $TMP_PDF_DIR/*.pdf
rsync -av $TMP_PDF_DIR/*.pdf $DATA_DIR/
rm -rf $TMP_PDF_DIR $TMP_PDF_DIR.zip

echo "PDF synchronisés dans $DATA_DIR"

echo "Récupération des CSV"
#Récupération des CSV
rm -rf $TMP_CSV_DIR $TMP_CSV_DIR.csv
wget -O $TMP_CSV_DIR.csv $HTTP_CIVA_DATA/DR/$CAMPAGNE.csv
mkdir $TMP_CSV_DIR 2> /dev/null
cat $TMP_CSV_DIR.csv | grep -v '^"CVI acheteur"' | awk -F '";"' '{ print >> ("'$TMP_CSV_DIR'/DR_" $3 "_'$CAMPAGNE'.csv")}'
rsync -av $TMP_CSV_DIR/*.csv $DATA_DIR/
rm -rf $TMP_CSV_DIR $TMP_CSV_DIR.csv

echo "CSV synchronisés dans $DATA_DIR"
