#!/bin/bash
. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

DREVPATH=$1

cat $DREVPATH | grep -E "      [A-Z_]+\:" | sed -r 's|^([\ ]+)([A-Z_]+)\:|\2|' | while read odg ; do
  mkdir $EXPORTDIR"/"$odg 2> /dev/null

  bash bin/export_docs.sh DRev 10 $odg > $EXPORTDIR"/"$odg"/drev.csv.part"
  iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR"/"$odg"/drev.csv.part" > $EXPORTDIR"/"$odg"/drev.csv"
  rm $EXPORTDIR"/"$odg"/drev.csv.part"

done

sleep 10

bash bin/export_docs.sh DR 30 > $EXPORTDIR/dr.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/dr.csv.part > $EXPORTDIR/dr.csv
rm $EXPORTDIR/dr.csv.part

bash bin/export_docs.sh SV12 30 > $EXPORTDIR/sv12.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv12.csv.part > $EXPORTDIR/sv12.csv
rm $EXPORTDIR/sv12.csv.part

bash bin/export_docs.sh SV11 30 > $EXPORTDIR/sv11.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/sv11.csv.part > $EXPORTDIR/sv11.csv
rm $EXPORTDIR/sv11.csv.part

php symfony pieces:export-csv $SYMFONYTASKOPTIONS >  $EXPORTDIR/pieces.csv.part
iconv -f UTF8 -t ISO88591//TRANSLIT $EXPORTDIR/pieces.csv.part > $EXPORTDIR/pieces.csv
rm $EXPORTDIR/pieces.csv.part

sleep 10


cat $DREVPATH | grep -E "      [A-Z_]+\:" | sed -r 's|^([\ ]+)([A-Z_]+)\:|\2|' | while read odg ; do
  head -n 1 $EXPORTDIR/dr.csv > $EXPORTDIR"/"$odg"/dr.csv"
  head -n 1 $EXPORTDIR/sv12.csv > $EXPORTDIR"/"$odg"/sv12.csv"
  head -n 1 $EXPORTDIR/sv11.csv > $EXPORTDIR"/"$odg"/sv11.csv"
  head -n 1 $EXPORTDIR/pieces.csv > $EXPORTDIR"/"$odg"/pieces.csv"
  cat $EXPORTDIR"/"$odg"/drev.csv" | cut -d ';' -f 2 | sort | uniq | grep -vi 'Identifiant' | while read identifiant ; do
    cat $EXPORTDIR/dr.csv | grep ";$identifiant;" | while read line ; do
      echo $line >> $EXPORTDIR"/"$odg"/dr.csv"
    done
    cat $EXPORTDIR/sv12.csv | grep ";$identifiant;" | while read line ; do
      echo $line >> $EXPORTDIR"/"$odg"/sv12.csv"
    done
    cat $EXPORTDIR/sv11.csv | grep ";$identifiant;" | while read line ; do
      echo $line >> $EXPORTDIR"/"$odg"/sv11.csv"
    done
    cat $EXPORTDIR/pieces.csv | grep ";$identifiant;" | while read line ; do
      echo $line >> $EXPORTDIR"/"$odg"/pieces.csv"
    done
  done
done
