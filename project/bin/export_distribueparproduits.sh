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

cut -d ";" -f 2 $EXPORTFORAPPGLOBALSUBDIR/lots.csv $EXPORTFORAPPGLOBALSUBDIR/habilitation.csv | sed 's/"//g' | sort -u | sed -r 's/[0-9]{2}$//' > $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids.tmp
cut -d ';' -f 3  $EXPORTFORAPPGLOBALSUBDIR/production.csv  | sed 's/"//g' | sort -u | sed -r 's/[0-9]{2}$//' >> $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids.tmp
sort -u $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids.tmp > $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids

head -n 1 $GLOBALDIR"/etablissements.csv" > $EXPORTFORAPPGLOBALSUBDIR/etablissements.csv
cat $GLOBALDIR"/etablissements.csv" | sort -t ";" -k 1,1 | join -t ";" -1 1 -2 1 - $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids >> $EXPORTFORAPPGLOBALSUBDIR/etablissements.csv

rm $EXPORTFORAPPGLOBALSUBDIR/etablissements.ids

head -n 1 $GLOBALDIR/production.csv > $EXPORTFORAPPGLOBALSUBDIR/production.csv.part
awk -F ';' '{print $9}' $EXPORTFORAPPGLOBALSUBDIR/etablissements.csv  | grep '[0-9]' | sort -u | tr '\n' '|'  | sed 's/.$/\\);\/p/' | sed 's/^/\/;\\(/' | sed 's/|/\\|/g'  > $EXPORTFORAPPGLOBALSUBDIR/sed.cmd
sed -n -f $EXPORTFORAPPGLOBALSUBDIR/sed.cmd $GLOBALDIR/production.csv >> $EXPORTFORAPPGLOBALSUBDIR/production.csv.part
mv $EXPORTFORAPPGLOBALSUBDIR/production.csv.part $EXPORTFORAPPGLOBALSUBDIR/production.csv
rm $EXPORTFORAPPGLOBALSUBDIR/sed.cmd

for type in dr sv11 sv12 ; do
    head -n 1 $EXPORTFORAPPGLOBALSUBDIR/production.csv  > $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part"
    cat $EXPORTFORAPPGLOBALSUBDIR/production.csv | grep -ia "^"$type";" >> $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part"
    mv $EXPORTFORAPPGLOBALSUBDIR/$type".csv.part" $EXPORTFORAPPGLOBALSUBDIR/$type".csv"
done
