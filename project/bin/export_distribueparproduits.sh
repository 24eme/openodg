#!/bin/bash

# Mode multi app
if ! test -f $(echo $0 | sed 's/[^\/]*$//')config.inc && ! test $1 ; then
    ls . $(echo $0 | sed 's/[^\/]*$//') | grep "config_" | grep ".inc$" | sed 's/config_//' | sed 's/\.inc//' | while read app; do
        bash $(echo $0 | sed 's/[^\/]*$//')export_distribueparproduits.sh $app;
    done
    exit 0;
fi

if ! test $1 ; then
    . $(echo $0 | sed 's/[^\/]*$//')config.inc
else
    . $(echo $0 | sed 's/[^\/]*$//')config_"$1".inc
fi

EXPORTGLOBALDIR=web/exports_igp
EXPORTAPPDIR=web/exports_"$1"
EXPORTDIRFORGLOBAL=$EXPORTAPPDIR/GLOBAL

if [ -d "$EXPORTDIRFORGLOBAL" ]; then
  rm -rf $EXPORTDIRFORGLOBAL
fi

mkdir $EXPORTDIRFORGLOBAL 2> /dev/null

for file in $(find "$EXPORTGLOBALDIR" -maxdepth 1 -type f -name "*.csv")
do
  FILENAME="$(basename $file)"
  if [ ! -f "$EXPORTDIRFORGLOBAL/$FILENAME" ]; then
    head -n 1 $file > $EXPORTDIRFORGLOBAL/$FILENAME
  fi
  cat $file | grep -E $HASHPRODUIT --binary-files=text >> $EXPORTDIRFORGLOBAL/$FILENAME
done

for type in dr sv11 sv12 ; do
    doc_output=$EXPORTDIRFORGLOBAL"/"$type".csv.tmp"
    doc_globalfile=$EXPORTGLOBALDIR"/"$type".csv"
    head -n 1 $doc_globalfile > $doc_output
    cat $doc_globalfile | iconv -f iso88591 | tail -n +2 | awk -F ';' '{if ( $4 ~ /[0-9]/ ) print "if ! grep -a "$4" '$doc_output' | grep -a '"'"'"$1";"$2"'"'"' > /dev/null ; then grep -a '"'"'"$1";"$2";"$3";"$4"'"'"' '$doc_globalfile' >> '$doc_output'  ; fi " }' | bash
    mv $doc_output $EXPORTDIRFORGLOBAL"/"$type".csv"
done

cut -d ";" -f 2 $EXPORTDIRFORGLOBAL/lots.csv | sort | uniq | sed -r 's/[0-9]{2}$//' > $EXPORTDIRFORGLOBAL/etablissements_ids.tmp

head -n 1 $EXPORTGLOBALDIR"/etablissements.csv" > $EXPORTDIRFORGLOBAL/etablissements.csv
cat $EXPORTGLOBALDIR"/etablissements.csv" | sort -t ";" -k 1,1 | join -t ";" -1 1 -2 1 - $EXPORTDIRFORGLOBAL/etablissements_ids.tmp >> $EXPORTDIRFORGLOBAL/etablissements.csv

rm $EXPORTDIRFORGLOBAL/etablissements_ids.tmp