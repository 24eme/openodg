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
  FILETYPE=$(echo $FILENAME | sed 's/\..*//')
  FILTER=$(eval echo '$'"HASHPRODUIT_"$FILETYPE)
  if ! test "$FILTER" ; then
      FILTER=$(echo $HASHPRODUIT);
  fi
  cat $file | grep -E "$FILTER" --binary-files=text >> $EXPORTFORAPPGLOBALSUBDIR/$FILENAME
done

head -n 1 $GLOBALDIR/production.csv > $EXPORTFORAPPGLOBALSUBDIR/production.csv.part
tail -n +2 $EXPORTFORAPPGLOBALSUBDIR/production.ligneavecdrev.csv $EXPORTFORAPPGLOBALSUBDIR/production.lignesansdrev.csv | grep -va ^== | grep -a ';' | iconv -f ISO88591 -t UTF8 | awk -F ';' '{uniq = $1"-"$2"-"$4 ; if ( ! unicite[uniq] || unicite[uniq] == $3 ) { print $0  ; unicite[uniq] = $3 } }' | awk -F ';' '{print $1";"$2";"$3";"}' | sort -u > /tmp/productionid.$$.grep
grep -a -f /tmp/productionid.$$.grep $GLOBALDIR/production.csv >> $EXPORTFORAPPGLOBALSUBDIR/production.csv.part
mv $EXPORTFORAPPGLOBALSUBDIR/production.csv.part $EXPORTFORAPPGLOBALSUBDIR/production.csv
rm /tmp/productionid.$$.grep

cut -d ";" -f 2 $EXPORTFORAPPGLOBALSUBDIR/lots.csv $EXPORTFORAPPGLOBALSUBDIR/habilitation.csv | sed 's/"//g' | sort -u | sed -r 's/[0-9]{2}$//' > $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids.tmp
cut -d ";" -f 3 $EXPORTFORAPPGLOBALSUBDIR/production.csv >> $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids.tmp
sort -u $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids.tmp > $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids

head -n 1 $GLOBALDIR"/etablissements.csv" > $EXPORTFORAPPGLOBALSUBDIR/etablissements.csv
cat $GLOBALDIR"/etablissements.csv" | sort -t ";" -k 1,1 | join -t ";" -1 1 -2 1 - $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids >> $EXPORTFORAPPGLOBALSUBDIR/etablissements.csv

rm $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids

for type in dr sv11 sv12 ; do
    head -n 1 $EXPORTFORAPPGLOBALSUBDIR/production.csv  > $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part"
    cat $EXPORTFORAPPGLOBALSUBDIR/production.csv | grep -ia "^"$type";" >> $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part"
    mv $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part" $EXPORTFORAPPGLOBALSUBDIR/$type".csv"
done
