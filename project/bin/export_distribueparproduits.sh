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

GLOBALDIR=web/exports_igp
EXPORTAPPDIR=web/exports_"$1"
EXPORTFORAPPGLOBALSUBDIR=$EXPORTAPPDIR/GLOBAL

if [ -d "$EXPORTFORAPPGLOBALSUBDIR" ]; then
  rm -rf $EXPORTFORAPPGLOBALSUBDIR
fi

mkdir $EXPORTFORAPPGLOBALSUBDIR 2> /dev/null

for file in $(find "$GLOBALDIR" -maxdepth 1 -type f -name "*.csv")
do
  FILENAME="$(basename $file)"
  if [ ! -f "$EXPORTFORAPPGLOBALSUBDIR/$FILENAME" ]; then
    head -n 1 $file > $EXPORTFORAPPGLOBALSUBDIR/$FILENAME
  fi
  cat $file | grep -E $HASHPRODUIT --binary-files=text >> $EXPORTFORAPPGLOBALSUBDIR/$FILENAME
done

cut -d ";" -f 2 $EXPORTFORAPPGLOBALSUBDIR/lots.csv $EXPORTFORAPPGLOBALSUBDIR/habilitation.csv | sed 's/"//g' | sort -u | sed -r 's/[0-9]{2}$//' > $EXPORTFORAPPGLOBALSUBDIR/etablissements_ids.tmp

head -n 1 $GLOBALDIR"/etablissements.csv" > $EXPORTFORAPPGLOBALSUBDIR/etablissements.csv
cat $GLOBALDIR"/etablissements.csv" | sort -t ";" -k 1,1 | join -t ";" -1 1 -2 1 - $EXPORTFORAPPGLOBALSUBDIR/etablissements_ids.tmp >> $EXPORTFORAPPGLOBALSUBDIR/etablissements.csv

rm $EXPORTFORAPPGLOBALSUBDIR/etablissements_ids.tmp

head -n 1 $GLOBALDIR/production.csv | iconv -f ISO88591 -t UTF8 > $EXPORTFORAPPGLOBALSUBDIR/production.csv.part
awk -F ';' '{print $9}' $EXPORTFORAPPGLOBALSUBDIR/etablissements.csv | grep '[0-9]' | sort -u | while read cvi ; do
    cat $GLOBALDIR/production.csv | grep -a ";"$cvi";" >> $EXPORTFORAPPGLOBALSUBDIR/production.csv.part
done
cat $EXPORTFORAPPGLOBALSUBDIR/production.csv.part | iconv -f UTF8 -t ISO88591 > $EXPORTFORAPPGLOBALSUBDIR/production.csv

for type in dr sv11 sv12 ; do
    head -n 1 $EXPORTFORAPPGLOBALSUBDIR/production.csv.part  > $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part"
    cat $EXPORTFORAPPGLOBALSUBDIR/production.csv.part | grep -i "^"$type";" >> $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part"
    cat $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part" | iconv -f UTF8 -t ISO88591 > $EXPORTFORAPPGLOBALSUBDIR/$type".csv"
done

rm $EXPORTFORAPPGLOBALSUBDIR/dr.csv.part
rm $EXPORTFORAPPGLOBALSUBDIR/sv11.csv.part
rm $EXPORTFORAPPGLOBALSUBDIR/sv12.csv.part
rm $EXPORTFORAPPGLOBALSUBDIR/production.csv.part