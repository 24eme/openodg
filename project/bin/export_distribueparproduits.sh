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

cut -d ";" -f 2 $EXPORTDIRFORGLOBAL/lots.csv $EXPORTDIRFORGLOBAL/habilitation.csv | sed 's/"//g' | sort -u | sed -r 's/[0-9]{2}$//' > $EXPORTDIRFORGLOBAL/etablissements_ids.tmp

head -n 1 $EXPORTGLOBALDIR"/etablissements.csv" > $EXPORTDIRFORGLOBAL/etablissements.csv
cat $EXPORTGLOBALDIR"/etablissements.csv" | sort -t ";" -k 1,1 | join -t ";" -1 1 -2 1 - $EXPORTDIRFORGLOBAL/etablissements_ids.tmp >> $EXPORTDIRFORGLOBAL/etablissements.csv

rm $EXPORTDIRFORGLOBAL/etablissements_ids.tmp

head -n 1 $EXPORTGLOBALDIR/production.csv | iconv -f ISO88591 -t UTF8 > $EXPORTDIRFORGLOBAL/production.csv.part
awk -F ';' '{print $9}' $EXPORTDIRFORGLOBAL/etablissements.csv | grep '[0-9]' | sort -u | while read cvi ; do
    cat $EXPORTGLOBALDIR/production.csv | grep -a ";"$cvi";" >> $EXPORTDIRFORGLOBAL/production.csv.part
done
cat $EXPORTDIRFORGLOBAL/production.csv.part | iconv -f UTF8 -t ISO88591 > $EXPORTDIRFORGLOBAL/production.csv

for type in dr sv11 sv12 ; do
    head -n 1 $EXPORTDIRFORGLOBAL/production.csv.part  > $EXPORTDIRFORGLOBAL/$type".csv.part"
    cat $EXPORTDIRFORGLOBAL/production.csv.part | grep -i "^"$type";" >> $EXPORTDIRFORGLOBAL/$type".csv.part"
    cat $EXPORTDIRFORGLOBAL/$type".csv.part" | iconv -f UTF8 -t ISO88591 > $EXPORTDIRFORGLOBAL/$type".csv"
done

rm $EXPORTDIRFORGLOBAL/dr.csv.part
rm $EXPORTDIRFORGLOBAL/sv11.csv.part
rm $EXPORTDIRFORGLOBAL/sv12.csv.part
rm $EXPORTDIRFORGLOBAL/production.csv.part