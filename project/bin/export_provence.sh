#!/bin/bash

. bin/config.inc

mkdir $EXPORTDIR 2> /dev/null

php symfony export:etablissements-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/etablissements.csv.part

sort $EXPORTDIR/etablissements.csv.part > $EXPORTDIR/etablissements.csv.part.sorted
mv $EXPORTDIR/etablissements.csv.part{.sorted,}

php symfony export:chais-csv $SYMFONYTASKOPTIONS > $EXPORTDIR/chais.csv.part

cat $EXPORTDIR/etablissements.csv.part | grep -E "^IdOp" > $EXPORTDIR/etablissements.csv
cat $EXPORTDIR/etablissements.csv.part | grep -Ev "^IdOp" >> $EXPORTDIR/etablissements.csv

mv $EXPORTDIR/chais.csv{.part,}

cat $EXPORTDIR/etablissements.csv | iconv -f UTF8 -t ISO88591//TRANSLIT > $EXPORTDIR/etablissements.iso8859.csv
cat $EXPORTDIR/chais.csv | iconv -f UTF8 -t ISO88591//TRANSLIT >$EXPORTDIR/chais.iso8859.csv
