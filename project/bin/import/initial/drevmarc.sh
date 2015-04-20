#!/bin/bash

. bin/config.inc

WORKDIR=$TMPDIR/import_drevmarc
WORKDIROPERATEUR=$TMPDIR/import_operateurs
DATADIR=data/import/extravitis/revendication
mkdir $WORKDIR 2> /dev/null

cat $DATADIR/AVA_MARC.csv | iconv -f iso88591 -t utf8 | tr -d "\r" | cut -d ";" -f 2,3,4,5,6,7,8 | sort -t ";" -k 2,2 > $WORKDIR/marc.csv

join -t ";" -1 2 -2 2 $WORKDIROPERATEUR/id_evv_cvi.csv $WORKDIR/marc.csv | cut -d ";" -f 3,4,5,6,7,8,9 > $WORKDIR/marc_cvi.csv

echo "#cvi;annee;date distillation debut;date distillation fin;volume alcool;titre alcoometrique;quantite marc" > $WORKDIR/drevmarc.csv

cat $WORKDIR/marc_cvi.csv | sort | grep -E "^[0-9]+;2013;" >> $WORKDIR/drevmarc.csv

php symfony import:DRevMarc $WORKDIR/drevmarc.csv